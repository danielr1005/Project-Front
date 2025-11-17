<?php 

require_once 'config.php';

if(!isLoggedIn()) {
    header('Location:login.php');
    exit;
}

if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    header('Location:index.php');
    exit;
}

$producto_id = (int)$_GET['id'];
$user = getCurrentUser();
$conn = getDBConnection();

$stmt = $conn ->prepare("SELECT vendedor_id, con_imagen FROM productos WHERE id = ?");
$stmt->bind_param("i", $producto_id);
$stmt->execute();
$result = $stmt->get_result();
$producto = $result->fetch_assoc();
$stmt->close();

if(!$producto){
    header('Location:index.php?error=producto_no_encontrado');
    exit;
}

if($producto['vendedor_id']!=$user['id']){
    header('Location:index.php?error=sin_permiso');
    exit;
}

$stmt= $conn->prepare("DELETE FROM productos WHERE id= ?");
$stmt->bind_param("i",$producto_id);
$stmt->execute();
$stmt->close();

if(!empty($producto['con_imagen'])){
    $image_path = "uploads/img_{$producto_id}.jpg";
if(file_exists($image_path)){
    unlink($image_path);
}
}
$conn -> close();

header('Location:mis_productos.php?mensaje=producto_eliminado');


?>