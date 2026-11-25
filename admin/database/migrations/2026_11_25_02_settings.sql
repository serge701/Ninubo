-- Business settings (key/value). Defaults live in app/Core/Settings.php; rows here override them.

CREATE TABLE IF NOT EXISTS settings (
  setting_key VARCHAR(80) NOT NULL PRIMARY KEY,
  setting_value TEXT NOT NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
