<?php
require_once 'config.php';

// Redirigir a login si no está autenticado
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$conn = getDBConnection();
$user = getCurrentUser();

// Filtros
$categoria_id = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;
$busqueda = isset($_GET['busqueda']) ? sanitize($_GET['busqueda']) : '';

// Query de productos
$query = "SELECT p.*, u.nombre as vendedor_nombre, sc.nombre as subcategoria_nombre, 
          c.nombre as categoria_nombre, i.nombre as integridad_nombre
          FROM productos p
          INNER JOIN usuarios u ON p.vendedor_id = u.id
          INNER JOIN subcategorias sc ON p.subcategoria_id = sc.id
          INNER JOIN categorias c ON sc.categoria_id = c.id
          INNER JOIN integridad i ON p.integridad_id = i.id
          WHERE p.estado_id = 1 AND u.estado_id = 1";

$params = [];
$types = '';

if ($categoria_id > 0) {
    $query .= " AND c.id = ?";
    $params[] = $categoria_id;
    $types .= 'i';
}

if (!empty($busqueda)) {
    $query .= " AND (p.nombre LIKE ? OR p.descripcion LIKE ?)";
    $search_term = "%$busqueda%";
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= 'ss';
}

$query .= " ORDER BY p.fecha_registro DESC LIMIT 50";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$productos_result = $stmt->get_result();

// Obtener categorías para el filtro (después de usar la conexión)
$categorias_query = "SELECT * FROM categorias ORDER BY nombre";
$categorias_result = $conn->query($categorias_query);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu Mercado SENA - Marketplace</title>
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
</h1>
<nav class="nav">

                    <a href="mis_productos.php">Mis Productos</a>
                    <a href="favoritos.php">Favoritos</a>
                    <a href="publicar.php">Publicar Producto</a>
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
            <div class="filters-section">
                <form method="GET" action="index.php" class="filters-form">
                    <div class="filter-group">
                        <input type="text" name="busqueda" placeholder="Buscar productos..." 
                               value="<?php echo htmlspecialchars($busqueda); ?>" class="search-input">
                    </div>
                    <div class="filter-group">
                        <select name="categoria" class="select-input">
                            <option value="0">Categorías</option>
                            <?php while ($cat = $categorias_result->fetch_assoc()): ?>
                                <option value="<?php echo $cat['id']; ?>" 
                                        <?php echo $categoria_id == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['nombre']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn-secondary">Buscar</button>
                    <?php if ($categoria_id > 0 || !empty($busqueda)): ?>
                        <a href="index.php" class="btn-link">Limpiar filtros</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="products-grid">
                <?php if ($productos_result->num_rows > 0): ?>
                    <?php while ($producto = $productos_result->fetch_assoc()): ?>
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
                                    <p class="product-seller">Vendedor: <?php echo htmlspecialchars($producto['vendedor_nombre']); ?></p>
                                    <p class="product-category"><?php echo htmlspecialchars($producto['categoria_nombre']); ?> - 
                                       <?php echo htmlspecialchars($producto['subcategoria_nombre']); ?></p>
                                    <span class="product-condition 
                                    <?php echo (strtolower($producto['integridad_nombre']) == 'nuevo') ? 'condition-nuevo' : ''; ?>
                                    <?php echo (strtolower($producto['integridad_nombre']) == 'usado') ? 'condition-usado' : ''; ?>">
                                    <?php echo htmlspecialchars($producto['integridad_nombre']); ?>
                                    </span>
                                    <span class="product-stock">Disponibles: <?php echo $producto['disponibles']; ?></span>
                                </div>
                            </a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-products">
                        <p>No se encontraron productos. ¡Sé el primero en publicar!</p>
                        <?php if ($user): ?>
                            <a href="publicar.php" class="btn-primary">Publicar Producto</a>
                        <?php endif; ?>
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
<?php
$conn->close();
?>
