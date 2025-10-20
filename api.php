<?php
// api.php - Punto de entrada para todas las rutas API

// Obtener la ruta solicitada
$requestUri = $_SERVER['REQUEST_URI'];
$scriptName = $_SERVER['SCRIPT_NAME'];

// Remover el script name de la ruta
if (strpos($requestUri, $scriptName) === 0) {
    $requestUri = substr($requestUri, strlen($scriptName));
}

// Remover query string
$requestUri = strtok($requestUri, '?');

// Limpiar la ruta
$requestUri = trim($requestUri, '/');

// Si es una ruta API, ejecutar el router
if (strpos($requestUri, 'api/') === 0) {
    // Simular las variables del servidor para el router
    $_SERVER['REQUEST_URI'] = '/' . $requestUri;
    $_SERVER['SCRIPT_NAME'] = '/api/router.php';
    
    // Incluir el router
    require_once 'router.php';
} else {
    // Si no es una ruta API, mostrar error
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Endpoint no encontrado', 'timestamp' => date('Y-m-d H:i:s')]);
}
?>
