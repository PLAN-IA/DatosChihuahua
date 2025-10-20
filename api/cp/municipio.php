<?php
// api/cp/municipio.php - Endpoint para información del municipio

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
    
    $codigo = $_GET['codigo'] ?? '';
    
    if (empty($codigo)) {
        http_response_code(400);
        echo json_encode(['error' => 'Parámetro codigo es requerido', 'timestamp' => date('Y-m-d H:i:s')]);
        exit;
    }
    
    $codigosPostales = new CodigosPostales();
    $info = $codigosPostales->obtenerInfoMunicipio($codigo);
    
    if ($info) {
        echo json_encode([
            'codigo_postal' => $codigo,
            'informacion_municipio' => $info,
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Código postal no encontrado', 'timestamp' => date('Y-m-d H:i:s')]);
    }
    
} catch (Exception $e) {
    error_log("Error en cp/municipio: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor', 'timestamp' => date('Y-m-d H:i:s')]);
}
?>
