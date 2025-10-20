<?php
// config/Config.php - Clase para manejar configuración

class Config {
    private static $config = [];
    private static $loaded = false;

    /**
     * Cargar configuración desde archivo .env
     */
    public static function load() {
        if (self::$loaded) {
            return;
        }

        $envFile = __DIR__ . '/../.env';
        
        if (!file_exists($envFile)) {
            // Si no existe .env, usar configuración por defecto
            self::$config = self::getDefaultConfig();
            self::$loaded = true;
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            if (strpos($line, '#') === 0) {
                continue; // Comentario
            }
            
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remover comillas si las tiene
                if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                    (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                    $value = substr($value, 1, -1);
                }
                
                self::$config[$key] = $value;
            }
        }

        self::$loaded = true;
    }

    /**
     * Obtener valor de configuración
     */
    public static function get($key, $default = null) {
        self::load();
        return isset(self::$config[$key]) ? self::$config[$key] : $default;
    }

    /**
     * Verificar si está en modo debug
     */
    public static function isDebug() {
        return self::get('APP_DEBUG', false) === 'true';
    }

    /**
     * Verificar si está en modo desarrollo
     */
    public static function isDevelopment() {
        return self::get('APP_ENV', 'production') === 'development';
    }

    /**
     * Obtener configuración de base de datos
     */
    public static function getDatabaseConfig() {
        return [
            'host' => self::get('DB_HOST', 'localhost'),
            'dbname' => self::get('DB_NAME', 'mapas_automatizacion'),
            'username' => self::get('DB_USER', 'root'),
            'password' => self::get('DB_PASSWORD', ''),
            'charset' => self::get('DB_CHARSET', 'utf8mb4'),
            'port' => self::get('DB_PORT', 3306)
        ];
    }

    /**
     * Configuración por defecto si no existe archivo .env
     */
    private static function getDefaultConfig() {
        return [
            'DB_HOST' => 'localhost',
            'DB_NAME' => 'mapas_automatizacion',
            'DB_USER' => 'root',
            'DB_PASSWORD' => '',
            'DB_CHARSET' => 'utf8mb4',
            'DB_PORT' => '3306',
            'APP_NAME' => 'Sistema de Servicios Privados',
            'APP_VERSION' => '1.0.0',
            'APP_ENV' => 'development',
            'APP_DEBUG' => 'true',
            'APP_URL' => 'http://localhost',
            'APP_KEY' => 'default_key_change_in_production',
            'SESSION_LIFETIME' => '120',
            'MAX_SEARCH_RESULTS' => '50',
            'DEFAULT_SEARCH_LIMIT' => '20',
            'LOG_LEVEL' => 'info',
            'LOG_FILE' => 'logs/app.log'
        ];
    }
}
?>
