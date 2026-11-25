-- Esquema para el formulario de brief inicial de clientes (Ninubo)
-- Ejecutar una sola vez en la base de datos MySQL de XAMPP (phpMyAdmin o CLI).

CREATE DATABASE IF NOT EXISTS ninubo
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE ninubo;

CREATE TABLE IF NOT EXISTS client_briefs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  token VARCHAR(48) NOT NULL,
  client_label VARCHAR(190) NOT NULL,
  status ENUM('pendiente','en_progreso','completado') NOT NULL DEFAULT 'pendiente',
  lang VARCHAR(2) NOT NULL DEFAULT 'es' COMMENT 'Idioma en que el cliente está llenando el formulario (es/en)',
  data LONGTEXT NULL COMMENT 'Respuestas del formulario, codificadas como JSON',
  current_step INT NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  completed_at DATETIME NULL,
  UNIQUE KEY uniq_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
