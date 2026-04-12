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

    public function actualizarEstado(int $id, string $nuevoEstado): bool
    {
        $stmt = $this->db->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
        $stmt->bind_param('si', $nuevoEstado, $id);
        return $stmt->execute();
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
