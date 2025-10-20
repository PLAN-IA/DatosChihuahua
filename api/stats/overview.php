<?php
// api/stats/overview.php - Endpoint para estadísticas generales

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
        'sistema' => [
            'nombre' => Config::get('APP_NAME', 'Sistema de Códigos Postales'),
            'version' => Config::get('APP_VERSION', '1.0.0'),
            'timestamp' => date('Y-m-d H:i:s'),
            'entorno' => Config::get('APP_ENV', 'production')
        ],
        'codigos_postales' => [
            'total' => $codigosPostales->contarCodigosPostales(),
            'por_zona' => $codigosPostales->contarPorZona(),
            'por_municipio' => $codigosPostales->contarPorMunicipio()
        ],
        'base_datos' => [
            'conectada' => true,
            'tablas' => ['codigos_postales_chihuahua']
        ]
    ];
    
    echo json_encode($stats, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("Error en stats/overview: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor', 'timestamp' => date('Y-m-d H:i:s')]);
}
?>
