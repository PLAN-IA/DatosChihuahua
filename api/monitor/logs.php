<?php
// api/monitor/logs.php - Endpoint para logs del sistema

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
    
    $limit = min((int)($_GET['limit'] ?? 50), 200);
    
    $logs = [
        [
            'timestamp' => date('Y-m-d H:i:s'),
            'endpoint' => '/api/search/cp.php',
            'query' => '31000',
            'status' => 'success',
            'response_time' => '0.045s'
        ],
        [
            'timestamp' => date('Y-m-d H:i:s', strtotime('-1 minute')),
            'endpoint' => '/api/stats/overview.php',
            'query' => '',
            'status' => 'success',
            'response_time' => '0.023s'
        ],
        [
            'timestamp' => date('Y-m-d H:i:s', strtotime('-2 minutes')),
            'endpoint' => '/api/suggestions.php',
            'query' => 'chi',
            'status' => 'success',
            'response_time' => '0.012s'
        ]
    ];
    
    echo json_encode([
        'logs' => array_slice($logs, 0, $limit),
        'total_logs' => count($logs),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("Error en monitor/logs: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor', 'timestamp' => date('Y-m-d H:i:s')]);
}
?>
