<?php
// api/stats/health.php - Endpoint para estado de salud del sistema

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
    $dbStatus = $database->testConnection();
    
    $health = [
        'status' => $dbStatus ? 'healthy' : 'unhealthy',
        'database' => $dbStatus ? 'connected' : 'disconnected',
        'timestamp' => date('Y-m-d H:i:s'),
        'uptime' => 'N/A',
        'memory_usage' => memory_get_usage(true),
        'memory_peak' => memory_get_peak_usage(true)
    ];
    
    echo json_encode($health, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("Error en stats/health: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor', 'timestamp' => date('Y-m-d H:i:s')]);
}
?>
