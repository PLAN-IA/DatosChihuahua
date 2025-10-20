<?php
// api/analysis/zonas-populosas.php - Endpoint para análisis de zonas populosas

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
    
    $codigosPostales = new CodigosPostales();
    $analisis = $codigosPostales->analizarZonasPopulosas();
    
    echo json_encode([
        'zonas_populosas' => $analisis,
        'total_zonas' => count($analisis),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("Error en analysis/zonas-populosas: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor', 'timestamp' => date('Y-m-d H:i:s')]);
}
?>
