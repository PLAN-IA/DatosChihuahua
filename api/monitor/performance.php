<?php
// api/monitor/performance.php - Endpoint para métricas de rendimiento

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
    
    $performance = [
        'memory' => [
            'current' => memory_get_usage(true),
            'peak' => memory_get_peak_usage(true),
            'limit' => ini_get('memory_limit')
        ],
        'execution_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'],
        'database_queries' => 'N/A',
        'cache_status' => 'N/A',
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($performance, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("Error en monitor/performance: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor', 'timestamp' => date('Y-m-d H:i:s')]);
}
?>
