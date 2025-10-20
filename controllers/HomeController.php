<?php
// controllers/HomeController.php - Controlador principal

require_once 'models/Calles.php';
require_once 'models/Servicios.php';
require_once 'models/CodigosPostales.php';
require_once 'config/Config.php';

class HomeController {
    private $calles;
    private $servicios;
    private $codigosPostales;

    public function __construct() {
        $this->calles = new Calles();
        $this->servicios = new Servicios();
        $this->codigosPostales = new CodigosPostales();
    }

    /**
     * Manejar búsqueda de calles
     */
    public function buscarCalles($query) {
        try {
            if (empty($query)) {
                return [
                    'success' => false,
                    'message' => 'Debe proporcionar un término de búsqueda',
                    'data' => []
                ];
            }

            $resultados = $this->calles->buscarCalles($query);
            
            return [
                'success' => true,
                'message' => 'Búsqueda completada',
                'data' => $resultados,
                'total' => count($resultados)
            ];

        } catch (InvalidArgumentException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error interno del servidor',
                'data' => []
            ];
        }
    }

    /**
     * Manejar búsqueda de servicios
     */
    public function buscarServicios($tipo, $colonia = null) {
        try {
            if (empty($tipo)) {
                return [
                    'success' => false,
                    'message' => 'Debe seleccionar un tipo de servicio',
                    'data' => []
                ];
            }

            $resultados = $this->servicios->buscarServicios($tipo, $colonia);
            
            return [
                'success' => true,
                'message' => 'Búsqueda completada',
                'data' => $resultados,
                'total' => count($resultados)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error interno del servidor',
                'data' => []
            ];
        }
    }

    /**
     * Obtener estadísticas del sistema
     */
    public function obtenerEstadisticas() {
        try {
            $estadisticas = [
                'total_calles' => $this->calles->contarCalles(),
                'calles_principales' => $this->calles->contarCalles(),
                'tipos_servicios' => count($this->servicios->obtenerTiposServicios()),
                'farmacias' => $this->servicios->contarServicios('farmacias'),
                'hospitales' => $this->servicios->contarServicios('hospitales'),
                'escuelas_primarias' => $this->servicios->contarServicios('escuelas_primarias'),
                'parques' => $this->servicios->contarServicios('parques')
            ];

            return [
                'success' => true,
                'data' => $estadisticas
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error obteniendo estadísticas',
                'data' => []
            ];
        }
    }

    /**
     * Obtener tipos de servicios disponibles
     */
    public function obtenerTiposServicios() {
        try {
            $tipos = $this->servicios->obtenerTiposServicios();
            
            return [
                'success' => true,
                'data' => $tipos
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error obteniendo tipos de servicios',
                'data' => []
            ];
        }
    }

    /**
     * Obtener tipos de vialidad
     */
    public function obtenerTiposVialidad() {
        try {
            $tipos = $this->calles->obtenerTiposVialidad();
            
            return [
                'success' => true,
                'data' => $tipos
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error obteniendo tipos de vialidad',
                'data' => []
            ];
        }
    }

    /**
     * Verificar estado del sistema
     */
    public function healthCheck() {
        try {
            $database = new Database();
            $dbStatus = $database->testConnection();
            
            return [
                'success' => true,
                'status' => 'OK',
                'database' => $dbStatus ? 'Connected' : 'Disconnected',
                'timestamp' => date('Y-m-d H:i:s'),
                'version' => Config::get('APP_VERSION', '1.0.0')
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'status' => 'ERROR',
                'message' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }

    /**
     * Manejar búsqueda de códigos postales
     */
    public function buscarCodigosPostales($query) {
        try {
            if (empty($query)) {
                return [
                    'success' => false,
                    'message' => 'Debe proporcionar un término de búsqueda',
                    'data' => []
                ];
            }

            $resultados = $this->codigosPostales->buscarGeneral($query, 50);
            
            return [
                'success' => true,
                'message' => 'Búsqueda completada',
                'data' => $resultados,
                'total' => count($resultados)
            ];

        } catch (InvalidArgumentException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error interno del servidor',
                'data' => []
            ];
        }
    }
}
?>
