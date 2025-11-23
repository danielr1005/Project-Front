<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();
$error = '';
$success = '';
$active_section = isset($_GET['section']) ? $_GET['section'] : 'perfil';

$conn = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section = $_POST['section'] ?? 'perfil';
    
    if ($section === 'perfil') {
        $nombre = sanitize($_POST['nombre'] ?? '');
        $descripcion = sanitize($_POST['descripcion'] ?? '');
        $link = sanitize($_POST['link'] ?? '');
        
        if (!empty($nombre)) {
            $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, descripcion = ?, link = ? WHERE id = ?");
            $stmt->bind_param("sssi", $nombre, $descripcion, $link, $user['id']);
            
            if ($stmt->execute()) {
                $success = 'Perfil actualizado correctamente';
                $_SESSION['usuario_nombre'] = $nombre;
                $user = getCurrentUser();
            } else {
                $error = 'Error al actualizar el perfil';
            }
            
            $stmt->close();
        } else {
            $error = 'El nombre es obligatorio';
        }
    } elseif ($section === 'configuracion') {
        $notifica_correo = isset($_POST['notifica_correo']) ? 1 : 0;
        $notifica_push = isset($_POST['notifica_push']) ? 1 : 0;
        $uso_datos = isset($_POST['uso_datos']) ? 1 : 0;
        
        $stmt = $conn->prepare("UPDATE usuarios SET notifica_correo = ?, notifica_push = ?, uso_datos = ? WHERE id = ?");
        $stmt->bind_param("iiii", $notifica_correo, $notifica_push, $uso_datos, $user['id']);
        
        if ($stmt->execute()) {
            $success = 'Configuración actualizada correctamente';
            $user = getCurrentUser();
        } else {
            $error = 'Error al actualizar la configuración';
        }
        
        $stmt->close();
    } elseif ($section === 'seguridad') {
        $password_actual = $_POST['password_actual'] ?? '';
        $password_nueva = $_POST['password_nueva'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';
        
        if (!empty($password_actual) && !empty($password_nueva)) {
            // Verificar contraseña actual
            if (password_verify($password_actual, $user['password'])) {
                if ($password_nueva === $password_confirm) {
                    if (strlen($password_nueva) >= 6) {
                        $password_hash = password_hash($password_nueva, PASSWORD_DEFAULT);
                        $stmt = $conn->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
                        $stmt->bind_param("si", $password_hash, $user['id']);
                        
                        if ($stmt->execute()) {
                            $success = 'Contraseña actualizada correctamente';
                        } else {
                            $error = 'Error al actualizar la contraseña';
                        }
                        
                        $stmt->close();
                    } else {
                        $error = 'La nueva contraseña debe tener al menos 6 caracteres';
                    }
                } else {
                    $error = 'Las contraseñas no coinciden';
                }
            } else {
                $error = 'La contraseña actual es incorrecta';
            }
        } else {
            $error = 'Por favor completa todos los campos';
        }
    }
    
    $active_section = $section;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Tu Mercado SENA</title>
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
                
                </nav>
            </div>
        </div>
    </header>

    <main class="main">
        <div class="container">
            <div class="settings-container">
                <div class="settings-sidebar">
                    <ul>
                        <li><a href="#" data-section="perfil" class="<?php echo $active_section === 'perfil' ? 'active' : ''; ?>">Información Personal</a></li>
                        <li><a href="#" data-section="configuracion" class="<?php echo $active_section === 'configuracion' ? 'active' : ''; ?>">Configuración</a></li>
                        <li><a href="#" data-section="seguridad" class="<?php echo $active_section === 'seguridad' ? 'active' : ''; ?>">Seguridad</a></li>
                        <li><a href="logout.php">Cerrar Sesión</a></li>
                    </ul>
                </div>
                
                <div class="settings-content">
                    <?php if ($error): ?>
                        <div class="error-message"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="success-message"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <!-- Sección: Información Personal -->
                    <div id="perfil" class="settings-section <?php echo $active_section === 'perfil' ? 'active' : ''; ?>">
                        <h2>Información Personal</h2>
                        <form method="POST" action="perfil.php" class="profile-form">
                            <input type="hidden" name="section" value="perfil">
                            
                            <div class="settings-group">
                                <h3>Datos Básicos</h3>
                                <div class="form-group">
                                    <label for="nombre">Nombre de Usuario *</label>
                                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($user['nombre']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Correo Electrónico</label>
                                    <input type="email" id="email" value="<?php echo htmlspecialchars($user['correo']); ?>" disabled>
                                    <small>El correo no se puede cambiar</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="descripcion">Descripción</label>
                                    <textarea id="descripcion" name="descripcion" rows="5" maxlength="512"><?php echo htmlspecialchars($user['descripcion']); ?></textarea>
                                    <small>Cuéntale a otros usuarios sobre ti</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="link">Enlace (Redes sociales, sitio web, etc.)</label>
                                    <input type="url" id="link" name="link" value="<?php echo htmlspecialchars($user['link']); ?>" maxlength="128" placeholder="https://...">
                                    <small>Comparte tus redes sociales o sitio web</small>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn-primary">Guardar Cambios</button>
                        </form>
                    </div>
                    
                    <!-- Sección: Configuración -->
                    <div id="configuracion" class="settings-section <?php echo $active_section === 'configuracion' ? 'active' : ''; ?>">
                        <h2>Configuración del Marketplace</h2>
                        <form method="POST" action="perfil.php" class="profile-form">
                            <input type="hidden" name="section" value="configuracion">
                            
                            <div class="settings-group">
                                <h3>Notificaciones</h3>
                                
                                <div class="toggle-switch">
                                    <label for="notifica_correo">Notificaciones por Correo</label>
                                    <label class="switch">
                                        <input type="checkbox" id="notifica_correo" name="notifica_correo" 
                                               <?php echo $user['notifica_correo'] ? 'checked' : ''; ?>>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <small>Recibir notificaciones importantes por correo electrónico</small>
                                
                                <div class="toggle-switch">
                                    <label for="notifica_push">Notificaciones Push</label>
                                    <label class="switch">
                                        <input type="checkbox" id="notifica_push" name="notifica_push" 
                                               <?php echo $user['notifica_push'] ? 'checked' : ''; ?>>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <small>Recibir notificaciones emergentes en tu dispositivo</small>
                            </div>
                            
                            <div class="settings-group">
                                <h3>Ahorro de Datos</h3>
                                
                                <div class="toggle-switch">
                                    <label for="uso_datos">Modo Ahorro de Datos</label>
                                    <label class="switch">
                                        <input type="checkbox" id="uso_datos" name="uso_datos" 
                                               <?php echo $user['uso_datos'] ? 'checked' : ''; ?>>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <small>Reduce el consumo de datos evitando cargar imágenes automáticamente</small>
                            </div>
                            
                            <button type="submit" class="btn-primary">Guardar Configuración</button>
                        </form>
                    </div>
                    
                    <!-- Sección: Seguridad -->
                    <div id="seguridad" class="settings-section <?php echo $active_section === 'seguridad' ? 'active' : ''; ?>">
                        <h2>Seguridad</h2>
                        <form method="POST" action="perfil.php" class="profile-form">
                            <input type="hidden" name="section" value="seguridad">
                            
                            <div class="settings-group">
                                <h3>Cambiar Contraseña</h3>
                                
                                <div class="form-group">
                                    <label for="password_actual">Contraseña Actual *</label>
                                    <input type="password" id="password_actual" name="password_actual" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="password_nueva">Nueva Contraseña *</label>
                                    <input type="password" id="password_nueva" name="password_nueva" required minlength="6">
                                    <small>Mínimo 6 caracteres</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="password_confirm">Confirmar Nueva Contraseña *</label>
                                    <input type="password" id="password_confirm" name="password_confirm" required minlength="6">
                                </div>
                            </div>
                            
                            <button type="submit" class="btn-primary">Cambiar Contraseña</button>
                        </form>
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