<?php
// config/database.php - Configuración mejorada de la base de datos

require_once 'Config.php';

class Database {
    private $conn;
    private $config;

    public function __construct() {
        $this->config = Config::getDatabaseConfig();
    }

    /**
     * Obtener conexión a la base de datos
     */
    public function getConnection() {
        if ($this->conn === null) {
            $this->conn = $this->createConnection();
        }
        
        return $this->conn;
    }

    /**
     * Crear nueva conexión PDO
     */
    private function createConnection() {
        try {
            $dsn = sprintf(
                "mysql:host=%s;port=%d;dbname=%s;charset=%s",
                $this->config['host'],
                $this->config['port'],
                $this->config['dbname'],
                $this->config['charset']
            );

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . $this->config['charset']
            ];

            $this->conn = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                $options
            );

            return $this->conn;

        } catch(PDOException $e) {
            $this->logError('Error de conexión a la base de datos: ' . $e->getMessage());
            
            if (Config::isDebug()) {
                throw new Exception("Error de conexión: " . $e->getMessage());
            } else {
                throw new Exception("Error interno del servidor");
            }
        }
    }

    /**
     * Verificar conexión
     */
    public function testConnection() {
        try {
            $conn = $this->getConnection();
            $stmt = $conn->query("SELECT 1");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Cerrar conexión
     */
    public function closeConnection() {
        $this->conn = null;
    }

    /**
     * Log de errores
     */
    private function logError($message) {
        $logFile = Config::get('LOG_FILE', 'logs/app.log');
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}" . PHP_EOL;
        
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }

    /**
     * Destructor para cerrar conexión
     */
    public function __destruct() {
        $this->closeConnection();
    }
}
?>
