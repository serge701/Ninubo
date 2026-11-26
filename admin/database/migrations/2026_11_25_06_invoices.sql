-- Phase 3: invoices, line items, payments and recurring services (monthly care plans, yearly domain & hosting renewal).

CREATE TABLE IF NOT EXISTS invoices (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  number VARCHAR(40) NULL COMMENT 'Assigned when the invoice is issued; drafts have none',
  client_id INT UNSIGNED NOT NULL,
  status ENUM('draft','sent','partial','paid','void') NOT NULL DEFAULT 'draft',
  issue_date DATE NOT NULL,
  due_date DATE NOT NULL,
  payment_terms ENUM('full','50_50','40_30_30') NOT NULL DEFAULT '50_50',
  subtotal DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  taxable_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  tax_rate DECIMAL(6,3) NOT NULL DEFAULT 0.000 COMMENT 'Snapshot of the sales tax rate (%)',
  taxable_pct DECIMAL(5,2) NOT NULL DEFAULT 80.00 COMMENT 'Snapshot of the data-processing taxable portion (%)',
  tax_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  amount_paid DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  tax_exempt TINYINT(1) NOT NULL DEFAULT 0,
  bill_to TEXT NULL COMMENT 'Client name/address snapshot taken when issued',
  notes TEXT NULL COMMENT 'Shown to the client on the invoice',
  internal_notes TEXT NULL,
  created_by INT UNSIGNED NULL,
  issued_at DATETIME NULL,
  voided_at DATETIME NULL,
  void_reason VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_invoices_number (number),
  KEY ix_invoices_client (client_id),
  KEY ix_invoices_status (status, due_date),
  CONSTRAINT fk_invoices_client FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE RESTRICT,
  CONSTRAINT fk_invoices_created_by FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS client_subscriptions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  client_id INT UNSIGNED NOT NULL,
  package_id INT UNSIGNED NULL,
  description VARCHAR(190) NOT NULL,
  details TEXT NULL,
  unit_price DECIMAL(10,2) NOT NULL,
  billing ENUM('monthly','yearly') NOT NULL,
  tax_type ENUM('data_processing','taxable','exempt') NOT NULL DEFAULT 'data_processing',
  start_date DATE NOT NULL,
  next_invoice_date DATE NOT NULL,
  status ENUM('active','cancelled') NOT NULL DEFAULT 'active',
  source_invoice_id INT UNSIGNED NULL,
  cancelled_at DATETIME NULL,
  created_by INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY ix_subs_client (client_id),
  KEY ix_subs_due (status, next_invoice_date),
  CONSTRAINT fk_subs_client FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE,
  CONSTRAINT fk_subs_package FOREIGN KEY (package_id) REFERENCES packages (id) ON DELETE SET NULL,
  CONSTRAINT fk_subs_invoice FOREIGN KEY (source_invoice_id) REFERENCES invoices (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS invoice_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  invoice_id INT UNSIGNED NOT NULL,
  package_id INT UNSIGNED NULL,
  subscription_id INT UNSIGNED NULL COMMENT 'Set when the line bills a period of a recurring service',
  description VARCHAR(190) NOT NULL,
  details TEXT NULL,
  quantity DECIMAL(10,2) NOT NULL DEFAULT 1.00,
  unit_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  billing ENUM('one_time','monthly','yearly') NOT NULL DEFAULT 'one_time',
  tax_type ENUM('data_processing','taxable','exempt') NOT NULL DEFAULT 'data_processing',
  line_total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  sort_order INT NOT NULL DEFAULT 0,
  KEY ix_items_invoice (invoice_id),
  CONSTRAINT fk_items_invoice FOREIGN KEY (invoice_id) REFERENCES invoices (id) ON DELETE CASCADE,
  CONSTRAINT fk_items_package FOREIGN KEY (package_id) REFERENCES packages (id) ON DELETE SET NULL,
  CONSTRAINT fk_items_subscription FOREIGN KEY (subscription_id) REFERENCES client_subscriptions (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS invoice_payments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  invoice_id INT UNSIGNED NOT NULL,
  paid_on DATE NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  method ENUM('zelle','check','cash','card','bank_transfer','other') NOT NULL DEFAULT 'zelle',
  reference VARCHAR(120) NOT NULL DEFAULT '',
  notes VARCHAR(255) NOT NULL DEFAULT '',
  recorded_by INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY ix_payments_invoice (invoice_id),
  KEY ix_payments_date (paid_on),
  CONSTRAINT fk_payments_invoice FOREIGN KEY (invoice_id) REFERENCES invoices (id) ON DELETE CASCADE,
  CONSTRAINT fk_payments_user FOREIGN KEY (recorded_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Owner's answers (Nov 2026): blog & booking are Premium-only; Business delivery is 10–15 business days;
-- domain and hosting are included for the first year, then renewed yearly.
UPDATE packages SET description = REPLACE(description, 'domain and hosting setup', 'domain and hosting for the first year')
WHERE id IN (1, 2, 6) AND description LIKE '%domain and hosting setup%';

UPDATE packages SET description = REPLACE(description, 'Estimated delivery: 5–7 business days', 'Estimated delivery: 10–15 business days')
WHERE id = 2 AND name = 'Business Website';

INSERT INTO packages (name, category, description, price, price_type, billing, tax_type, brief_profile, sort_order) VALUES
('Domain & Hosting Renewal', 'hosting', 'Yearly renewal of the website domain and hosting (both are included for the first year with every website package).', 0.00, 'fixed', 'yearly', 'data_processing', NULL, 90);

-- Which catalog item is scheduled automatically as a yearly renewal when a website package is invoiced.
INSERT INTO settings (setting_key, setting_value) VALUES ('website_renewal_package_id', LAST_INSERT_ID())
ON DUPLICATE KEY UPDATE setting_value = setting_value;
