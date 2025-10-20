<?php
// index.php - Punto de entrada principal del sistema

// Incluir archivos necesarios PRIMERO
require_once 'config/Config.php';
require_once 'config/database.php';
require_once 'controllers/HomeController.php';
require_once 'views/home.php';

// Inicializar configuración
Config::load();

// Configuración de errores DESPUÉS de cargar Config
error_reporting(E_ALL);
ini_set('display_errors', Config::isDebug() ? 1 : 0);

try {
    $controller = new HomeController();
    $data = [];
    $error_message = '';

    // Procesar búsqueda de calles
    if (isset($_GET['buscar_calle']) && !empty($_GET['buscar_calle'])) {
        $resultado = $controller->buscarCalles($_GET['buscar_calle']);
        
        if ($resultado['success']) {
            $data['resultados_calles'] = $resultado['data'];
            $data['busqueda_calle'] = $_GET['buscar_calle'];
        } else {
            $error_message = $resultado['message'];
            $data['busqueda_calle'] = $_GET['buscar_calle'];
        }
    }

    // Procesar búsqueda de servicios
    if (isset($_GET['buscar_servicio']) && !empty($_GET['tipo_servicio'])) {
        $resultado = $controller->buscarServicios($_GET['tipo_servicio'], $_GET['colonia_servicio'] ?? '');
        
        if ($resultado['success']) {
            $data['resultados_servicios'] = $resultado['data'];
            $data['tipo_servicio'] = $_GET['tipo_servicio'];
            $data['colonia_servicio'] = $_GET['colonia_servicio'] ?? '';
        } else {
            $error_message = $resultado['message'];
            $data['tipo_servicio'] = $_GET['tipo_servicio'];
            $data['colonia_servicio'] = $_GET['colonia_servicio'] ?? '';
        }
    }

    // Procesar búsqueda de códigos postales
    if (isset($_GET['buscar_cp']) && !empty($_GET['buscar_codigo_postal'])) {
        $resultado = $controller->buscarCodigosPostales($_GET['buscar_codigo_postal']);
        
        if ($resultado['success']) {
            $data['resultados_codigos_postales'] = $resultado['data'];
            $data['busqueda_codigo_postal'] = $_GET['buscar_codigo_postal'];
        } else {
            $error_message = $resultado['message'];
            $data['busqueda_codigo_postal'] = $_GET['buscar_codigo_postal'];
        }
    }

    // Obtener datos necesarios para la vista
    $tipos_servicios_result = $controller->obtenerTiposServicios();
    $data['tipos_servicios'] = $tipos_servicios_result['success'] ? $tipos_servicios_result['data'] : [];

    $estadisticas_result = $controller->obtenerEstadisticas();
    $data['estadisticas'] = $estadisticas_result['success'] ? $estadisticas_result['data'] : [];

    $data['error_message'] = $error_message;

    // Renderizar vista
    renderHomePage($data);

} catch (Exception $e) {
    // Manejo de errores globales
    $error_data = [
        'error_message' => Config::isDebug() ? $e->getMessage() : 'Error interno del servidor',
        'tipos_servicios' => [],
        'estadisticas' => []
    ];
    
    renderHomePage($error_data);
}
?>

