<?php
// models/CodigosPostales.php - Modelo para códigos postales de Chihuahua

require_once __DIR__ . '/../config/database.php';

class CodigosPostales {
    private $conn;
    private $table_name = "codigos_postales_chihuahua";
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    // Buscar códigos postales por asentamiento
    public function buscarPorAsentamiento($query, $limit = 100) {
        if (empty($query) || strlen(trim($query)) < 2) {
            return [];
        }
        
        $sql = "SELECT codigo_postal, asentamiento, tipo_asentamiento, municipio, ciudad, zona 
                FROM " . $this->table_name . " 
                WHERE asentamiento LIKE :query 
                ORDER BY asentamiento 
                LIMIT :limit";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':query', "%" . $query . "%", PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Buscar códigos postales por código postal
    public function buscarPorCodigo($codigo, $limit = 100) {
        if (empty($codigo) || strlen(trim($codigo)) < 3) {
            return [];
        }
        
        $sql = "SELECT codigo_postal, asentamiento, tipo_asentamiento, municipio, ciudad, zona 
                FROM " . $this->table_name . " 
                WHERE codigo_postal LIKE :codigo 
                ORDER BY codigo_postal 
                LIMIT :limit";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':codigo', $codigo . "%", PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Buscar códigos postales por municipio
    public function buscarPorMunicipio($municipio, $limit = 100) {
        if (empty($municipio) || strlen(trim($municipio)) < 2) {
            return [];
        }
        
        $sql = "SELECT codigo_postal, asentamiento, tipo_asentamiento, municipio, ciudad, zona 
                FROM " . $this->table_name . " 
                WHERE municipio LIKE :municipio 
                ORDER BY asentamiento 
                LIMIT :limit";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':municipio', "%" . $municipio . "%", PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Obtener código postal específico
    public function obtenerCodigoPostal($codigo) {
        $sql = "SELECT * FROM " . $this->table_name . " WHERE codigo_postal = :codigo LIMIT 1";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':codigo', $codigo, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    // Buscar general para autocompletado
    public function buscarGeneral($query, $limit = 100) {
        if (empty($query) || strlen(trim($query)) < 2) {
            return [];
        }
        
        $sql = "SELECT d_codigo, d_asenta, d_tipo_asenta, D_mnpio, d_ciudad, d_zona 
                FROM " . $this->table_name . " 
                WHERE d_asenta LIKE :query1 
                   OR D_mnpio LIKE :query2 
                   OR d_ciudad LIKE :query3 
                   OR d_codigo LIKE :query4
                ORDER BY 
                    CASE 
                        WHEN d_codigo LIKE :query5 THEN 1
                        WHEN d_asenta LIKE :query6 THEN 2
                        WHEN D_mnpio LIKE :query7 THEN 3
                        ELSE 4
                    END,
                    d_asenta
                LIMIT :limit";
        
        $stmt = $this->conn->prepare($sql);
        $searchTerm = "%" . $query . "%";
        $stmt->bindValue(':query1', $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(':query2', $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(':query3', $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(':query4', $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(':query5', $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(':query6', $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(':query7', $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Obtener estadísticas
    public function obtenerEstadisticas() {
        $stats = [];
        
        // Total de códigos postales
        $stmt = $this->conn->query('SELECT COUNT(*) as total FROM ' . $this->table_name);
        $stats['total_codigos_postales'] = $stmt->fetch()['total'];
        
        // Por municipio
        $stmt = $this->conn->query('SELECT municipio, COUNT(*) as total FROM ' . $this->table_name . ' GROUP BY municipio ORDER BY total DESC LIMIT 10');
        $stats['por_municipio'] = $stmt->fetchAll();
        
        // Por zona
        $stmt = $this->conn->query('SELECT zona, COUNT(*) as total FROM ' . $this->table_name . ' GROUP BY zona ORDER BY total DESC');
        $stats['por_zona'] = $stmt->fetchAll();
        
        return $stats;
    }
    
    // Validar código postal
    public function validarCodigoPostal($codigo) {
        if (empty($codigo) || !preg_match('/^\d{5}$/', $codigo)) {
            return false;
        }
        
        $stmt = $this->conn->prepare('SELECT COUNT(*) as existe FROM ' . $this->table_name . ' WHERE codigo_postal = :codigo');
        $stmt->bindValue(':codigo', $codigo, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetch()['existe'] > 0;
    }
    
    /**
     * Contar códigos postales
     * @return int Total de códigos postales
     */
    public function contarCodigosPostales() {
        try {
            $sql = "SELECT COUNT(*) as total FROM " . $this->table_name;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            return (int) $result['total'];
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Contar códigos postales por zona
     * @return array Conteo por zona
     */
    public function contarPorZona() {
        try {
            $sql = "SELECT d_zona as zona, COUNT(*) as total FROM " . $this->table_name . " 
                    WHERE d_zona IS NOT NULL AND d_zona != '' 
                    GROUP BY d_zona ORDER BY total DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Contar códigos postales por municipio
     * @return array Conteo por municipio
     */
    public function contarPorMunicipio() {
        try {
            $sql = "SELECT D_mnpio as municipio, COUNT(*) as total FROM " . $this->table_name . " 
                    WHERE D_mnpio IS NOT NULL AND D_mnpio != '' 
                    GROUP BY D_mnpio ORDER BY total DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Contar códigos postales por estado
     * @return array Conteo por estado
     */
    public function contarPorEstado() {
        try {
            $sql = "SELECT d_estado as estado, COUNT(*) as total FROM " . $this->table_name . " 
                    WHERE d_estado IS NOT NULL AND d_estado != '' 
                    GROUP BY d_estado ORDER BY total DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Obtener detalles de un código postal
     * @param string $codigo Código postal
     * @return array|null Detalles del código postal
     */
    public function obtenerDetalles($codigo) {
        try {
            $sql = "SELECT * FROM " . $this->table_name . " WHERE d_codigo = :codigo LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':codigo', $codigo, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Analizar códigos postales por zona
     * @return array Análisis por zona
     */
    public function analizarPorZona() {
        try {
            $sql = "SELECT d_zona as zona, 
                           COUNT(*) as total_codigos,
                           COUNT(DISTINCT D_mnpio) as municipios_distintos,
                           COUNT(DISTINCT d_estado) as estados_distintos
                    FROM " . $this->table_name . " 
                    WHERE d_zona IS NOT NULL AND d_zona != ''
                    GROUP BY d_zona 
                    ORDER BY total_codigos DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    // Obtener códigos postales por zona
    public function obtenerPorZona($zona, $limit = 100) {
        $sql = "SELECT codigo_postal, asentamiento, municipio, ciudad 
                FROM " . $this->table_name . " 
                WHERE zona = :zona 
                ORDER BY municipio, asentamiento 
                LIMIT :limit";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':zona', $zona, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    // ========================================
    // NUEVOS MÉTODOS PARA ENDPOINTS AVANZADOS
    // ========================================

    // Buscar códigos postales exactos o parciales
    public function buscarCodigosPostales($query, $limit = 100) {
        if (empty($query) || strlen(trim($query)) < 2) {
            return [];
        }
        
        $sql = "SELECT d_codigo, d_asenta, d_tipo_asenta, D_mnpio, d_estado, d_zona 
                FROM " . $this->table_name . " 
                WHERE d_codigo LIKE :query1 
                   OR d_asenta LIKE :query2 
                   OR D_mnpio LIKE :query3 
                ORDER BY d_codigo, d_asenta 
                LIMIT :limit";
        
        $stmt = $this->conn->prepare($sql);
        $searchTerm = "%" . $query . "%";
        $stmt->bindValue(':query1', $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(':query2', $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(':query3', $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Listar todos los códigos postales de un municipio
    public function buscarPorMunicipioExacto($municipio, $limit = 500) {
        if (empty($municipio)) {
            return [];
        }
        
        $sql = "SELECT d_codigo, d_asenta, d_tipo_asenta, D_mnpio, d_estado, d_zona 
                FROM " . $this->table_name . " 
                WHERE D_mnpio = :municipio 
                ORDER BY d_codigo, d_asenta 
                LIMIT :limit";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':municipio', $municipio, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Listar códigos postales por colonia o fraccionamiento
    public function buscarPorColonia($colonia, $limit = 200) {
        if (empty($colonia) || strlen(trim($colonia)) < 2) {
            return [];
        }
        
        $sql = "SELECT d_codigo, d_asenta, d_tipo_asenta, D_mnpio, d_estado, d_zona 
                FROM " . $this->table_name . " 
                WHERE d_asenta LIKE :colonia 
                ORDER BY d_asenta, d_codigo 
                LIMIT :limit";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':colonia', "%" . $colonia . "%", PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Autocompletado inteligente para formularios
    public function obtenerSugerencias($texto, $limit = 20) {
        if (empty($texto) || strlen(trim($texto)) < 2) {
            return [];
        }
        
        $sql = "SELECT DISTINCT d_asenta as sugerencia, 'colonia' as tipo
                FROM " . $this->table_name . " 
                WHERE d_asenta LIKE :texto1
                UNION
                SELECT DISTINCT D_mnpio as sugerencia, 'municipio' as tipo
                FROM " . $this->table_name . " 
                WHERE D_mnpio LIKE :texto2
                UNION
                SELECT DISTINCT d_codigo as sugerencia, 'codigo' as tipo
                FROM " . $this->table_name . " 
                WHERE d_codigo LIKE :texto3
                ORDER BY sugerencia 
                LIMIT :limit";
        
        $stmt = $this->conn->prepare($sql);
        $searchTerm = "%" . $texto . "%";
        $stmt->bindValue(':texto1', $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(':texto2', $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(':texto3', $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Listar colonias asociadas a un código postal
    public function obtenerColoniasPorCP($codigo) {
        if (empty($codigo)) {
            return [];
        }
        
        $sql = "SELECT d_asenta, d_tipo_asenta, D_mnpio, d_zona 
                FROM " . $this->table_name . " 
                WHERE d_codigo = :codigo 
                ORDER BY d_asenta";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':codigo', $codigo, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Información administrativa del municipio y estado
    public function obtenerInfoMunicipio($codigo) {
        if (empty($codigo)) {
            return null;
        }
        
        $sql = "SELECT DISTINCT D_mnpio, d_estado, d_ciudad, c_estado, c_mnpio 
                FROM " . $this->table_name . " 
                WHERE d_codigo = :codigo 
                LIMIT 1";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':codigo', $codigo, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Estadísticas por municipio
    public function obtenerStatsMunicipios() {
        $sql = "SELECT D_mnpio as municipio, COUNT(*) as total_cp, 
                       COUNT(DISTINCT d_asenta) as total_colonias
                FROM " . $this->table_name . " 
                GROUP BY D_mnpio 
                ORDER BY total_cp DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Estadísticas por colonia
    public function obtenerStatsColonias() {
        $sql = "SELECT d_asenta as colonia, COUNT(*) as total_cp, 
                       GROUP_CONCAT(DISTINCT D_mnpio) as municipios
                FROM " . $this->table_name . " 
                GROUP BY d_asenta 
                HAVING COUNT(*) > 1
                ORDER BY total_cp DESC 
                LIMIT 100";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Análisis de zonas populosas
    public function analizarZonasPopulosas() {
        $sql = "SELECT d_zona as zona, COUNT(*) as total_cp, 
                       COUNT(DISTINCT d_asenta) as total_colonias,
                       COUNT(DISTINCT D_mnpio) as total_municipios
                FROM " . $this->table_name . " 
                GROUP BY d_zona 
                ORDER BY total_cp DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Agrupación de códigos postales en rangos
    public function analizarRangos() {
        $sql = "SELECT 
                    CASE 
                        WHEN CAST(d_codigo AS UNSIGNED) BETWEEN 31000 AND 31999 THEN '31000-31999'
                        WHEN CAST(d_codigo AS UNSIGNED) BETWEEN 32000 AND 32999 THEN '32000-32999'
                        WHEN CAST(d_codigo AS UNSIGNED) BETWEEN 33000 AND 33999 THEN '33000-33999'
                        WHEN CAST(d_codigo AS UNSIGNED) BETWEEN 34000 AND 34999 THEN '34000-34999'
                        WHEN CAST(d_codigo AS UNSIGNED) BETWEEN 35000 AND 35999 THEN '35000-35999'
                        ELSE 'Otros'
                    END as rango,
                    COUNT(*) as total_cp,
                    COUNT(DISTINCT D_mnpio) as municipios
                FROM " . $this->table_name . " 
                GROUP BY rango 
                ORDER BY total_cp DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Análisis por municipio
    public function analizarMunicipios() {
        $sql = "SELECT D_mnpio as municipio, 
                       COUNT(*) as total_cp,
                       COUNT(DISTINCT d_asenta) as total_colonias,
                       COUNT(DISTINCT d_zona) as zonas,
                       MIN(d_codigo) as cp_minimo,
                       MAX(d_codigo) as cp_maximo
                FROM " . $this->table_name . " 
                GROUP BY D_mnpio 
                ORDER BY total_cp DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener todos los códigos postales para exportación
    public function obtenerTodos($limit = 10000) {
        $sql = "SELECT d_codigo, d_asenta, d_tipo_asenta, D_mnpio, d_estado, d_zona, 
                       d_ciudad, c_estado, c_mnpio, c_tipo_asenta
                FROM " . $this->table_name . " 
                ORDER BY d_codigo, d_asenta 
                LIMIT :limit";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
