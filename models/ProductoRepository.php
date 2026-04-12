<?php

/**
 * ProductoRepository
 * Maneja la lógica de base de datos para el catálogo de Marlene Store.
 */
class ProductoRepository
{
    private mysqli $db;

    public function __construct(mysqli $conexion)
    {
        $this->db = $conexion;
    }

    /**
     * Lista productos con soporte para búsquedas y filtros.
     */
    public function listarProductos($filtros = [])
    {
        // 1. Base de la consulta
        $sql = "SELECT p.*, c.nombre as categoria_nombre 
            FROM productos p 
            LEFT JOIN categorias c ON p.categoria = c.id 
            WHERE 1=1";

        // 2. Filtro por ESTADO (Activos/Inactivos)
        if (isset($filtros['ver'])) {
            $estado = ($filtros['ver'] === 'activos') ? 1 : 0;
            $sql .= " AND p.activo = $estado";
        }

        // 3. Filtro por BUSCADOR de texto
        if (!empty($filtros['buscar'])) {
            $busqueda = $this->db->real_escape_string($filtros['buscar']);
            $sql .= " AND (p.nombre LIKE '%$busqueda%' OR p.sku LIKE '%$busqueda%')";
        }

        // 4. EL QUE TE ESTÁ FALTANDO: Filtro por CATEGORÍA
        if (!empty($filtros['categoria'])) {
            $id_cat = (int)$filtros['categoria'];
            $sql .= " AND p.categoria = $id_cat";
        }

        $sql .= " ORDER BY p.id DESC";

        $res = $this->db->query($sql);
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Método privado para ejecutar consultas de forma segura (Blindaje)
     */
    private function consultar(string $sql, string $tipos, array $params): array
    {
        try {
            $stmt = $this->db->prepare($sql);
            if (!empty($tipos)) {
                $stmt->bind_param($tipos, ...$params);
            }
            $stmt->execute();
            $resultado = $stmt->get_result();
            return $resultado->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Error SQL en ProductoRepository: " . $e->getMessage());
            return [];
        }
    }
}
