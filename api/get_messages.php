<?php
require_once '../config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

$chat_id = isset($_GET['chat_id']) ? (int)$_GET['chat_id'] : 0;
$last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;

if ($chat_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Chat ID inválido']);
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

// Verificar permisos
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

// Obtener nuevos mensajes
$stmt = $conn->prepare("SELECT id, es_comprador, mensaje, fecha_registro 
                       FROM mensajes 
                       WHERE chat_id = ? AND id > ? 
                       ORDER BY fecha_registro ASC");
$stmt->bind_param("ii", $chat_id, $last_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    // Convertir es_comprador según el usuario actual para mostrar correctamente
    // Si es vendedor, invertimos el valor para que se muestre correctamente
    if ($es_vendedor) {
        $row['es_comprador'] = $row['es_comprador'] == 1 ? 0 : 1;
    }
    $messages[] = $row;
}

$stmt->close();

// Actualizar visto
if ($es_comprador) {
    $stmt = $conn->prepare("UPDATE chats SET visto_comprador = 1 WHERE id = ?");
} else {
    $stmt = $conn->prepare("UPDATE chats SET visto_vendedor = 1 WHERE id = ?");
}
$stmt->bind_param("i", $chat_id);
$stmt->execute();
$stmt->close();

$conn->close();

echo json_encode(['success' => true, 'messages' => $messages]);
?>

