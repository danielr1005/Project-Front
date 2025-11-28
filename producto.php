<?php

require_once 'config.php';

// Redirigir a login si no está autenticado
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$producto_id = (int)$_GET['id'];
$conn = getDBConnection();
$user = getCurrentUser();

// Obtener información del producto
$stmt = $conn->prepare("SELECT p.*, u.nombre as vendedor_nombre, u.id as vendedor_id, u.descripcion as vendedor_desc,
                       sc.nombre as subcategoria_nombre, c.nombre as categoria_nombre, 
                       i.nombre as integridad_nombre, i.descripcion as integridad_desc
                       FROM productos p
                       INNER JOIN usuarios u ON p.vendedor_id = u.id
                       INNER JOIN subcategorias sc ON p.subcategoria_id = sc.id
                       INNER JOIN categorias c ON sc.categoria_id = c.id
                       INNER JOIN integridad i ON p.integridad_id = i.id
                       WHERE p.id = ? AND p.estado_id = 1");
$stmt->bind_param("i", $producto_id);
$stmt->execute();
$result = $stmt->get_result();
$producto = $result->fetch_assoc();
$stmt->close();

if (!$producto) {
    header('Location: index.php');
    exit;
}

// Registrar vista si hay usuario logueado
if ($user && $user['id'] != $producto['vendedor_id']) {
    $stmt = $conn->prepare("INSERT IGNORE INTO vistos (usuario_id, producto_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user['id'], $producto_id);
    $stmt->execute();
    $stmt->close();
}

// Verificar si hay chat existente
$chat_existente = null;
if ($user && $user['id'] != $producto['vendedor_id']) {
    $stmt = $conn->prepare("SELECT id FROM chats WHERE comprador_id = ? AND producto_id = ? AND estado_id = 1");
    $stmt->bind_param("ii", $user['id'], $producto_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $chat_existente = $result->fetch_assoc();
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($producto['nombre']); ?> - Tu Mercado SENA</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
<h1 class="logo">
  <a href="index.php">
      <img src="logo_new.png"  class="logo-img">
      Tu Mercado SENA
  </a>
</h1>                <nav class="nav">
                    <a href="mis_productos.php">Mis Productos</a>
                    <a href="publicar.php">Publicar Producto</a>
                    <a href="index.php">Volver</a>
                    <div class="notification-badge">
                        <span class="notification-icon" id="notificationIcon" title="Chats y notificaciones">💬</span>
                        <span class="notification-count hidden" id="notificationCount">0</span>
                        <div class="chats-list" id="chatsList"></div>
                    </div>
                    <a href="perfil.php" class="perfil-link">
                    <div class="user-avatar-container">
                        <img src="<?php echo getUserAvatarUrl($user['id']); ?>" 
                             alt="Avatar de <?php echo htmlspecialchars($user['nombre']); ?>" 
                            class="user-avatar">
                        <span class="user-name-footer"><?php echo htmlspecialchars($user['nombre']); ?></span>
                    </div>
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <main class="main">
        <div class="container">
            <div class="product-detail">
 <div class="product-image-section">

<?php if (!empty($producto['imagen'])): ?>

    <img src="data:<?php echo $producto['imagen_tipo']; ?>;base64,<?php echo base64_encode($producto['imagen']); ?>"
         alt="<?php echo htmlspecialchars($producto['nombre']); ?>"
         class="product-detail-image">

<?php else: ?>

    <img src="images/placeholder.jpg"
         alt="Sin imagen"
         class="product-detail-image">

<?php endif; ?>

</div>
                <div class="product-detail-info">
                    <h1 class="product-detail-title"><?php echo htmlspecialchars($producto['nombre']); ?></h1>
                    <p class="product-detail-price"><?php echo formatPrice($producto['precio']); ?></p>
                    
                    <div class="product-meta">
                        <p><strong>Categoría:</strong> <?php echo htmlspecialchars($producto['categoria_nombre']); ?> - 
                           <?php echo htmlspecialchars($producto['subcategoria_nombre']); ?></p>
                        <p><strong>Condición:</strong> <?php echo htmlspecialchars($producto['integridad_nombre']); ?></p>
                        <p><strong>Disponibles:</strong> <?php echo $producto['disponibles']; ?></p>
                        <p><strong>Publicado:</strong> <?php echo date('d/m/Y', strtotime($producto['fecha_registro'])); ?></p>
                    </div>
                    
                    <div class="product-description">
                        <h3>Descripción</h3>
                        <p><?php echo nl2br(htmlspecialchars($producto['descripcion'])); ?></p>
                    </div>
                    
                    <div class="seller-info">
                        <h3>Vendedor</h3>
                        <p><strong><?php echo htmlspecialchars($producto['vendedor_nombre']); ?></strong></p>
                        <?php if ($producto['vendedor_desc']): ?>
                            <p><?php echo htmlspecialchars($producto['vendedor_desc']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-actions">
                        <?php if ($user['id'] == $producto['vendedor_id']): ?>
                            <a href="editar_producto.php?id=<?php echo $producto['id']; ?>" class="btn-secondary">Editar Producto</a>
                            <a href="eliminar_producto.php?id=<?php echo $producto['id']; ?>"class="btn-secondary"onclick="return confirm('¿Estás seguro de que quieres eliminar este producto? Esta acción no se puede deshacer.');">Eliminar Producto</a>
                        <?php else: ?>
                            <?php if ($chat_existente): ?>
                                <a href="chat.php?id=<?php echo $chat_existente['id']; ?>" class="btn-primary">Ver Conversación</a>
                            <?php else: ?>
                                <a href="contactar.php?producto_id=<?php echo $producto['id']; ?>" class="btn-primary">Contactar Vendedor</a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Tu Mercado SENA. Todos los derechos reservados.</p>
        </div>
    </footer>
    <script src="script.js"></script>
</body>
</html>

