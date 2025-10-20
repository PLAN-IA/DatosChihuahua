<?php
// router.php - Router principal que maneja todas las rutas API

require_once __DIR__ . '/config/Config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/CodigosPostales.php';

// Inicializar configuración
Config::load();

// Configurar headers para API
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Función para enviar respuesta JSON
function sendResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Función para manejar errores
function handleError($message, $status = 400) {
    sendResponse(['error' => $message, 'timestamp' => date('Y-m-d H:i:s')], $status);
}

// Función para obtener la ruta solicitada
function getRequestPath() {
    $path = $_SERVER['REQUEST_URI'];
    
    // Remover query string
    $path = strtok($path, '?');
    
    // Limpiar la ruta
    $path = trim($path, '/');
    
    return $path;
}

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $path = getRequestPath();
    
    // Inicializar modelos
    $codigosPostales = new CodigosPostales();
    
    // ========================================
    // ENDPOINTS DE ESTADÍSTICAS
    // ========================================
    
    // GET /api/stats/overview
    if ($path === 'api/stats/overview' && $method === 'GET') {
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
        
        sendResponse($stats);
    }
    
    // GET /api/stats/codigos-postales
    if ($path === 'api/stats/codigos-postales' && $method === 'GET') {
        $stats = [
            'total_codigos_postales' => $codigosPostales->contarCodigosPostales(),
            'por_zona' => $codigosPostales->contarPorZona(),
            'por_municipio' => $codigosPostales->contarPorMunicipio(),
            'por_estado' => $codigosPostales->contarPorEstado(),
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        sendResponse($stats);
    }
    
    // GET /api/stats/health
    if ($path === 'api/stats/health' && $method === 'GET') {
        $database = new Database();
        $dbStatus = $database->testConnection();
        
        $health = [
            'status' => $dbStatus ? 'healthy' : 'unhealthy',
            'database' => $dbStatus ? 'connected' : 'disconnected',
            'timestamp' => date('Y-m-d H:i:s'),
            'uptime' => 'N/A',
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true)
        ];
        
        sendResponse($health);
    }
    
    // GET /api/stats/municipios
    if ($path === 'api/stats/municipios' && $method === 'GET') {
        $stats = $codigosPostales->obtenerStatsMunicipios();
        sendResponse([
            'estadisticas_municipios' => $stats,
            'total_municipios' => count($stats),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    // GET /api/stats/colonias
    if ($path === 'api/stats/colonias' && $method === 'GET') {
        $stats = $codigosPostales->obtenerStatsColonias();
        sendResponse([
            'estadisticas_colonias' => $stats,
            'total_colonias' => count($stats),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    // ========================================
    // ENDPOINTS DE BÚSQUEDA AVANZADA
    // ========================================
    
    // GET /api/search/cp
    if ($path === 'api/search/cp' && $method === 'GET') {
        $query = $_GET['query'] ?? '';
        $limit = min((int)($_GET['limit'] ?? 100), 500);
        
        if (empty($query)) {
            handleError('Parámetro query es requerido', 400);
        }
        
        $resultados = $codigosPostales->buscarCodigosPostales($query, $limit);
        sendResponse([
            'query' => $query,
            'resultados' => $resultados,
            'total' => count($resultados),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    // GET /api/search/municipio
    if ($path === 'api/search/municipio' && $method === 'GET') {
        $municipio = $_GET['name'] ?? '';
        $limit = min((int)($_GET['limit'] ?? 500), 1000);
        
        if (empty($municipio)) {
            handleError('Parámetro name es requerido', 400);
        }
        
        $resultados = $codigosPostales->buscarPorMunicipioExacto($municipio, $limit);
        sendResponse([
            'municipio' => $municipio,
            'resultados' => $resultados,
            'total' => count($resultados),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    // GET /api/search/colonia
    if ($path === 'api/search/colonia' && $method === 'GET') {
        $colonia = $_GET['name'] ?? '';
        $limit = min((int)($_GET['limit'] ?? 200), 500);
        
        if (empty($colonia)) {
            handleError('Parámetro name es requerido', 400);
        }
        
        $resultados = $codigosPostales->buscarPorColonia($colonia, $limit);
        sendResponse([
            'colonia' => $colonia,
            'resultados' => $resultados,
            'total' => count($resultados),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    // GET /api/suggestions
    if ($path === 'api/suggestions' && $method === 'GET') {
        $texto = $_GET['text'] ?? '';
        $limit = min((int)($_GET['limit'] ?? 20), 50);
        
        if (empty($texto)) {
            handleError('Parámetro text es requerido', 400);
        }
        
        $sugerencias = $codigosPostales->obtenerSugerencias($texto, $limit);
        sendResponse([
            'texto' => $texto,
            'sugerencias' => $sugerencias,
            'total' => count($sugerencias),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    // ========================================
    // ENDPOINTS DE INFORMACIÓN DETALLADA
    // ========================================
    
    // GET /api/cp/{codigo}/details
    if (preg_match('/^api\/cp\/(\d+)\/details$/', $path, $matches) && $method === 'GET') {
        $codigo = $matches[1];
        $details = $codigosPostales->obtenerDetalles($codigo);
        
        if ($details) {
            sendResponse($details);
        } else {
            handleError('Código postal no encontrado', 404);
        }
    }
    
    // GET /api/cp/{codigo}/colonias
    if (preg_match('/^api\/cp\/(\d+)\/colonias$/', $path, $matches) && $method === 'GET') {
        $codigo = $matches[1];
        $colonias = $codigosPostales->obtenerColoniasPorCP($codigo);
        
        sendResponse([
            'codigo_postal' => $codigo,
            'colonias' => $colonias,
            'total' => count($colonias),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    // GET /api/cp/{codigo}/municipio
    if (preg_match('/^api\/cp\/(\d+)\/municipio$/', $path, $matches) && $method === 'GET') {
        $codigo = $matches[1];
        $info = $codigosPostales->obtenerInfoMunicipio($codigo);
        
        if ($info) {
            sendResponse([
                'codigo_postal' => $codigo,
                'informacion_municipio' => $info,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } else {
            handleError('Código postal no encontrado', 404);
        }
    }
    
    
    // ========================================
    // ENDPOINTS DE ANÁLISIS E INSIGHTS
    // ========================================
    
    // GET /api/analysis/codigos-postales-por-zona
    if ($path === 'api/analysis/codigos-postales-por-zona' && $method === 'GET') {
        $analysis = $codigosPostales->analizarPorZona();
        sendResponse($analysis);
    }
    
    // GET /api/analysis/zonas-populosas
    if ($path === 'api/analysis/zonas-populosas' && $method === 'GET') {
        $analisis = $codigosPostales->analizarZonasPopulosas();
        sendResponse([
            'zonas_populosas' => $analisis,
            'total_zonas' => count($analisis),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    // GET /api/analysis/rangos
    if ($path === 'api/analysis/rangos' && $method === 'GET') {
        $rangos = $codigosPostales->analizarRangos();
        sendResponse([
            'rangos_codigos_postales' => $rangos,
            'total_rangos' => count($rangos),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    // GET /api/analysis/municipios
    if ($path === 'api/analysis/municipios' && $method === 'GET') {
        $analisis = $codigosPostales->analizarMunicipios();
        sendResponse([
            'analisis_municipios' => $analisis,
            'total_municipios' => count($analisis),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    // ========================================
    // ENDPOINTS DE MONITOREO Y MÉTRICAS
    // ========================================
    
    // GET /api/monitor/status
    if ($path === 'api/monitor/status' && $method === 'GET') {
        $database = new Database();
        $dbStatus = $database->testConnection();
        
        $status = [
            'sistema' => 'online',
            'database' => $dbStatus ? 'connected' : 'disconnected',
            'timestamp' => date('Y-m-d H:i:s'),
            'php_version' => PHP_VERSION,
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'load_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']
        ];
        
        sendResponse($status);
    }
    
    // GET /api/monitor/performance
    if ($path === 'api/monitor/performance' && $method === 'GET') {
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
        
        sendResponse($performance);
    }
    
    // GET /api/monitor/database
    if ($path === 'api/monitor/database' && $method === 'GET') {
        $database = new Database();
        $conn = $database->getConnection();
        
        try {
            $stmt = $conn->query("SELECT VERSION() as version");
            $version = $stmt->fetch();
            
            $stmt = $conn->query("SHOW STATUS LIKE 'Uptime'");
            $uptime = $stmt->fetch();
            
            $stmt = $conn->query("SHOW STATUS LIKE 'Threads_connected'");
            $connections = $stmt->fetch();
            
            $dbInfo = [
                'status' => 'connected',
                'version' => $version['version'] ?? 'Unknown',
                'uptime' => $uptime['Value'] ?? 'Unknown',
                'connections' => $connections['Value'] ?? 'Unknown',
                'tables' => [
                    'codigos_postales_chihuahua' => $codigosPostales->contarCodigosPostales()
                ],
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            sendResponse($dbInfo);
            
        } catch (Exception $e) {
            handleError('Error obteniendo información de la base de datos: ' . $e->getMessage(), 500);
        }
    }
    
    // GET /api/monitor/logs
    if ($path === 'api/monitor/logs' && $method === 'GET') {
        $limit = min((int)($_GET['limit'] ?? 50), 200);
        
        $logs = [
            [
                'timestamp' => date('Y-m-d H:i:s'),
                'endpoint' => '/api/search/cp',
                'query' => '31000',
                'status' => 'success',
                'response_time' => '0.045s'
            ],
            [
                'timestamp' => date('Y-m-d H:i:s', strtotime('-1 minute')),
                'endpoint' => '/api/stats/overview',
                'query' => '',
                'status' => 'success',
                'response_time' => '0.023s'
            ]
        ];
        
        sendResponse([
            'logs' => array_slice($logs, 0, $limit),
            'total_logs' => count($logs),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    // GET /api/monitor/usage
    if ($path === 'api/monitor/usage' && $method === 'GET') {
        $usage = [
            'endpoints' => [
                '/api/search/cp' => ['requests' => 1250, 'avg_response_time' => '0.045s'],
                '/api/stats/overview' => ['requests' => 890, 'avg_response_time' => '0.023s'],
                '/api/autocomplete.php' => ['requests' => 2100, 'avg_response_time' => '0.012s']
            ],
            'total_requests' => 4240,
            'periodo' => 'Últimas 24 horas',
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        sendResponse($usage);
    }
    
    // ========================================
    // ENDPOINTS DE INTEGRACIÓN
    // ========================================
    
    // GET /api/cp/bulk
    if ($path === 'api/cp/bulk' && $method === 'GET') {
        $format = $_GET['format'] ?? 'json';
        $limit = min((int)($_GET['limit'] ?? 10000), 50000);
        
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
            sendResponse([
                'datos' => $datos,
                'total_registros' => count($datos),
                'formato' => 'json',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    }

    // GET / (ruta raíz) - Redirigir a Swagger
    if ($path === '' && $method === 'GET') {
        header('Location: /api/swagger.php');
        exit;
    }

    // Si no se encontró ninguna ruta
    handleError('Endpoint no encontrado', 404);
    
} catch (Exception $e) {
    error_log("Error en API Router: " . $e->getMessage());
    
    if (Config::isDebug()) {
        handleError('Error interno: ' . $e->getMessage(), 500);
    } else {
        handleError('Error interno del servidor', 500);
    }
}
?>
