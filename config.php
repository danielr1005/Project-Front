<?php

// =========================================================
// CONFIGURACIÓN DE LA BASE DE DATOS Y TIEMPO
// =========================================================

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tu_mercado_sena_v3');

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =========================================================
// FUNCIONES DE CONEXIÓN Y UTILIDAD
// =========================================================

/**
 * Conexión a la base de datos
 */
function getDBConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die("Error de conexión: " . $conn->connect_error);
        }
        $conn->set_charset("utf8mb4");
        // Establece la zona horaria de la conexión SQL para que coincida con PHP
        $conn->query("SET time_zone = '-05:00'");
        return $conn;
    } catch (Exception $e) {
        die("Error de conexión: " . $e->getMessage());
    }
}
function getUserAvatarUrl($userId) {
    // Asegúrate de que esta ruta sea correcta
    $defaultAvatar = 'assests/images/avatars/defa.jpg'; 
    
    $conn = getDBConnection();
    // Solo necesitamos saber si el BLOB NO está vacío
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE id = ? AND avatar IS NOT NULL");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $stmt->close();
        // CLAVE: Devuelve la URL del script que lee la imagen desde la BD
        return 'api/get_avatar.php?id=' . $userId; 
    }

    $stmt->close();
    
    return $defaultAvatar; 
}

/**
 * Verifica si el usuario está logueado
 */
function isLoggedIn() {
    return isset($_SESSION['usuario_id']);
}

/**
 * Obtener información del usuario actual
 */
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
    
    return $user;
}
function isSellerFavorite($votante_id, $vendedor_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id FROM favoritos WHERE votante_id = ? AND votado_id = ?");
    $stmt->bind_param("ii", $votante_id, $vendedor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result->num_rows > 0;
    $stmt->close();
    return $exists;
}
function forceLightTheme() {
    echo "<script>
        localStorage.setItem('theme', 'light');
        document.documentElement.setAttribute('data-theme', 'light');
    </script>";
}

/**
 * Sanitizar entrada
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Formatear precio (Ej: 1.234.567 COP)
 */
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' COP';
}


// =========================================================
// FUNCIONES DE FECHA Y AVATAR
// =========================================================

/**
 * Formatea un timestamp de base de datos a tiempo relativo (Ej: hace 5 minutos)
 */
function formato_tiempo_relativo($timestamp_db) {
    // Configurar la zona horaria del servidor (¡MUY IMPORTANTE!)
    date_default_timezone_set('America/Bogota'); 
    
    $tiempo_mensaje = strtotime($timestamp_db);
    $tiempo_actual = time();
    $diferencia = $tiempo_actual - $tiempo_mensaje;

    $segundos_por_minuto = 60;
    $segundos_por_hora = 3600;
    $segundos_por_dia = 86400;

    if ($diferencia < 30) {
        return "Ahora";
    } elseif ($diferencia < $segundos_por_minuto) {
        return "hace " . $diferencia . " segundos";
    } elseif ($diferencia < ($segundos_por_minuto * 60)) {
        // Minutos
        $minutos = round($diferencia / $segundos_por_minuto);
        if ($minutos == 1) {
            return "hace 1 minuto";
        }
        return "hace " . $minutos . " minutos";
    } elseif ($diferencia < $segundos_por_dia) {
        // Horas
        $horas = round($diferencia / $segundos_por_hora);
        if ($horas == 1) {
            return "hace 1 hora";
        }
        return "hace " . $horas . " horas";
    } else {
        // Si es más de un día, mostramos la fecha corta
        return date('d M', $tiempo_mensaje); // Ej: 14 Nov
    }
}

/**
 * Obtiene la URL del avatar del usuario.
 * (Actualizado para usar una imagen por defecto)
 * @param int $userId El ID del usuario.
 * @return string La URL del avatar (o un placeholder).
 */

?>