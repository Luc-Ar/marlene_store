<?php
class ClienteRepository
{
    private mysqli $db;

    public function __construct(mysqli $conexion)
    {
        $this->db = $conexion;
    }

    /**
     * Obtiene clientes con métricas de compra calculadas.
     */
    public function listarClientes(string $buscar = ''): array
    {
        $sql = "SELECT c.*, 
                       COUNT(p.id) as total_pedidos,
                       COALESCE(SUM(p.total), 0) as total_gastado
                FROM clientes c
                LEFT JOIN pedidos p ON c.id = p.id_cliente";

        $params = [];
        $tipos = "";

        if ($buscar !== '') {
            $sql .= " WHERE c.nombre LIKE ? OR c.apellido LIKE ? OR c.telefono LIKE ?";
            $term = "%$buscar%";
            array_push($params, $term, $term, $term);
            $tipos = "sss";
        }

        $sql .= " GROUP BY c.id ORDER BY total_gastado DESC";

        $stmt = $this->db->prepare($sql);
        if ($buscar !== '') {
            $stmt->bind_param($tipos, ...$params);
        }

        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM clientes WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $resultado = $stmt->get_result()->fetch_assoc();
        return $resultado ?: null;
    }
}
