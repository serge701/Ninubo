-- CRM users (username + password sign-in) and login throttling.
-- Roles: admin (everything + users/catalog/settings) and sales (clients, invoices, contracts, briefs).

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(60) NOT NULL,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL DEFAULT '',
  phone VARCHAR(40) NOT NULL DEFAULT '',
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','sales') NOT NULL DEFAULT 'sales',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  must_change_password TINYINT(1) NOT NULL DEFAULT 0,
  last_login_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_users_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS login_attempts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL,
  ip VARCHAR(45) NOT NULL,
  created_at DATETIME NOT NULL,
  KEY ix_attempts_user_ip (username, ip, created_at),
  KEY ix_attempts_ip (ip, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
