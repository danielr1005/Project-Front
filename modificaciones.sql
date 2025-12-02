- --------------------------------------------
-- Archivo: modificaciones.sql
-- Descripción: Contiene los cambios a la base de datos
-- --------------------------------------------

-- 1️⃣ Tabla productos: agregar columnas para imagen y tipo de imagen
ALTER TABLE productos 
ADD COLUMN imagen LONGBLOB NULL;

ALTER TABLE productos 
ADD COLUMN imagen_tipo VARCHAR(50) NULL;

-- 2️⃣ Tabla mensajes: modificar fecha de registro para usar timestamp automático
ALTER TABLE mensajes
MODIFY COLUMN fecha_registro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- 3️⃣ Tabla usuarios: agregar columna para tipo de avatar
ALTER TABLE usuarios 
ADD COLUMN avatar_tipo VARCHAR(50) NULL;

-- 4️⃣ Tabla favoritos: cambiar la referencia de votado_id a productos(id)
-- Primero eliminar la llave foránea antigua
ALTER TABLE favoritos
DROP FOREIGN KEY usuario_votado;

-- Luego crear la nueva relación con productos
ALTER TABLE favoritos
ADD CONSTRAINT producto_votado
FOREIGN KEY (votado_id) REFERENCES productos(id) 
ON DELETE CASCADE 
ON UPDATE CASCADE;

-- 🔹 Fin de modificaciones