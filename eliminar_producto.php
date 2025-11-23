<?php 

require_once 'config.php';

// 1. Verificación de seguridad y autenticación
if(!isLoggedIn()) {
    header('Location:login.php');
    exit;
}

// 2. Verificación de ID del producto
if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    header('Location:index.php');
    exit;
}

$producto_id = (int)$_GET['id'];
$user = getCurrentUser();
$conn = getDBConnection();

// 3. Buscar datos del producto y verificar permisos
$stmt = $conn->prepare("SELECT vendedor_id, con_imagen FROM productos WHERE id = ?");
$stmt->bind_param("i", $producto_id);
$stmt->execute();
$result = $stmt->get_result();
$producto = $result->fetch_assoc();
$stmt->close();

if(!$producto){
    header('Location:index.php?error=producto_no_encontrado');
    exit;
}

if($producto['vendedor_id'] != $user['id']){
    header('Location:index.php?error=sin_permiso');
    exit;
}

/* ----------------------------------------------------
    4. INICIO DE TRANSACCIÓN: ELIMINACIÓN EN CASCADA SEGURA
    (Asegura que si una eliminación falla, ninguna se complete)
------------------------------------------------------*/
$success = false;
// Iniciar la transacción
$conn->begin_transaction(); 

try {
    
    // A. Eliminar Mensajes (Depende de chats)
    // Usa un subquery para borrar todos los mensajes de todos los chats de este producto de una vez.
    $sql_mensajes = "
        DELETE FROM mensajes 
        WHERE chat_id IN (
            SELECT id FROM chats WHERE producto_id = ?
        )
    ";
    $stmt = $conn->prepare($sql_mensajes);
    $stmt->bind_param("i", $producto_id);
    $stmt->execute();
    $stmt->close();

    // B. Eliminar Chats (Depende de productos)
    $sql_chats = "DELETE FROM chats WHERE producto_id = ?";
    $stmt = $conn->prepare($sql_chats);
    $stmt->bind_param("i", $producto_id);
    $stmt->execute();
    $stmt->close();
    
    // C. Eliminar Vistos (La tabla que causó el error #1451)
    $sql_vistos = "DELETE FROM vistos WHERE producto_id = ?";
    $stmt = $conn->prepare($sql_vistos);
    $stmt->bind_param("i", $producto_id);
    $stmt->execute();
    $stmt->close();

    // D. Eliminar el Producto (Ahora que todas las dependencias se han ido)
    $sql_producto = "DELETE FROM productos WHERE id = ?";
    $stmt = $conn->prepare($sql_producto);
    $stmt->bind_param("i", $producto_id);
    $stmt->execute();
    $stmt->close();
    
    // Si todas las consultas fueron exitosas, confirma los cambios
    $conn->commit(); 
    $success = true;

} catch (Exception $e) {
    // Si algo falla, deshace todos los cambios
    $conn->rollback();
    // Opcional: Para debugging, puedes imprimir o loguear $e->getMessage();
}

/* ----------------------------------------------------
    5. ELIMINAR ARCHIVO FÍSICO Y REDIRECCIÓN
------------------------------------------------------*/
if ($success) {
    // Eliminar imagen si existe
    if(!empty($producto['con_imagen'])){
        $image_path = "uploads/img_{$producto_id}.jpg";
        if(file_exists($image_path)) {
            unlink($image_path);
        }
    }

    $conn->close();
    // Redirigir al éxito
    header('Location:mis_productos.php?mensaje=producto_eliminado');
} else {
    $conn->close();
    // Redirigir al error de eliminación
    header('Location:mis_productos.php?error=eliminacion_fallida');
}

exit;
?>