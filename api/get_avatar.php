<?php
// ¡Asegúrate de que este sea el primer carácter del archivo!
require_once '../config.php'; 

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("HTTP/1.0 400 Bad Request");
    exit;
}

$userId = (int)$_GET['id'];
$conn = getDBConnection();

$stmt = $conn->prepare("SELECT avatar, avatar_tipo FROM usuarios WHERE id = ?");

if (!$stmt) {
    header('Location: ../assests/images/avatars/defa.jpg'); // Fallback en error de SQL
    exit;
}

$stmt->bind_param("i", $userId);
$stmt->execute();

$avatarData = NULL;
$avatarType = 'image/jpeg';
$stmt->bind_result($avatarData, $avatarType);
$stmt->store_result();


if ($stmt->num_rows === 1 && $stmt->fetch() && !empty($avatarData)) {
    
    $stmt->close();
    $conn->close();

    // 🛑 CLAVE DE LIMPIEZA: Limpia cualquier salida residual o advertencia
    if (ob_get_contents()) ob_clean(); 
    flush(); 
    
    header("Content-Type: " . $avatarType);
    header("Content-Length: " . strlen($avatarData));
    header('Cache-Control: public, max-age=31536000'); 
    
    echo $avatarData;
    exit;
}

$stmt->close();
$conn->close();

// Fallback al default si no hay avatar o no se encuentra el ID
header('Location: ../assests/images/avatars/defa.jpg');
exit;

// ¡Asegúrate de que este sea el último carácter del archivo!
// NO CIERRES LA ETIQUETA PHP SI ES POSIBLE.
// ?>