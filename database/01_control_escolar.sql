-- Control Escolar | MariaDB (XAMPP) | puerto 3306 | BD control_escolar
-- Despliegue LOCAL: este script crea la base de datos y las tablas.
-- Importar desde phpMyAdmin (pestaña "Importar") o por consola:
--   mysql -u root -p < control_escolar_local.sql

SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS control_escolar
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE control_escolar;

-- (Opcional) Usuario dedicado en lugar de usar root.
-- Descomenta, cambia la contraseña y usa estos datos en la conexión PHP.
-- CREATE USER IF NOT EXISTS 'escolar_user'@'localhost' IDENTIFIED BY 'cambia_esta_clave';
-- GRANT ALL PRIVILEGES ON control_escolar.* TO 'escolar_user'@'localhost';
-- FLUSH PRIVILEGES;

CREATE TABLE IF NOT EXISTS usuarios(
 id INT AUTO_INCREMENT PRIMARY KEY, usuario VARCHAR(50) NOT NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL, nombre VARCHAR(100) NOT NULL,
 rol ENUM('admin','maestro') NOT NULL DEFAULT 'maestro', activo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS maestros(
 id INT AUTO_INCREMENT PRIMARY KEY, usuario_id INT NULL UNIQUE,
 nombre VARCHAR(80) NOT NULL, apellidos VARCHAR(80) NOT NULL, email VARCHAR(120) NULL, telefono VARCHAR(30) NULL,
 FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS grupos(
 id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(50) NOT NULL UNIQUE, grado VARCHAR(20) NULL, ciclo VARCHAR(20) NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS alumnos(
 id INT AUTO_INCREMENT PRIMARY KEY, matricula VARCHAR(30) NOT NULL UNIQUE,
 nombre VARCHAR(80) NOT NULL, apellidos VARCHAR(80) NOT NULL, fecha_nacimiento DATE NULL, email VARCHAR(120) NULL,
 grupo_id INT NULL, activo TINYINT(1) NOT NULL DEFAULT 1,
 FOREIGN KEY (grupo_id) REFERENCES grupos(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS materias(
 id INT AUTO_INCREMENT PRIMARY KEY, clave VARCHAR(20) NOT NULL UNIQUE, nombre VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS asignaciones(
 id INT AUTO_INCREMENT PRIMARY KEY, grupo_id INT NOT NULL, materia_id INT NOT NULL, maestro_id INT NOT NULL,
 UNIQUE(grupo_id,materia_id),
 FOREIGN KEY (grupo_id) REFERENCES grupos(id) ON DELETE CASCADE,
 FOREIGN KEY (materia_id) REFERENCES materias(id) ON DELETE CASCADE,
 FOREIGN KEY (maestro_id) REFERENCES maestros(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS calificaciones(
 id INT AUTO_INCREMENT PRIMARY KEY, alumno_id INT NOT NULL, asignacion_id INT NOT NULL,
 parcial TINYINT NOT NULL, calificacion DECIMAL(4,1) NOT NULL,
 UNIQUE(alumno_id,asignacion_id,parcial),
 CHECK (parcial BETWEEN 1 AND 3), CHECK (calificacion BETWEEN 0 AND 10),
 FOREIGN KEY (alumno_id) REFERENCES alumnos(id) ON DELETE CASCADE,
 FOREIGN KEY (asignacion_id) REFERENCES asignaciones(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT IGNORE INTO usuarios(usuario,password_hash,nombre,rol) VALUES ('admin','$2y$10$S2yKANVBqeyV5F6G6tMPseI6SJqTKiA3pK4S9VXiqsouI3yCCwSh.','Administrador','admin');
