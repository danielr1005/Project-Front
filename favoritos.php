<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();
$conn = getDBConnection();

/* ---------------------------
   🔄 AGREGAR / QUITAR FAVORITO
--------------------------- */
if (isset($_GET['producto_id'])) {
    $producto_id = (int)$_GET['producto_id'];
    $usuario_id = $user['id'];

    // Revisar si ya existe
    $stmt = $conn->prepare("SELECT id FROM favoritos WHERE votante_id = ? AND votado_id = ?");
    if (!$stmt) die("Error prepare: " . $conn->error);
    $stmt->bind_param("ii", $usuario_id, $producto_id);
    $stmt->execute();
    $existe = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($existe) {
        // Si ya existe → eliminar
        $stmt = $conn->prepare("DELETE FROM favoritos WHERE votante_id = ? AND votado_id = ?");
        if (!$stmt) die("Error prepare delete: " . $conn->error);
    } else {
        // Si no existe → agregar
        $stmt = $conn->prepare("INSERT INTO favoritos (votante_id, votado_id) VALUES (?, ?)");
        if (!$stmt) die("Error prepare insert: " . $conn->error);
    }

    $stmt->bind_param("ii", $usuario_id, $producto_id);
    if (!$stmt->execute()) die("Error execute: " . $stmt->error);
    $stmt->close();

    // Redirigir
    if (isset($_GET['redirect']) && $_GET['redirect'] === 'producto' && isset($_GET['id'])) {
        header("Location: producto.php?id=" . (int)$_GET['id']);
    } else {
        header("Location: favoritos.php");
    }
    exit;
}

/* ------------------------------------------------------
   📌 OBTENER FAVORITOS
---------------------------------------------------------*/
$query = "
    SELECT p.*, 
           c.nombre AS categoria_nombre,
           sc.nombre AS subcategoria_nombre,
           e.nombre AS estado_nombre
    FROM favoritos f
    INNER JOIN productos p ON p.id = f.votado_id
    INNER JOIN subcategorias sc ON p.subcategoria_id = sc.id
    INNER JOIN categorias c ON sc.categoria_id = c.id
    INNER JOIN estados e ON p.estado_id = e.id
    WHERE f.votante_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$productos_favoritos = $stmt->get_result();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Favoritos - Tu Mercado SENA</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <h1 class="logo">
                    <a href="index.php">
                        <img src="logo_new.png" class="logo-img" alt="Tu Mercado SENA">
                        Tu Mercado SENA
                    </a>
                </h1>
                <nav class="nav">
                    <a href="index.php">Menú Principal</a>
                    <a href="publicar.php">Publicar Producto</a>
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
            <div class="page-header">
                <h1>Mis Favoritos</h1>
            </div>
            
            <div class="products-grid">
                <?php if ($productos_favoritos->num_rows > 0): ?>
                    <?php while ($producto = $productos_favoritos->fetch_assoc()): ?>
                        <div class="product-card">
                            <a href="producto.php?id=<?php echo $producto['id']; ?>">
                                <?php if (!empty($producto['imagen'])): ?>
                                    <img src="data:<?php echo $producto['imagen_tipo']; ?>;base64,<?php echo base64_encode($producto['imagen']); ?>"
                                         alt="<?php echo htmlspecialchars($producto['nombre']); ?>"
                                         class="product-image">
                                <?php else: ?>
                                    <img src="images/placeholder.jpg"
                                         alt="Sin imagen"
                                         class="product-image">
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
                                <a href="favoritos.php?producto_id=<?php echo $producto['id']; ?>&toggle=1"
                                   class="btn-small"
                                   onclick="return confirm('¿Quieres quitar este producto de tus favoritos?');">
                                   Quitar de Favoritos
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-products">
                        <p>No has agregado productos a tus favoritos todavía.</p>
                        <a href="index.php" class="btn-primary">Explorar Productos</a>
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
