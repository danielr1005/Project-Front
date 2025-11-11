# Tu Mercado SENA - Marketplace

Marketplace funcional desarrollado con HTML, PHP, CSS y JavaScript para la plataforma Tu Mercado SENA.

## Características

- ✅ Sistema de autenticación (registro e inicio de sesión)
- ✅ Publicación y edición de productos
- ✅ Búsqueda y filtrado de productos por categoría
- ✅ Sistema de chat entre comprador y vendedor
- ✅ Perfil de usuario editable
- ✅ Gestión de productos (mis productos)
- ✅ Interfaz responsive y moderna
- ✅ Subida de imágenes para productos

## Requisitos

- PHP 7.4 o superior
- MySQL/MariaDB
- Servidor web (Apache con XAMPP recomendado)
- Extensión mysqli de PHP

## Instalación

1. **Importar la base de datos:**
   - Abre phpMyAdmin
   - Crea una base de datos llamada `tu_mercado_sena_v3`
   - Importa el archivo `tu_mercado_sena_v3 (1).sql`

2. **Configurar la conexión:**
   - Edita el archivo `config.php`
   - Ajusta las credenciales de la base de datos si es necesario:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'tu_mercado_sena_v3');
     ```

3. **Permisos de carpetas:**
   - Asegúrate de que la carpeta `uploads` tenga permisos de escritura (chmod 777 en Linux/Mac)
   - En Windows, asegúrate de que la carpeta tenga permisos de escritura

4. **Iniciar el servidor:**
   - Si usas XAMPP, inicia Apache y MySQL
   - Accede a la aplicación en `http://localhost/Nueva_carpeta/`

## Estructura del Proyecto

```
.
├── config.php              # Configuración de la base de datos
├── index.php               # Página principal con listado de productos
├── login.php               # Página de inicio de sesión
├── register.php            # Página de registro
├── logout.php              # Cerrar sesión
├── producto.php            # Detalle de producto
├── publicar.php            # Publicar nuevo producto
├── editar_producto.php     # Editar producto existente
├── mis_productos.php       # Listado de productos del usuario
├── contactar.php           # Iniciar conversación con vendedor
├── chat.php                # Sistema de chat
├── perfil.php              # Perfil de usuario
├── styles.css              # Estilos CSS
├── script.js               # JavaScript
├── uploads/                # Carpeta para imágenes de productos
└── README.md               # Este archivo
```

## Funcionalidades Principales

### Autenticación
- Registro de nuevos usuarios
- Inicio de sesión con correo y contraseña
- Cierre de sesión

### Productos
- Listado de productos con búsqueda y filtros
- Detalle de producto
- Publicación de productos con imagen
- Edición de productos propios
- Gestión de productos del usuario

### Chat
- Iniciar conversación con vendedor
- Enviar mensajes
- Ver historial de mensajes

### Perfil
- Editar información de perfil
- Ver y gestionar productos publicados

## Usuarios de Prueba

La base de datos incluye algunos usuarios de prueba. Para iniciar sesión, necesitarás conocer las contraseñas hasheadas. Puedes crear un nuevo usuario desde la página de registro.

## Notas

- Las imágenes de productos se guardan en la carpeta `uploads/` con el formato `img_[id_producto].jpg`
- Los productos tienen estados: activo, invisible, eliminado
- El sistema incluye categorías y subcategorías predefinidas
- Los chats se crean automáticamente cuando un comprador contacta a un vendedor

## Desarrollo Futuro

Funcionalidades que se pueden agregar:
- Sistema de calificaciones y comentarios
- Notificaciones en tiempo real
- Sistema de favoritos
- Búsqueda avanzada
- Panel de administración
- Sistema de denuncias
- Historial de transacciones

## Soporte

Para problemas o consultas, revisa la documentación de la base de datos en el archivo SQL.

## Licencia

Este proyecto es para uso educativo del SENA.

