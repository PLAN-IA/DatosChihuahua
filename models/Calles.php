<?php
// models/Calles.php - Modelo mejorado para manejar datos de calles

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/Config.php';

class Calles {
    private $conn;
    private $table_name = "vialidades_chihuahua";
    private $max_results;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->max_results = Config::get('MAX_SEARCH_RESULTS', 1000);
    }

    /**
     * Buscar calles por nombre
     * @param string $query Término de búsqueda
     * @param int $limit Límite de resultados
     * @param int $offset Offset para paginación
     * @return array Resultados de la búsqueda
     * @throws Exception Si hay error en la consulta
     */
    public function buscarCalles($query, $limit = null, $offset = 0) {
        // Validar entrada
        if (empty($query) || strlen(trim($query)) < 2) {
            throw new InvalidArgumentException('El término de búsqueda debe tener al menos 2 caracteres');
        }

        $limit = $limit ?? Config::get('DEFAULT_SEARCH_LIMIT', 100);
        $limit = min($limit, $this->max_results); // Respetar límite máximo
        $offset = max(0, $offset); // Offset no puede ser negativo

        try {
            $sql = "SELECT id, nombre_vialidad, via_principal, clasificacion, tipo_vialidad, municipio, calle_inicio, calle_fin 
                    FROM " . $this->table_name . " 
                    WHERE nombre_vialidad LIKE :query 
                    ORDER BY 
                        CASE WHEN nombre_vialidad LIKE :exact_match THEN 1 ELSE 2 END,
                        nombre_vialidad
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($sql);
            $searchTerm = "%" . trim($query) . "%";
            $exactMatch = trim($query) . "%";
            
            $stmt->bindParam(':query', $searchTerm, PDO::PARAM_STR);
            $stmt->bindParam(':exact_match', $exactMatch, PDO::PARAM_STR);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            return $stmt->fetchAll();

        } catch (PDOException $e) {
            $this->logError('Error buscando calles: ' . $e->getMessage());
            throw new Exception('Error interno al buscar calles');
        }
    }

    // Obtener calles principales
    public function obtenerCallesPrincipales($limit = 50) {
        $sql = "SELECT id, nombre, cla_calle, t_vial2, t_vial3 
                FROM " . $this->table_name . " 
                WHERE v_ppal = 'SI'
                ORDER BY nombre
                LIMIT :limit";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    // Obtener tipos de vialidad
    public function obtenerTiposVialidad() {
        $sql = "SELECT tipo_vialidad as tipo, COUNT(*) as cantidad
                FROM " . $this->table_name . " 
                WHERE tipo_vialidad IS NOT NULL AND tipo_vialidad != ''
                GROUP BY tipo_vialidad
                ORDER BY cantidad DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    // Buscar intersecciones
    public function buscarIntersecciones($calle1, $calle2 = null, $limit = 20) {
        $sql = "SELECT id, nombre_vialidad, calle_inicio, calle_fin, via_principal, clasificacion
                FROM " . $this->table_name . " 
                WHERE calle_inicio LIKE :calle1 OR calle_fin LIKE :calle1";
        
        $params = [':calle1' => "%" . $calle1 . "%"];
        
        if ($calle2) {
            $sql .= " OR calle_inicio LIKE :calle2 OR calle_fin LIKE :calle2";
            $params[':calle2'] = "%" . $calle2 . "%";
        }
        
        $sql .= " ORDER BY nombre_vialidad LIMIT :limit";
        $params[':limit'] = $limit;
        
        $stmt = $this->conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Contar total de calles
     * @param string|null $query Término de búsqueda opcional
     * @return int Total de calles
     */
    public function contarCalles($query = null) {
        try {
            if ($query) {
                $sql = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE nombre_vialidad LIKE :query";
                $stmt = $this->conn->prepare($sql);
                $searchTerm = "%" . trim($query) . "%";
                $stmt->bindParam(':query', $searchTerm, PDO::PARAM_STR);
            } else {
                $sql = "SELECT COUNT(*) as total FROM " . $this->table_name;
                $stmt = $this->conn->prepare($sql);
            }
            
            $stmt->execute();
            $result = $stmt->fetch();
            return (int) $result['total'];

        } catch (PDOException $e) {
            $this->logError('Error contando calles: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Contar calles principales
     * @return int Total de calles principales
     */
    public function contarCallesPrincipales() {
        try {
            $sql = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE via_principal = 'SI'";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            return (int) $result['total'];
        } catch (PDOException $e) {
            $this->logError('Error contando calles principales: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Contar calles por municipio
     * @return array Conteo por municipio
     */
    public function contarPorMunicipio() {
        try {
            $sql = "SELECT municipio, COUNT(*) as total FROM " . $this->table_name . " 
                    WHERE municipio IS NOT NULL AND municipio != '' 
                    GROUP BY municipio ORDER BY total DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->logError('Error contando por municipio: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Contar calles por clasificación
     * @return array Conteo por clasificación
     */
    public function contarPorClasificacion() {
        try {
            $sql = "SELECT clasificacion, COUNT(*) as total FROM " . $this->table_name . " 
                    WHERE clasificacion IS NOT NULL AND clasificacion != '' 
                    GROUP BY clasificacion ORDER BY total DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->logError('Error contando por clasificación: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener detalles de una calle específica
     * @param int $id ID de la calle
     * @return array|null Detalles de la calle
     */
    public function obtenerDetalles($id) {
        try {
            $sql = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            $this->logError('Error obteniendo detalles de calle: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener intersecciones de una calle
     * @param int $id ID de la calle
     * @return array Intersecciones
     */
    public function obtenerIntersecciones($id) {
        try {
            $sql = "SELECT calle_inicio, calle_fin FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $calle = $stmt->fetch();
            
            if (!$calle) {
                return [];
            }
            
            $intersecciones = [];
            if ($calle['calle_inicio']) {
                $intersecciones[] = $calle['calle_inicio'];
            }
            if ($calle['calle_fin']) {
                $intersecciones[] = $calle['calle_fin'];
            }
            
            return $intersecciones;
        } catch (PDOException $e) {
            $this->logError('Error obteniendo intersecciones: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener calles cercanas por código postal
     * @param string $codigo Código postal
     * @param int $limit Límite de resultados
     * @return array Calles cercanas
     */
    public function obtenerCallesCercanasPorCP($codigo, $limit = 10) {
        try {
            // Esta es una implementación básica - en un sistema real usarías coordenadas geográficas
            $sql = "SELECT * FROM " . $this->table_name . " 
                    WHERE municipio LIKE :codigo OR nombre_vialidad LIKE :codigo
                    ORDER BY nombre_vialidad LIMIT :limit";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':codigo', '%' . $codigo . '%', PDO::PARAM_STR);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->logError('Error obteniendo calles cercanas por CP: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Calcular ruta entre dos calles
     * @param string $from Calle origen
     * @param string $to Calle destino
     * @return array Información de la ruta
     */
    public function calcularRuta($from, $to) {
        try {
            // Implementación básica - en un sistema real usarías algoritmos de routing
            $sql = "SELECT * FROM " . $this->table_name . " 
                    WHERE nombre_vialidad LIKE :from OR nombre_vialidad LIKE :to
                    ORDER BY nombre_vialidad";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':from', '%' . $from . '%', PDO::PARAM_STR);
            $stmt->bindValue(':to', '%' . $to . '%', PDO::PARAM_STR);
            $stmt->execute();
            $calles = $stmt->fetchAll();
            
            return [
                'from' => $from,
                'to' => $to,
                'calles_encontradas' => $calles,
                'distancia_estimada' => 'N/A',
                'tiempo_estimado' => 'N/A',
                'nota' => 'Implementación básica - requiere algoritmo de routing'
            ];
        } catch (PDOException $e) {
            $this->logError('Error calculando ruta: ' . $e->getMessage());
            return ['error' => 'Error calculando ruta'];
        }
    }

    /**
     * Obtener conexiones de una calle
     * @param string $calle Nombre de la calle
     * @return array Conexiones
     */
    public function obtenerConexiones($calle) {
        try {
            $sql = "SELECT DISTINCT calle_inicio, calle_fin FROM " . $this->table_name . " 
                    WHERE nombre_vialidad LIKE :calle OR calle_inicio LIKE :calle OR calle_fin LIKE :calle";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':calle', '%' . $calle . '%', PDO::PARAM_STR);
            $stmt->execute();
            $resultados = $stmt->fetchAll();
            
            $conexiones = [];
            foreach ($resultados as $resultado) {
                if ($resultado['calle_inicio']) {
                    $conexiones[] = $resultado['calle_inicio'];
                }
                if ($resultado['calle_fin']) {
                    $conexiones[] = $resultado['calle_fin'];
                }
            }
            
            return array_unique($conexiones);
        } catch (PDOException $e) {
            $this->logError('Error obteniendo conexiones: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Analizar calles por municipio
     * @return array Análisis por municipio
     */
    public function analizarPorMunicipio() {
        try {
            $sql = "SELECT municipio, 
                           COUNT(*) as total_calles,
                           COUNT(CASE WHEN via_principal = 'SI' THEN 1 END) as calles_principales,
                           COUNT(CASE WHEN tipo_vialidad = 'Avenida' THEN 1 END) as avenidas,
                           COUNT(CASE WHEN tipo_vialidad = 'Calle' THEN 1 END) as calles
                    FROM " . $this->table_name . " 
                    WHERE municipio IS NOT NULL AND municipio != ''
                    GROUP BY municipio 
                    ORDER BY total_calles DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->logError('Error analizando por municipio: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Analizar densidad de calles
     * @return array Análisis de densidad
     */
    public function analizarDensidad() {
        try {
            $sql = "SELECT tipo_vialidad, 
                           COUNT(*) as cantidad,
                           ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM " . $this->table_name . "), 2) as porcentaje
                    FROM " . $this->table_name . " 
                    WHERE tipo_vialidad IS NOT NULL AND tipo_vialidad != ''
                    GROUP BY tipo_vialidad 
                    ORDER BY cantidad DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->logError('Error analizando densidad: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Log de errores
     * @param string $message Mensaje de error
     */
    private function logError($message) {
        $logFile = Config::get('LOG_FILE', 'logs/app.log');
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [CALLES] {$message}" . PHP_EOL;
        
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
}
?>
