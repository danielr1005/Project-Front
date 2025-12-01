<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();
$producto_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($producto_id <= 0) {
    header('Location: mis_productos.php');
    exit;
}

$conn = getDBConnection();

// Verificar que el producto pertenece al usuario
$stmt = $conn->prepare("SELECT * FROM productos WHERE id = ? AND vendedor_id = ?");
$stmt->bind_param("ii", $producto_id, $user['id']);
$stmt->execute();
$result = $stmt->get_result();
$producto = $result->fetch_assoc();
$stmt->close();

if (!$producto) {
    header('Location: mis_productos.php');
    exit;
}

$error = '';
$success = '';

// Obtener categorías y subcategorías
$categorias_query = "SELECT * FROM categorias ORDER BY nombre";
$categorias_result = $conn->query($categorias_query);

$subcategorias_query = "SELECT sc.*, c.nombre as categoria_nombre FROM subcategorias sc 
                       INNER JOIN categorias c ON sc.categoria_id = c.id ORDER BY c.nombre, sc.nombre";
$subcategorias_result = $conn->query($subcategorias_query);
$subcategorias = [];
while ($row = $subcategorias_result->fetch_assoc()) {
    $subcategorias[] = $row;
}

$integridad_query = "SELECT * FROM integridad ORDER BY id";
$integridad_result = $conn->query($integridad_query);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = sanitize($_POST['nombre'] ?? '');
    $descripcion = sanitize($_POST['descripcion'] ?? '');
    $precio = floatval($_POST['precio'] ?? 0);
    $disponibles = intval($_POST['disponibles'] ?? 1);
    $subcategoria_id = intval($_POST['subcategoria_id'] ?? 0);
    $integridad_id = intval($_POST['integridad_id'] ?? 1);
    $estado_id = intval($_POST['estado_id'] ?? 1);
    
    if (empty($nombre) || empty($descripcion) || $precio <= 0 || $subcategoria_id <= 0) {
        $error = 'Por favor completa todos los campos correctamente';
    } else {
        $stmt = $conn->prepare("UPDATE productos SET nombre = ?, subcategoria_id = ?, integridad_id = ?, 
                               estado_id = ?, descripcion = ?, precio = ?, disponibles = ? 
                               WHERE id = ? AND vendedor_id = ?");
        $stmt->bind_param("siiisidii", 
    $nombre,          // s = string
    $subcategoria_id, // i = int
    $integridad_id,   // i = int
    $estado_id,       // i = int
    $descripcion,     // s = string
    $precio,          // d = double
    $disponibles,     // i = int
    $producto_id,     // i = int
    $user['id']       // i = int
);
        
        if ($stmt->execute()) {
            // Manejar nueva imagen si se subió
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {

    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif'];
    $mime = mime_content_type($_FILES['imagen']['tmp_name']);

    if (in_array($mime, $allowed)) {

        $imgData = file_get_contents($_FILES['imagen']['tmp_name']);

        $stmt_img = $conn->prepare("UPDATE productos SET imagen = ?, imagen_tipo = ? WHERE id = ?");
        $stmt_img->bind_param("ssi", $imgData, $mime, $producto_id);
        $stmt_img->send_long_data(0, $imgData);
        $stmt_img->execute();
        $stmt_img->close();
    }
}
  
            
            $success = 'Producto actualizado exitosamente';
            $producto = array_merge($producto, [
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'precio' => $precio,
                'disponibles' => $disponibles,
                'subcategoria_id' => $subcategoria_id,
                'integridad_id' => $integridad_id,
                'estado_id' => $estado_id
            ]);
        } else {
            $error = 'Error al actualizar producto: ' . $conn->error;
        }
        
        $stmt->close();
    }
}

// Obtener estados
$estados_query = "SELECT * FROM estados WHERE id IN (1, 2, 3) ORDER BY id";
$estados_result = $conn->query($estados_query);

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto - Tu Mercado SENA</title>
    <link rel="stylesheet" href="styles.css">
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
</h1>                <nav class="nav">
                    <a href="mis_productos.php">Mis Productos</a>
                    <a href="favoritos.php">Favoritos</a>
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
            <div class="form-container">
                <h1>Editar Producto</h1>
                
                <?php if ($error): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="success-message"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="editar_producto.php?id=<?php echo $producto_id; ?>" enctype="multipart/form-data" class="product-form">
                    <div class="form-group">
                        <label for="nombre">Nombre del Producto *</label>
                        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($producto['nombre']); ?>" required maxlength="64">
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion">Descripción *</label>
                        <textarea id="descripcion" name="descripcion" rows="5" required maxlength="512"><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="precio">Precio (COP) *</label>
                            <input type="number" id="precio" name="precio" step="0.01" min="0" 
                                   value="<?php echo $producto['precio']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="disponibles">Cantidad Disponible *</label>
                            <input type="number" id="disponibles" name="disponibles" min="1" 
                                   value="<?php echo $producto['disponibles']; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="subcategoria_id">Categoría *</label>
                        <select id="subcategoria_id" name="subcategoria_id" required>
                            <option value="">Selecciona una categoría</option>
                            <?php
                            $current_categoria = '';
                            foreach ($subcategorias as $subcat):
                                if ($current_categoria != $subcat['categoria_nombre']):
                                    if ($current_categoria != '') echo '</optgroup>';
                                    echo '<optgroup label="' . htmlspecialchars($subcat['categoria_nombre']) . '">';
                                    $current_categoria = $subcat['categoria_nombre'];
                                endif;
                            ?>
                                <option value="<?php echo $subcat['id']; ?>" 
                                        <?php echo $producto['subcategoria_id'] == $subcat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($subcat['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                            <?php if ($current_categoria != '') echo '</optgroup>'; ?>
                        </select>
                    </div>
                    
                   <div class="form-group">
    <label for="integridad_id">Condición *</label>
    <select id="integridad_id" name="integridad_id" required>
        <?php 
        $integridad_result->data_seek(0);
        while ($int = $integridad_result->fetch_assoc()): ?>
            <option value="<?php echo $int['id']; ?>" 
                    <?php echo $producto['integridad_id'] == $int['id'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($int['nombre']); ?> - 
                <?php echo htmlspecialchars($int['descripcion']); ?>
            </option>
        <?php endwhile; ?>
    </select>
</div>

<!-- Estado (Debajo de Condición) -->
<div class="form-group">
    <label for="estado_id">Estado *</label>
    <select id="estado_id" name="estado_id" required>
        <?php while ($estado = $estados_result->fetch_assoc()): ?>
            <option value="<?php echo $estado['id']; ?>" 
                    <?php echo $producto['estado_id'] == $estado['id'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($estado['nombre']); ?>
            </option>
        <?php endwhile; ?>
    </select>
</div>

                    
                    <div class="form-group">
                        <label for="imagen">Nueva Imagen del Producto (opcional)</label>
                        <?php if (!empty($producto['imagen'])): ?>
                        <p>Imagen actual:</p>
                    <img src="data:<?php echo $producto['imagen_tipo']; ?>;base64,<?php echo base64_encode($producto['imagen']); ?>"
                    style="max-width: 200px; height: auto;">
                    <?php endif; ?>
                        <input type="file" id="imagen" name="imagen" accept="image/jpeg,image/jpg,image/png,image/gif">
                        <small>Formatos aceptados: JPG, PNG, GIF. Deja vacío para mantener la imagen actual.</small>
                    </div>
                    
                    <button type="submit" class="btn-primary">Guardar Cambios</button>
                    <a href="index.php" class="btn-secondary">Cancelar</a>
                </form>
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

