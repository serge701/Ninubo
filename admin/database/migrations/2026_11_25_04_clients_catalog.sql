-- Phase 2: clients, client activity timeline, packages & services catalog, briefs linked to client + package.

CREATE TABLE IF NOT EXISTS clients (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_name VARCHAR(190) NOT NULL DEFAULT '',
  contact_name VARCHAR(120) NOT NULL,
  contact_title VARCHAR(120) NOT NULL DEFAULT '',
  email VARCHAR(190) NOT NULL DEFAULT '',
  phone VARCHAR(40) NOT NULL DEFAULT '',
  alt_phone VARCHAR(40) NOT NULL DEFAULT '',
  preferred_contact ENUM('phone','text','whatsapp','email') NOT NULL DEFAULT 'phone',
  website VARCHAR(190) NOT NULL DEFAULT '',
  industry VARCHAR(80) NOT NULL DEFAULT '',
  address1 VARCHAR(190) NOT NULL DEFAULT '',
  address2 VARCHAR(190) NOT NULL DEFAULT '',
  city VARCHAR(80) NOT NULL DEFAULT '',
  state VARCHAR(40) NOT NULL DEFAULT 'TX',
  zip VARCHAR(20) NOT NULL DEFAULT '',
  preferred_language ENUM('en','es') NOT NULL DEFAULT 'en',
  status ENUM('lead','active','inactive') NOT NULL DEFAULT 'lead',
  source VARCHAR(60) NOT NULL DEFAULT '',
  tax_exempt TINYINT(1) NOT NULL DEFAULT 0,
  notes TEXT NULL,
  assigned_to INT UNSIGNED NULL,
  created_by INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY ix_clients_status (status),
  KEY ix_clients_assigned (assigned_to),
  CONSTRAINT fk_clients_assigned FOREIGN KEY (assigned_to) REFERENCES users (id) ON DELETE SET NULL,
  CONSTRAINT fk_clients_created_by FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS client_activities (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  client_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NULL,
  type ENUM('note','call','email','meeting','whatsapp','task','system') NOT NULL DEFAULT 'note',
  body TEXT NOT NULL,
  due_at DATETIME NULL,
  done_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY ix_activities_client (client_id, created_at),
  KEY ix_activities_tasks (type, done_at, due_at),
  CONSTRAINT fk_activities_client FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE,
  CONSTRAINT fk_activities_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- tax_type: data_processing = taxed on the "taxable portion" setting (80% in Texas),
--           taxable = taxed on 100%, exempt = no sales tax (e.g. training).
-- brief_profile: which website-brief questions the client gets (see includes/brief-schema.php); NULL = no brief.
CREATE TABLE IF NOT EXISTS packages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  category ENUM('website','hosting','maintenance','software','data','marketing','training','other') NOT NULL DEFAULT 'website',
  description TEXT NULL,
  price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  billing ENUM('one_time','monthly','yearly') NOT NULL DEFAULT 'one_time',
  tax_type ENUM('data_processing','taxable','exempt') NOT NULL DEFAULT 'data_processing',
  brief_profile VARCHAR(30) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Starter catalog (prices at 0.00 until the owner sets them in Catalog).
INSERT INTO packages (name, category, description, price, billing, tax_type, brief_profile, sort_order) VALUES
('Landing Page', 'website', 'One-page website with your main message, services, call to action and contact form. Mobile friendly, basic on-page SEO.', 0.00, 'one_time', 'data_processing', 'landing', 10),
('Business Website', 'website', 'Multi-page website (Home, About, Services, Gallery, Contact and more) with Google Maps, reviews and on-page SEO for your service area.', 0.00, 'one_time', 'data_processing', 'business', 20),
('Online Store', 'website', 'E-commerce website with product catalog, cart, online payments and order notifications.', 0.00, 'one_time', 'data_processing', 'ecommerce', 30),
('Website Hosting', 'hosting', 'Hosting, SSL certificate, daily backups and uptime monitoring.', 0.00, 'monthly', 'data_processing', NULL, 40),
('Website Maintenance', 'maintenance', 'Content updates, plugin/security updates and technical support.', 0.00, 'monthly', 'data_processing', NULL, 50);

ALTER TABLE client_briefs
  ADD COLUMN client_id INT UNSIGNED NULL AFTER id,
  ADD COLUMN package_id INT UNSIGNED NULL AFTER client_id,
  ADD COLUMN brief_profile VARCHAR(30) NULL AFTER package_id,
  ADD KEY ix_briefs_client (client_id),
  ADD CONSTRAINT fk_briefs_client FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE SET NULL,
  ADD CONSTRAINT fk_briefs_package FOREIGN KEY (package_id) REFERENCES packages (id) ON DELETE SET NULL;
