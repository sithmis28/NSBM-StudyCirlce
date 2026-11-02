<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'nsbm_newsdesk');

$pdo = null;
$conn = null;
$connectedPort = null;

$ports = [3307, 3306];

foreach ($ports as $port) {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";port={$port};dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        $connectedPort = $port;

        $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, $port);
        if ($conn && !$conn->connect_error) {
            $conn->set_charset('utf8mb4');
        }
        break;
    } catch (Exception $e) {

    }
}

if (!$pdo) {
    die("<h3>Database Connection Error</h3><p>Unable to connect to MySQL database (" . htmlspecialchars(DB_NAME) . "). Please ensure MySQL is started in XAMPP Control Panel.</p>");
}

function getDB() {
    global $pdo;
    return $pdo;
}
?>

