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
    public function buscarPorAsentamiento($query, $limit = 10) {
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
    public function buscarPorCodigo($codigo, $limit = 10) {
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
    public function buscarPorMunicipio($municipio, $limit = 20) {
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
    public function buscarGeneral($query, $limit = 10) {
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
    
    // Obtener códigos postales por zona
    public function obtenerPorZona($zona, $limit = 20) {
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
}
?>
