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
     * ÚNICO lugar del sistema que debe cambiar el estado de un pedido.
     * Se encarga de devolver o volver a descontar stock según
     * corresponda, siempre dentro de una transacción.
     *
     * @return array{ok: bool, error: ?string}
     */
    public function cambiarEstado(int $id, string $nuevoEstado): array
    {
        $estadosValidos = ['pendiente', 'confirmado', 'en_preparacion', 'demorado', 'enviado', 'entregado', 'cancelado', 'expirado'];
        if (!in_array($nuevoEstado, $estadosValidos)) {
            return ['ok' => false, 'error' => 'Estado inválido.'];
        }

        $this->db->begin_transaction();

        try {
            // FOR UPDATE bloquea la fila hasta el commit/rollback, para
            // que dos cambios de estado simultáneos sobre el mismo
            // pedido no se pisen entre sí.
            $stmt = $this->db->prepare("SELECT estado FROM pedidos WHERE id = ? FOR UPDATE");
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

            $pasaACancelado = in_array($nuevoEstado, ['cancelado', 'expirado']) && !in_array($estadoActual, ['cancelado', 'expirado']);
            $saleDeCancelado = in_array($estadoActual, ['cancelado', 'expirado']) && !in_array($nuevoEstado, ['cancelado', 'expirado']);

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
            } elseif ($saleDeCancelado) {
                // Reactivar un pedido cancelado: hay que volver a
                // descontar el stock, y puede que ya no alcance.
                $itemsStmt = $this->db->prepare("SELECT id_producto, cantidad FROM pedido_items WHERE id_pedido = ?");
                $itemsStmt->bind_param('i', $id);
                $itemsStmt->execute();
                $items = $itemsStmt->get_result()->fetch_all(MYSQLI_ASSOC);

                foreach ($items as $item) {
                    $upd = $this->db->prepare("UPDATE productos SET stock = stock - ? WHERE id = ? AND stock >= ?");
                    $upd->bind_param('iii', $item['cantidad'], $item['id_producto'], $item['cantidad']);
                    $upd->execute();

                    if ($upd->affected_rows === 0) {
                        $this->db->rollback();
                        return ['ok' => false, 'error' => 'No se puede reactivar: ya no hay stock suficiente de uno o más productos.'];
                    }
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
