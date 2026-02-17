<?php
// includes/db.php
$host = 'localhost';
$dbname = 'teacher_portal';  // Or your DB name
$user = 'root';
$pass = '';
require_once __DIR__ . '/../config/config.php';

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            file_put_contents('logs/db_error.log', date('Y-m-d H:i:s') . " - Database connection failed: " . $e->getMessage() . "\n", FILE_APPEND);
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance->conn;
    }
}
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    // High-quality: Enable strict mode for better security
    $pdo->exec("SET sql_mode = 'STRICT_ALL_TABLES'");
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}

// Get database connection
function getDB() {
    return Database::getInstance();
}
?>
