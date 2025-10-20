<?php
// api/cp/bulk.php - Endpoint para exportar datos

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
    
    $format = $_GET['format'] ?? 'json';
    $limit = min((int)($_GET['limit'] ?? 10000), 50000);
    
    $codigosPostales = new CodigosPostales();
    $datos = $codigosPostales->obtenerTodos($limit);
    
    if ($format === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="codigos_postales_chihuahua.csv"');
        
        $output = fopen('php://output', 'w');
        
        fputcsv($output, ['codigo_postal', 'asentamiento', 'tipo_asentamiento', 'municipio', 'estado', 'zona', 'ciudad']);
        
        foreach ($datos as $row) {
            fputcsv($output, [
                $row['d_codigo'],
                $row['d_asenta'],
                $row['d_tipo_asenta'],
                $row['D_mnpio'],
                $row['d_estado'],
                $row['d_zona'],
                $row['d_ciudad']
            ]);
        }
        
        fclose($output);
        exit;
    } else {
        echo json_encode([
            'datos' => $datos,
            'total_registros' => count($datos),
            'formato' => 'json',
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_UNESCAPED_UNICODE);
    }
    
} catch (Exception $e) {
    error_log("Error en cp/bulk: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor', 'timestamp' => date('Y-m-d H:i:s')]);
}
?>
