-- --------------------------------------------
-- Archivo: modificaciones.sql
-- Descripción: Contiene los cambios a la base de datos
-- --------------------------------------------

-- 1️⃣ Tabla productos: agregar columna para ruta de imagen
ALTER TABLE productos 
ADD COLUMN imagen VARCHAR(255);

-- 2️⃣ Tabla mensajes: modificar fecha de registro para usar timestamp automático
ALTER TABLE mensajes
MODIFY COLUMN fecha_registro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- 3️⃣ Tabla usuarios: agregar columna para tipo de avatar
ALTER TABLE usuarios 
ADD COLUMN avatar_tipo VARCHAR(50) NULL;

-- 4️⃣ Tabla usuarios: convertir el campo avatar a LONGBLOB para almacenar imagen real
ALTER TABLE usuarios
MODIFY COLUMN avatar LONGBLOB NULL;


-- 🔹 Fin de modificaciones