CREATE DATABASE finanzas60s;
USE finanzas60s;
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    telefono VARCHAR(20) UNIQUE,
    nombre VARCHAR(100),
    email VARCHAR(150),
    paso_tip INT DEFAULT 1,
    estado VARCHAR(50) DEFAULT 'inicio',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE etiquetas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) UNIQUE
);
CREATE TABLE usuario_etiquetas (
    usuario_id INT,
    etiqueta_id INT,
    PRIMARY KEY (usuario_id, etiqueta_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (etiqueta_id) REFERENCES etiquetas(id) ON DELETE CASCADE
);
CREATE TABLE planes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50),
    duracion_dias INT,
    precio DECIMAL(10,2)
);
CREATE TABLE suscripciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    plan_id INT,
    fecha_inicio DATE,
    fecha_fin DATE,
    estado ENUM('activa','vencida','cancelada') DEFAULT 'activa',
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (plan_id) REFERENCES planes(id)
);
