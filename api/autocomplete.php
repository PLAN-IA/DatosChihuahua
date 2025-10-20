<?php
// api/autocomplete.php - Endpoint para autocompletado

require_once __DIR__ . '/../config/Config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Calles.php';
require_once __DIR__ . '/../models/CodigosPostales.php';

// Inicializar configuración
Config::load();

// Configurar headers para API
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Función para enviar respuesta JSON
function sendResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Función para manejar errores
function handleError($message, $status = 400) {
    sendResponse(['error' => $message], $status);
}

try {
    // Verificar método HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        handleError('Método no permitido', 405);
    }

    // Obtener parámetros
    $query = $_GET['q'] ?? '';
    $type = $_GET['type'] ?? 'codigos_postales'; // Solo codigos_postales disponibles
    $limit = min((int)($_GET['limit'] ?? 50), 1000); // Máximo 1000 resultados

    // Validar parámetros
    if (empty($query) || strlen(trim($query)) < 2) {
        sendResponse(['suggestions' => []]);
    }

    $query = trim($query);

    // Procesar según el tipo
    if ($type === 'codigos_postales') {
        $codigosPostales = new CodigosPostales();
        $resultados = $codigosPostales->buscarGeneral($query, $limit);
        
        $suggestions = [];
        foreach ($resultados as $cp) {
            $suggestions[] = [
                'value' => $cp['d_codigo'] . ' - ' . $cp['d_asenta'],
                'label' => $cp['d_codigo'] . ' - ' . $cp['d_asenta'],
                'codigo_postal' => $cp['d_codigo'],
                'asentamiento' => $cp['d_asenta'],
                'municipio' => $cp['D_mnpio'],
                'zona' => $cp['d_zona']
            ];
        }
        
    } else {
        handleError('Tipo de búsqueda no válido. Solo se permite: codigos_postales');
    }

    // Enviar respuesta exitosa
    sendResponse([
        'suggestions' => $suggestions,
        'query' => $query,
        'count' => count($suggestions)
    ]);

} catch (Exception $e) {
    error_log("Error en autocompletado: " . $e->getMessage());
    
    if (Config::isDebug()) {
        handleError('Error interno: ' . $e->getMessage(), 500);
    } else {
        handleError('Error interno del servidor', 500);
    }
}
?>
