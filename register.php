<?php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $nombre = sanitize($_POST['nombre'] ?? '');
    
    if (empty($email) || empty($password) || empty($nombre)) {
        $error = 'Por favor completa todos los campos';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El formato del correo electrónico no es válido';
    } elseif (strtolower(substr($email, strrpos($email, '@'))) !== '@sena.edu.co') {
        $error = 'El correo electrónico debe ser del dominio @sena.edu.co';
    } elseif ($password !== $password_confirm) {
        $error = 'Las contraseñas no coinciden';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres';
    } else {
        $conn = getDBConnection();
        
        // Verificar si el correo ya existe
        $stmt = $conn->prepare("SELECT id FROM correos WHERE correo = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Este correo ya está registrado';
            $stmt->close();
        } else {
            $stmt->close();
            
            // Insertar correo
            $stmt = $conn->prepare("INSERT INTO correos (correo, clave) VALUES (?, '')");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $correo_id = $conn->insert_id;
            $stmt->close();
            
            // Hash de contraseña
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Insertar usuario (rol_id = 3 es usuario normal)
            $rol_id = 3;
            $estado_id = 1; // activo
            $avatar = 0;
            $descripcion = '';
            $link = '';
            
            $stmt = $conn->prepare("INSERT INTO usuarios (correo_id, password, rol_id, nombre, avatar, descripcion, link, estado_id) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isissssi", $correo_id, $password_hash, $rol_id, $nombre, $avatar, $descripcion, $link, $estado_id);
            
            if ($stmt->execute()) {
                $success = 'Registro exitoso. Ahora puedes iniciar sesión.';
            } else {
                $error = 'Error al registrar usuario: ' . $conn->error;
            }
            
            $stmt->close();
        }
        
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Tu Mercado SENA</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h1>Registro</h1>
            <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>
            <form method="POST" action="register.php">
                <div class="form-group">
                    <label for="nombre">Nombre de Usuario</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="email">Correo Electrónico (@sena.edu.co) *</label>
                    <input type="email" id="email" name="email" placeholder="usuario@sena.edu.co" required>
                    <small>Solo se aceptan correos del dominio @sena.edu.co</small>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required minlength="6">
                </div>
                <div class="form-group">
                    <label for="password_confirm">Confirmar Contraseña</label>
                    <input type="password" id="password_confirm" name="password_confirm" required minlength="6">
                </div>
                <button type="submit" class="btn-primary">Registrarse</button>
            </form>
            <p class="auth-link">¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a></p>
            <p class="auth-link"><small>Recuerda: Solo se aceptan correos @sena.edu.co</small></p>
        </div>
    </div>
    <script src="script.js"></script>
    <script>
        // Validación del dominio @sena.edu.co en tiempo real
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.getElementById('email');
            if (emailInput) {
                emailInput.addEventListener('blur', function() {
                    const email = this.value.trim().toLowerCase();
                    if (email && !email.endsWith('@sena.edu.co')) {
                        this.setCustomValidity('El correo debe ser del dominio @sena.edu.co');
                        this.style.borderColor = '#e74c3c';
                    } else {
                        this.setCustomValidity('');
                        this.style.borderColor = '#ddd';
                    }
                });
                
                emailInput.addEventListener('input', function() {
                    if (this.style.borderColor === 'rgb(231, 76, 60)') {
                        const email = this.value.trim().toLowerCase();
                        if (email.endsWith('@sena.edu.co')) {
                            this.setCustomValidity('');
                            this.style.borderColor = '#ddd';
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>

