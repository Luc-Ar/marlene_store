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
     * Busca un producto por su ID. Devuelve null si no existe,
     * para que la página que llama pueda decidir qué hacer (ej. redirigir).
     */
    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM productos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result()->fetch_assoc();
        return $resultado ?: null;
    }

    /**
     * Chequea si un SKU ya existe (para evitar duplicados al crear/generar uno automático).
     */
    public function existeSku(string $sku): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM productos WHERE sku = ?");
        $stmt->bind_param("s", $sku);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    /**
     * Devuelve el ID más alto actual, usado para armar el correlativo del SKU automático.
     */
    public function obtenerUltimoId(): int
    {
        $res = $this->db->query("SELECT id FROM productos ORDER BY id DESC LIMIT 1");
        if ($res && $res->num_rows > 0) {
            return (int)$res->fetch_assoc()['id'];
        }
        return 0;
    }

    /**
     * Crea un producto nuevo. $datos debe traer las claves ya validadas
     * desde la página (nombre, precio, etc.) — el Repository no valida
     * reglas de negocio, solo persiste.
     * Devuelve el ID insertado, o false si falla.
     */
    public function crear(array $datos): int|false
    {
        $stmt = $this->db->prepare("
            INSERT INTO productos
                (sku, nombre, descripcion_corta, descripcion_larga, precio, precio_oferta,
                 categoria, subcategoria, peso_gramos, stock, imagen_principal, activo, destacado)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            'ssssddisiisii',
            $datos['sku'],
            $datos['nombre'],
            $datos['descripcion_corta'],
            $datos['descripcion_larga'],
            $datos['precio'],
            $datos['precio_oferta'],
            $datos['categoria'],
            $datos['subcategoria'],
            $datos['peso_gramos'],
            $datos['stock'],
            $datos['imagen_principal'],
            $datos['activo'],
            $datos['destacado']
        );

        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        return false;
    }

    /**
     * Actualiza un producto existente. Misma idea que crear(): recibe
     * datos ya validados/saneados desde la página.
     */
    public function actualizar(int $id, array $datos): bool
    {
        $stmt = $this->db->prepare("
            UPDATE productos SET
                nombre=?, descripcion_corta=?, descripcion_larga=?, precio=?, precio_oferta=?,
                categoria=?, subcategoria=?, peso_gramos=?, stock=?, imagen_principal=?, activo=?, destacado=?
            WHERE id=?
        ");
        $stmt->bind_param(
            'sssddisiisiii',
            $datos['nombre'],
            $datos['descripcion_corta'],
            $datos['descripcion_larga'],
            $datos['precio'],
            $datos['precio_oferta'],
            $datos['categoria'],
            $datos['subcategoria'],
            $datos['peso_gramos'],
            $datos['stock'],
            $datos['imagen_principal'],
            $datos['activo'],
            $datos['destacado'],
            $id
        );

        return $stmt->execute();
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
