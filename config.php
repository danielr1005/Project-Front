<?php
date_default_timezone_set('America/Bogota');

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tu_mercado_sena_v3');



// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Conexión a la base de datos
function getDBConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die("Error de conexión: " . $conn->connect_error);
        }
        $conn->set_charset("utf8mb4");
        $conn->query("SET time_zone = '-05:00'");
        return $conn;
    } catch (Exception $e) {
        die("Error de conexión: " . $e->getMessage());
    }
}

// Verificar si el usuario está logueado
function isLoggedIn() {
    return isset($_SESSION['usuario_id']);
}

// Obtener información del usuario actual
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT u.*, c.correo, r.nombre as rol_nombre 
                           FROM usuarios u 
                           INNER JOIN correos c ON u.correo_id = c.id 
                           INNER JOIN roles r ON u.rol_id = r.id 
                           WHERE u.id = ?");
    $stmt->bind_param("i", $_SESSION['usuario_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    
    return $user;
}

// Sanitizar entrada
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Formatear precio
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' COP';
}
?>
