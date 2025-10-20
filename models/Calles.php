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
        $this->max_results = Config::get('MAX_SEARCH_RESULTS', 50);
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

        $limit = $limit ?? Config::get('DEFAULT_SEARCH_LIMIT', 20);
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
        $sql = "SELECT t_vial2 as tipo, COUNT(*) as cantidad
                FROM " . $this->table_name . " 
                WHERE t_vial2 IS NOT NULL AND t_vial2 != ''
                GROUP BY t_vial2
                ORDER BY cantidad DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    // Buscar intersecciones
    public function buscarIntersecciones($calle1, $calle2 = null, $limit = 20) {
        $sql = "SELECT id, nombre, calle1, calle2, v_ppal, cla_calle
                FROM " . $this->table_name . " 
                WHERE calle1 LIKE :calle1 OR calle2 LIKE :calle1";
        
        $params = [':calle1' => "%" . $calle1 . "%"];
        
        if ($calle2) {
            $sql .= " OR calle1 LIKE :calle2 OR calle2 LIKE :calle2";
            $params[':calle2'] = "%" . $calle2 . "%";
        }
        
        $sql .= " ORDER BY nombre LIMIT :limit";
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
                $sql = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE nombre LIKE :query";
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
