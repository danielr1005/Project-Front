<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}
$conn = getDBConnection();
$user = getCurrentUser();

// Manejo de agregar/quitar favorito
if (isset($_GET['producto_id'])) {
    $producto_id = (int)$_GET['producto_id'];

    // Verificar si ya es favorito
    $stmt = $conn->prepare("SELECT * FROM favoritos WHERE votante_id = ? AND votado_id = ?");
    $stmt->bind_param("ii", $user['id'], $producto_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Quitar favorito
        $stmt_del = $conn->prepare("DELETE FROM favoritos WHERE votante_id = ? AND votado_id = ?");
        $stmt_del->bind_param("ii", $user['id'], $producto_id);
        $stmt_del->execute();
        $stmt_del->close();
    } else {
        // Agregar favorito
        $stmt_ins = $conn->prepare("INSERT INTO favoritos (votante_id, votado_id) VALUES (?, ?)");
        $stmt_ins->bind_param("ii", $user['id'], $producto_id);
        $stmt_ins->execute();
        $stmt_ins->close();
    }

    $stmt->close();

    // 🔹 REDIRECCION CORRECTA: volvemos a la misma página para recargar favoritos
    header('Location: favoritos.php');
    exit;
}

// Ahora sí, cargamos los favoritos actualizados
$stmt = $conn->prepare("
    SELECT p.*, sc.nombre as subcategoria_nombre, c.nombre as categoria_nombre, e.nombre as estado_nombre
    FROM favoritos f
    INNER JOIN productos p ON f.votado_id = p.id
    INNER JOIN subcategorias sc ON p.subcategoria_id = sc.id
    INNER JOIN categorias c ON sc.categoria_id = c.id
    INNER JOIN estados e ON p.estado_id = e.id
    WHERE f.votante_id = ?
");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$productos_favoritos_result = $stmt->get_result();
$stmt->close();
$conn->close();
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Favoritos - Tu Mercado SENA</title>
    <link rel="stylesheet" href="styles.css">

    <style>
        /* Estilos CSS específicos para esta página */
        .btn-remove-fav {
            background: #e74c3c;
            color: #fff;
            padding: 6px 10px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            transition: .2s;
            border: none; /* Para que parezca botón */
            cursor: pointer;
        }
        .btn-remove-fav:hover {
            background: #c0392b;
        }
        .product-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
            justify-content: center; /* Centrar el botón de acción */
        }
        .no-favorites {
            grid-column: 1 / -1; /* Ocupa todo el ancho en la cuadrícula */
            text-align: center;
            padding: 40px;
            border: 1px dashed var(--color-border, #ccc);
            border-radius: 8px;
            margin-top: 20px;
        }
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            padding-top: 20px;
        }
        .product-card {
            border: 1px solid var(--color-border, #ccc);
            border-radius: 8px;
            overflow: hidden;
            background-color: var(--color-card-bg, #fff);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding-bottom: 15px;
        }
        .product-card .product-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }
        /* Añade más estilos de .product-card y .product-info si son necesarios */
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <h1 class="logo">
                <a href="index.php">
                    <img src="logo_new.png" class="logo-img">
                    Tu Mercado SENA
                </a>
                </h1>
                <nav class="nav">
                    <a href="index.php" class="<?= $current_page == 'index.php' ? 'active' : '' ?>">Menu Principal</a>
                    <a href="mis_productos.php">Mis Productos</a>
                    <a href="publicar.php">Publicar Producto</a>
                    <a href="favoritos.php" class="<?= $current_page == 'favoritos.php' ? 'active' : '' ?>">Favoritos</a> 
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
                <h1>Mis Favoritos ❤️</h1>
            </div>
           <div class="favoritos-grid">
    <div class="products-grid">
        <?php if ($productos_favoritos_result->num_rows > 0): ?>
            <?php while ($producto = $productos_favoritos_result->fetch_assoc()): ?>
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
                        <a href="eliminar_favorito.php?id_producto=<?php echo $producto['id']; ?>"
                           class="btn-remove-fav"
                           onclick="return confirm('¿Estás seguro de que deseas eliminar este producto de tus favoritos?');">
                            Quitar de Favoritos
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-favorites">
                <p>No tienes ningún producto marcado como favorito.</p>
                <p>¡Explora el Menú Principal para encontrar productos que te gusten!</p>
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