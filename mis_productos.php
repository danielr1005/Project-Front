<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();
$conn = getDBConnection();

// Obtener productos del usuario
$stmt = $conn->prepare("SELECT p.*, sc.nombre as subcategoria_nombre, c.nombre as categoria_nombre,
                       e.nombre as estado_nombre
                       FROM productos p
                       INNER JOIN subcategorias sc ON p.subcategoria_id = sc.id
                       INNER JOIN categorias c ON sc.categoria_id = c.id
                       INNER JOIN estados e ON p.estado_id = e.id
                       WHERE p.vendedor_id = ? ORDER BY p.fecha_registro DESC");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$productos_result = $stmt->get_result();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Productos - Tu Mercado SENA</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <h1 class="logo">
                <a href="index.php">
            <img src="logo.png"  class="logo-img">
                 Tu Mercado SENA
                            </a>
</h1>
                <nav class="nav">
                    <a href="mis_productos.php">Mis Productos</a>
                    <a href="publicar.php">Publicar Producto</a>
                    <a href="perfil.php">Perfil</a>
                    <div class="notification-badge">
                        <span class="notification-icon" id="notificationIcon" title="Chats y notificaciones">💬</span>
                        <span class="notification-count hidden" id="notificationCount">0</span>
                        <div class="chats-list" id="chatsList"></div>
                    </div>
                    <button class="theme-toggle" id="themeToggle" title="Cambiar tema">🌓</button>
                    <a href="logout.php">Cerrar Sesión</a>
                </nav>
            </div>
        </div>
    </header>

    <main class="main">
        <div class="container">
            <div class="page-header">
                <h1>Mis Productos</h1>
                <a href="publicar.php" class="btn-primary">Publicar Nuevo Producto</a>
            </div>
            
            <div class="products-grid">
                <?php if ($productos_result->num_rows > 0): ?>
                    <?php while ($producto = $productos_result->fetch_assoc()): ?>
                        <div class="product-card">
                            <a href="producto.php?id=<?php echo $producto['id']; ?>">
                                <?php if ($producto['con_imagen']): ?>
                                    <img src="uploads/img_<?php echo $producto['id']; ?>.jpg" 
                                         alt="<?php echo htmlspecialchars($producto['nombre']); ?>"
                                         class="product-image"
                                         onerror="this.src='images/placeholder.jpg'">
                                <?php else: ?>
                                    <div class="product-image placeholder">Sin imagen</div>
                                <?php endif; ?>
                                <div class="product-info">
                                    <h3 class="product-name"><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                                    <p class="product-price"><?php echo formatPrice($producto['precio']); ?></p>
                                    <p class="product-category"><?php echo htmlspecialchars($producto['categoria_nombre']); ?> - 
                                       <?php echo htmlspecialchars($producto['subcategoria_nombre']); ?></p>
                                    <span class="product-status status-<?php echo $producto['estado_id']; ?>">
                                        <?php echo htmlspecialchars($producto['estado_nombre']); ?>
                                    </span>
                                    <span class="product-stock">Disponibles: <?php echo $producto['disponibles']; ?></span>
                                </div>
                            </a>
                            <div class="product-actions">
                                <a href="editar_producto.php?id=<?php echo $producto['id']; ?>" class="btn-small">Editar</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-products">
                        <p>No has publicado ningún producto todavía.</p>
                        <a href="publicar.php" class="btn-primary">Publicar tu primer producto</a>
                    </div>
                <?php endif; ?>
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
