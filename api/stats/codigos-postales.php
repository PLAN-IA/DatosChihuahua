<?php
// api/stats/codigos-postales.php - Endpoint para estadísticas de códigos postales

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
    
    $stats = [
        'total_codigos_postales' => $codigosPostales->contarCodigosPostales(),
        'por_zona' => $codigosPostales->contarPorZona(),
        'por_municipio' => $codigosPostales->contarPorMunicipio(),
        'por_estado' => $codigosPostales->contarPorEstado(),
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($stats, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("Error en stats/codigos-postales: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor', 'timestamp' => date('Y-m-d H:i:s')]);
}
?>
