<?php
class PedidoRepository
{
    private mysqli $db;

    public function __construct(mysqli $conexion)
    {
        $this->db = $conexion;
    }

    public function listarPedidos(array $filtros = [])
    {
        $sql = "SELECT p.*, c.nombre as c_nombre, c.apellido as c_apellido, c.telefono as cliente_telefono
                FROM pedidos p
                LEFT JOIN clientes c ON p.id_cliente = c.id";

        $condiciones = [];
        $params = [];
        $tipos = "";

        if (!empty($filtros['buscar'])) {
            $condiciones[] = "(p.numero_pedido LIKE ? OR c.nombre LIKE ? OR c.apellido LIKE ?)";
            $term = "%{$filtros['buscar']}%";
            array_push($params, $term, $term, $term);
            $tipos .= "sss";
        }

        if (!empty($filtros['estado'])) {
            $condiciones[] = "p.estado = ?";
            $params[] = $filtros['estado'];
            $tipos .= "s";
        }

        // Filtros de fecha (los agregué porque tu HTML tiene los inputs de fecha)
        if (!empty($filtros['desde'])) {
            $condiciones[] = "p.fecha_pedido >= ?";
            $params[] = $filtros['desde'] . " 00:00:00";
            $tipos .= "s";
        }
        if (!empty($filtros['hasta'])) {
            $condiciones[] = "p.fecha_pedido <= ?";
            $params[] = $filtros['hasta'] . " 23:59:59";
            $tipos .= "s";
        }

        if (count($condiciones) > 0) {
            $sql .= " WHERE " . implode(" AND ", $condiciones);
        }

        $sql .= " ORDER BY p.fecha_pedido DESC";

        // IMPORTANTE: Retornamos el objeto result para que funcione el while() en tu HTML
        return $this->ejecutarConsultaDirecta($sql, $tipos, $params);
    }

    /**
     * @deprecated Usar cambiarEstado() en su lugar — este método no
     * toca el stock, por eso quedó el bug de "cancelar no devuelve
     * stock". Lo dejamos acá por si algo viejo todavía lo llama, pero
     * no debería usarse en código nuevo.
     */
    public function actualizarEstado(int $id, string $nuevoEstado): bool
    {
        $stmt = $this->db->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
        $stmt->bind_param('si', $nuevoEstado, $id);
        return $stmt->execute();
    }

    /**
     * Transiciones de estado válidas. La clave es el estado actual,
     * el valor es la lista de estados a los que se puede pasar desde ahí.
     * Cualquier estado no listado acá (entregado, cancelado, expirado)
     * es terminal: no admite ninguna transición.
     */
    private const TRANSICIONES_VALIDAS = [
        'pendiente'      => ['confirmado', 'cancelado', 'expirado'],
        'confirmado'     => ['en_preparacion', 'cancelado'],
        'en_preparacion' => ['enviado', 'demorado', 'cancelado'],
        'demorado'       => ['en_preparacion', 'enviado', 'cancelado'],
        'enviado'        => ['entregado'],
    ];

    /**
     * Estados desde los que el cliente (no el admin) puede cancelar
     * su propio pedido. Una vez que entra en preparación, ya hay
     * trabajo invertido del local — a partir de ahí es solo admin.
     */
    // Una vez "confirmado" se considera pagado (por MercadoPago
    // automático, o porque el admin revisó la transferencia a mano).
    // A partir de ahí, cancelar ya no es autoservicio: el cliente
    // tiene que contactarnos directamente.
    private const ESTADOS_CANCELABLES_POR_CLIENTE = ['pendiente'];
    /**
     * ÚNICO lugar del sistema que debe cambiar el estado de un pedido.
     * Se encarga de devolver o volver a descontar stock según
     * corresponda, siempre dentro de una transacción, y valida que
     * la transición sea permitida según quién la pide.
     *
     * @param string $origen 'admin' o 'cliente' — determina qué
     *                        transiciones están permitidas.
     * @return array{ok: bool, error: ?string}
     */
    public function cambiarEstado(int $id, string $nuevoEstado, string $origen = 'admin'): array
    {
        $estadosValidos = ['pendiente', 'confirmado', 'en_preparacion', 'demorado', 'enviado', 'entregado', 'cancelado', 'expirado'];
        if (!in_array($nuevoEstado, $estadosValidos)) {
            return ['ok' => false, 'error' => 'Estado inválido.'];
        }

        $this->db->begin_transaction();

        try {
            $stmt = $this->db->prepare("SELECT estado, id_cliente FROM pedidos WHERE id = ? FOR UPDATE");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $pedido = $stmt->get_result()->fetch_assoc();

            if (!$pedido) {
                $this->db->rollback();
                return ['ok' => false, 'error' => 'El pedido no existe.'];
            }

            $estadoActual = $pedido['estado'];

            // Nada que hacer si no cambia
            if ($estadoActual === $nuevoEstado) {
                $this->db->commit();
                return ['ok' => true, 'error' => null];
            }

            // Los estados terminales (entregado, cancelado, expirado) no
            // admiten ninguna transición de salida — cubre tanto el caso
            // viejo de "reactivar cancelado" como cualquier otro intento.
            $siguientesPermitidos = self::TRANSICIONES_VALIDAS[$estadoActual] ?? [];
            if (!in_array($nuevoEstado, $siguientesPermitidos)) {
                $this->db->rollback();
                $motivo = in_array($estadoActual, ['cancelado', 'expirado'])
                    ? 'Este pedido está cancelado y no se puede modificar.'
                    : "No se puede pasar de \"$estadoActual\" a \"$nuevoEstado\".";
                return ['ok' => false, 'error' => $motivo];
            }

            // Si es el cliente quien pide el cambio, solo puede cancelar,
            // y solo desde los estados habilitados para eso.
            if ($origen === 'cliente') {
                if ($nuevoEstado !== 'cancelado' || !in_array($estadoActual, self::ESTADOS_CANCELABLES_POR_CLIENTE)) {
                    $this->db->rollback();
                    return ['ok' => false, 'error' => 'Ya no podés cancelar este pedido desde acá — contactanos si necesitás ayuda.'];
                }
            }

            $pasaACancelado = in_array($nuevoEstado, ['cancelado', 'expirado']);

            if ($pasaACancelado) {
                // Devolver stock de todos los items del pedido.
                $itemsStmt = $this->db->prepare("SELECT id_producto, cantidad FROM pedido_items WHERE id_pedido = ?");
                $itemsStmt->bind_param('i', $id);
                $itemsStmt->execute();
                $items = $itemsStmt->get_result()->fetch_all(MYSQLI_ASSOC);

                foreach ($items as $item) {
                    $upd = $this->db->prepare("UPDATE productos SET stock = stock + ? WHERE id = ?");
                    $upd->bind_param('ii', $item['cantidad'], $item['id_producto']);
                    $upd->execute();
                }
            }

            $updEstado = $this->db->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
            $updEstado->bind_param('si', $nuevoEstado, $id);
            $updEstado->execute();

            $this->db->commit();
            return ['ok' => true, 'error' => null];
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error en cambiarEstado (pedido $id): " . $e->getMessage());
            return ['ok' => false, 'error' => 'Error interno al cambiar el estado.'];
        }
    }

    private function ejecutarConsultaDirecta(string $sql, string $tipos, array $params)
    {
        $stmt = $this->db->prepare($sql);
        if (!empty($tipos)) {
            $stmt->bind_param($tipos, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result(); // Retorna el objeto mysqli_result
    }
}
