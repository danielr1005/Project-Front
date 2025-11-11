<?php
require_once '../config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

$chat_id = isset($_POST['chat_id']) ? (int)$_POST['chat_id'] : 0;
$mensaje = isset($_POST['mensaje']) ? trim($_POST['mensaje']) : '';

if ($chat_id <= 0 || empty($mensaje)) {
    echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
    exit;
}

$user = getCurrentUser();
$conn = getDBConnection();

// Verificar que el usuario es parte del chat
$stmt = $conn->prepare("SELECT comprador_id, producto_id FROM chats c 
                       INNER JOIN productos p ON c.producto_id = p.id 
                       WHERE c.id = ?");
$stmt->bind_param("i", $chat_id);
$stmt->execute();
$result = $stmt->get_result();
$chat = $result->fetch_assoc();
$stmt->close();

if (!$chat) {
    echo json_encode(['success' => false, 'error' => 'Chat no encontrado']);
    $conn->close();
    exit;
}

// Determinar si es comprador o vendedor
$es_comprador = $user['id'] == $chat['comprador_id'];
$stmt = $conn->prepare("SELECT vendedor_id FROM productos WHERE id = ?");
$stmt->bind_param("i", $chat['producto_id']);
$stmt->execute();
$producto_result = $stmt->get_result();
$producto = $producto_result->fetch_assoc();
$stmt->close();

$es_vendedor = $user['id'] == $producto['vendedor_id'];

if (!$es_comprador && !$es_vendedor) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    $conn->close();
    exit;
}

// Sanitizar mensaje
$mensaje = sanitize($mensaje);

// Insertar mensaje
$es_comprador_msg = $es_comprador ? 1 : 0;
$es_imagen = 0;

$stmt = $conn->prepare("INSERT INTO mensajes (es_comprador, chat_id, mensaje, es_imagen) 
                       VALUES (?, ?, ?, ?)");
$stmt->bind_param("iisi", $es_comprador_msg, $chat_id, $mensaje, $es_imagen);

if ($stmt->execute()) {
    $message_id = $conn->insert_id;
    
    // Actualizar visto
    if ($es_comprador) {
        $stmt2 = $conn->prepare("UPDATE chats SET visto_vendedor = 0, visto_comprador = 1 WHERE id = ?");
    } else {
        $stmt2 = $conn->prepare("UPDATE chats SET visto_comprador = 0, visto_vendedor = 1 WHERE id = ?");
    }
    $stmt2->bind_param("i", $chat_id);
    $stmt2->execute();
    $stmt2->close();
    
    // Obtener el mensaje completo
    $stmt3 = $conn->prepare("SELECT id, es_comprador, mensaje, fecha_registro FROM mensajes WHERE id = ?");
    $stmt3->bind_param("i", $message_id);
    $stmt3->execute();
    $result = $stmt3->get_result();
    $message = $result->fetch_assoc();
    $stmt3->close();
    
    // Convertir es_comprador según el usuario actual para la respuesta
    if ($es_vendedor) {
        $message['es_comprador'] = 0;
    } else {
        $message['es_comprador'] = 1;
    }
    
    echo json_encode(['success' => true, 'message' => $message]);
} else {
    echo json_encode(['success' => false, 'error' => 'Error al guardar mensaje']);
}

$stmt->close();
$conn->close();
?>

