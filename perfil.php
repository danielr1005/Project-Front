<?php
require_once 'config.php';

// Redirigir si no está logueado
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Obtener datos del usuario (refresca los datos de la sesión)
$user = getCurrentUser();
$error = '';
$success = '';

// Definir la sección activa, usando 'perfil' por defecto
$active_section = isset($_GET['section']) ? $_GET['section'] : 'perfil';

// Manejar mensajes de éxito después de redirecciones (ej. subida de avatar)
if (isset($_GET['status']) && $_GET['status'] === 'avatar_success') {
    $success = 'Avatar actualizado correctamente';
}

$conn = getDBConnection();

$section = $_GET['section'] ?? 'perfil';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section = $_POST['section'] ?? 'section';
    
    // ====================================================================
    // 1. LÓGICA DE ACTUALIZACIÓN DE PERFIL (Nombre, Descripción, Link)
    // ====================================================================
    if ($section === 'perfil') {
        $nombre = sanitize($_POST['nombre'] ?? '');
        $descripcion = sanitize($_POST['descripcion'] ?? '');
        $link = sanitize($_POST['link'] ?? '');
        
        if (!empty($nombre)) {
            $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, descripcion = ?, link = ? WHERE id = ?");
            $stmt->bind_param("sssi", $nombre, $descripcion, $link, $user['id']);
            
            if ($stmt->execute()) {
                $success = 'Perfil actualizado correctamente';
                // Actualizar sesión y datos del usuario
                $_SESSION['usuario_nombre'] = $nombre;
                $user = getCurrentUser();
            } else {
                $error = 'Error al actualizar el perfil';
            }
            
            $stmt->close();
        } else {
            $error = 'El nombre es obligatorio';
        }

    // ====================================================================
    // 2. LÓGICA DE SUBIDA DE AVATAR (NUEVO BLOQUE CLAVE)
    // ====================================================================
  } elseif ($section === 'avatar' && isset($_FILES['avatar_file'])) {
    $file = $_FILES['avatar_file'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        if (in_array($file['type'], $allowedTypes) && $file['size'] <= $maxSize) {
            $avatar_data = file_get_contents($file['tmp_name']);
            $avatar_tipo = $file['type'];

            // Preparar consulta
            $stmtA = $conn->prepare("UPDATE usuarios SET avatar = ?, avatar_tipo = ? WHERE id = ?");
            $null = NULL;
            $stmtA->bind_param("sbi", $null, $avatar_tipo, $user['id']);

            // Enviar datos binarios
            $stmtA->send_long_data(0, $avatar_data);

            if ($stmtA->execute()) {
                header('Location: perfil.php?section=perfil&status=avatar_success'); 
                exit;
            } else {
                $error = 'Error de base de datos al subir el avatar: ' . $conn->error;
            }
            $stmtA->close();
        } else {
            $error = 'El avatar debe ser JPEG, PNG o GIF y no exceder 2MB.';
        }
    } elseif ($file['error'] !== UPLOAD_ERR_NO_FILE) {
        $error = 'Error al subir el archivo: Código ' . $file['error'];
    }
}
    // ====================================================================
    // 4. LÓGICA DE SEGURIDAD
    // ====================================================================
    } elseif ($section === 'seguridad') {
        $password_actual = $_POST['password_actual'] ?? '';
        $password_nueva = $_POST['password_nueva'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';
        
        if (!empty($password_actual) && !empty($password_nueva)) {
            // ... (Lógica de cambio de contraseña existente) ...
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
    <?php
    $current_page = basename($_SERVER['PHP_SELF']);
    ?>
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
                    
                    <a href="mis_productos.php" class="<?= $current_page == 'mis_productos.php' ? 'active' : '' ?>">
                        Mis Productos
                    </a>
                    <a href="favoritos.php">Favoritos</a>
                    <a href="publicar.php" class="<?= $current_page == 'publicar.php' ? 'active' : '' ?>">
                        Publicar Producto
                    </a>
                    <div class="notification-badge">
                        <span class="notification-icon" id="notificationIcon" title="Chats y notificaciones">💬</span>
                        <span class="notification-count hidden" id="notificationCount">0</span>
                        <div class="chats-list" id="chatsList"></div>
                    </div>
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
                        
                        <a href="logout.php" onclick="return confirmarLogout();">Cerrar sesión</a>
                        <script>
                            function confirmarLogout() {
                                return confirm("¿Estás seguro de que deseas cerrar sesión?");
                            }
                        </script></li>  
                    </ul>
                </div>
                
                <div class="settings-content">
                    <?php if ($error): ?>
                        <div class="error-message"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="success-message"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <div id="perfil" class="settings-section <?php echo $active_section === 'perfil' ? 'active' : ''; ?>">
                        <h2>Información Personal</h2>
                        
                    <form method="POST" action="perfil.php" id="avatarUploadForm" enctype="multipart/form-data" style="display:none;">
    <input type="hidden" name="section" value="avatar">
    <input type="file" id="avatarInputHidden" name="avatar_file" accept="image/*">
    <button type="submit" id="avatarSubmitBtn">Subir Avatar</button>
</form>
<div class="profile-avatar-wrapper">
    <img id="avatarPhoto" src="<?php echo getUserAvatarUrl($user['id']); ?>" class="avatar-photo" alt="Avatar">

    <!-- Formulario de subida de avatar -->
    <form method="POST" action="perfil.php" id="avatarUploadForm" enctype="multipart/form-data">
        <input type="hidden" name="section" value="avatar">
        <input type="file" id="avatarInputHidden" name="avatar_file" accept="image/*" style="display:none;">
    </form>

    <!-- Botón lápiz -->
    <button id="avatarEditButton" class="avatar-edit-btn" title="Cambiar foto de perfil">
        <img src="assests/icons/icono-lapiz.png" alt="Editar">
    </button>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const avatarEditBtn = document.getElementById('avatarEditButton');
    const avatarInput = document.getElementById('avatarInputHidden');
    const avatarForm = document.getElementById('avatarUploadForm');
    const avatarPhoto = document.getElementById('avatarPhoto');

    // Abrir selector de archivos al hacer clic en el lápiz
    avatarEditBtn.addEventListener('click', () => {
        avatarInput.click();
    });

    // Previsualizar imagen y enviar formulario al seleccionar archivo
    avatarInput.addEventListener('change', () => {
        if (avatarInput.files && avatarInput.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                avatarPhoto.src = e.target.result; // Previsualización
            };
            reader.readAsDataURL(avatarInput.files[0]);

            avatarForm.submit(); // Enviar al backend
        }
    });
});
</script>
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
                    
                    <div id="configuracion" class="settings-section <?php echo $active_section === 'configuracion' ? 'active' : ''; ?>">
                        <h2>Configuración del Marketplace</h2>
                        <form method="POST" action="perfil.php" class="profile-form">
                            <input type="hidden" name="section" value="configuracion">
                            
                            <div class="settings-group">
                                <h3>Apariencia</h3>
                                <div class="toggle-switch">
                                    <label for="apariencia">Modo oscuro</label>
                                    <label class="switch">
                                        <button class="theme-toggle" id="themeToggle" title="Cambiar tema">🌓</button>
                                    </label>
                                </div>
                                <small>Acivar modo oscuro en toda la aplicacion</small>
                            </div>


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