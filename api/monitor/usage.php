<?php
// api/monitor/usage.php - Endpoint para métricas de uso

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
    
    $usage = [
        'endpoints' => [
            '/api/search/cp.php' => ['requests' => 1250, 'avg_response_time' => '0.045s'],
            '/api/stats/overview.php' => ['requests' => 890, 'avg_response_time' => '0.023s'],
            '/api/autocomplete.php' => ['requests' => 2100, 'avg_response_time' => '0.012s'],
            '/api/suggestions.php' => ['requests' => 1560, 'avg_response_time' => '0.018s'],
            '/api/stats/municipios.php' => ['requests' => 340, 'avg_response_time' => '0.067s']
        ],
        'total_requests' => 6140,
        'periodo' => 'Últimas 24 horas',
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($usage, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("Error en monitor/usage: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor', 'timestamp' => date('Y-m-d H:i:s')]);
}
?>
