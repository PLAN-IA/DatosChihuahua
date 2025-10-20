<?php
// models/Servicios.php - Modelo para manejar servicios

require_once __DIR__ . '/../config/database.php';

class Servicios {
    private $conn;
    
    // Mapeo de tipos de servicios a tablas
    private $servicios_map = [
        'farmacias' => 'servicios_chihuahua',
        'hospitales' => 'servicios_chihuahua',
        'gasolineras' => 'servicios_chihuahua',
        'universidades' => 'servicios_chihuahua',
        'institutos' => 'servicios_chihuahua'
    ];

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Buscar servicios por nombre general (para autocompletado)
    public function buscarServiciosPorNombre($query, $limit = 10) {
        $sql = "SELECT nombre_servicio, tipo_servicio, direccion, municipio 
                FROM servicios_chihuahua 
                WHERE nombre_servicio LIKE :query1 
                   OR tipo_servicio LIKE :query2
                   OR municipio LIKE :query3
                ORDER BY 
                    CASE 
                        WHEN nombre_servicio LIKE :query4 THEN 1
                        WHEN tipo_servicio LIKE :query5 THEN 2
                        ELSE 3
                    END,
                    nombre_servicio
                LIMIT :limit";
        
        $stmt = $this->conn->prepare($sql);
        $searchTerm = "%" . $query . "%";
        $stmt->bindValue(':query1', $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(':query2', $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(':query3', $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(':query4', $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(':query5', $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        $resultados = $stmt->fetchAll();
        $suggestions = [];
        
        foreach ($resultados as $servicio) {
            $suggestions[] = [
                'nombre' => $servicio['nombre_servicio'],
                'tipo' => $servicio['tipo_servicio'],
                'direccion' => $servicio['direccion'] ?? '',
                'municipio' => $servicio['municipio']
            ];
        }
        
        return $suggestions;
    }
    
    // Método auxiliar para buscar en una tabla específica
    private function buscarEnTabla($tabla, $query, $limit) {
        try {
            $sql = "SELECT nombre, direccion FROM " . $tabla . " 
                    WHERE nombre LIKE :query 
                    ORDER BY nombre 
                    LIMIT :limit";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':query', "%" . $query . "%", PDO::PARAM_STR);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    // Buscar servicios por tipo y ubicación
    public function buscarServicios($tipo, $colonia = null, $limit = 20) {
        if (!isset($this->servicios_map[$tipo])) {
            return ['error' => 'Tipo de servicio no válido', 'tipos_disponibles' => array_keys($this->servicios_map)];
        }
        
        $tabla = $this->servicios_map[$tipo];
        $sql = "SELECT * FROM " . $tabla;
        $params = [];
        
        if ($colonia) {
            $sql .= " WHERE nombre LIKE :colonia OR direccion LIKE :colonia OR colonia LIKE :colonia";
            $params[':colonia'] = "%" . $colonia . "%";
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

    // Obtener tipos de servicios disponibles
    public function obtenerTiposServicios() {
        return array_keys($this->servicios_map);
    }

    // Buscar farmacias específicamente
    public function buscarFarmacias($colonia = null, $limit = 20) {
        return $this->buscarServicios('farmacias', $colonia, $limit);
    }

    // Buscar hospitales específicamente
    public function buscarHospitales($colonia = null, $limit = 20) {
        return $this->buscarServicios('hospitales', $colonia, $limit);
    }

    // Buscar escuelas primarias específicamente
    public function buscarEscuelasPrimarias($colonia = null, $limit = 20) {
        return $this->buscarServicios('escuelas_primarias', $colonia, $limit);
    }

    // Buscar parques específicamente
    public function buscarParques($colonia = null, $limit = 20) {
        return $this->buscarServicios('parques', $colonia, $limit);
    }

    // Contar servicios por tipo
    public function contarServicios($tipo) {
        if (!isset($this->servicios_map[$tipo])) {
            return 0;
        }
        
        $tabla = $this->servicios_map[$tipo];
        $sql = "SELECT COUNT(*) as total FROM " . $tabla;
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result['total'];
    }
}
?>
