<?php
// api/monitor/database.php - Endpoint para estado de la base de datos

require_once __DIR__ . '/../../config/Config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/CodigosPostales.php';

// Inicializar configuración
Config::load();

// Configurar headers para API
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method !== 'GET') {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido', 'timestamp' => date('Y-m-d H:i:s')]);
        exit;
    }
    
    $database = new Database();
    $conn = $database->getConnection();
    
    try {
        $stmt = $conn->query("SELECT VERSION() as version");
        $version = $stmt->fetch();
        
        $stmt = $conn->query("SHOW STATUS LIKE 'Uptime'");
        $uptime = $stmt->fetch();
        
        $stmt = $conn->query("SHOW STATUS LIKE 'Threads_connected'");
        $connections = $stmt->fetch();
        
        $codigosPostales = new CodigosPostales();
        
        $dbInfo = [
            'status' => 'connected',
            'version' => $version['version'] ?? 'Unknown',
            'uptime' => $uptime['Value'] ?? 'Unknown',
            'connections' => $connections['Value'] ?? 'Unknown',
            'tables' => [
                'codigos_postales_chihuahua' => $codigosPostales->contarCodigosPostales()
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        echo json_encode($dbInfo, JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error obteniendo información de la base de datos: ' . $e->getMessage(), 'timestamp' => date('Y-m-d H:i:s')]);
    }
    
} catch (Exception $e) {
    error_log("Error en monitor/database: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor', 'timestamp' => date('Y-m-d H:i:s')]);
}
?>
