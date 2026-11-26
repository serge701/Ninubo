-- Phase 4: service agreements (contracts) with online e-signature + audit trail, or pen signature (uploaded scan).

-- Owner: Domain & Hosting Renewal is US$150 per year for now.
UPDATE packages SET price = 150.00 WHERE name = 'Domain & Hosting Renewal' AND price = 0.00;
UPDATE client_subscriptions s JOIN packages p ON p.id = s.package_id
   SET s.unit_price = 150.00
 WHERE p.name = 'Domain & Hosting Renewal' AND s.unit_price = 0.00 AND s.status = 'active';

CREATE TABLE IF NOT EXISTS contracts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  number VARCHAR(40) NULL COMMENT 'Assigned when the contract is finalized',
  client_id INT UNSIGNED NOT NULL,
  invoice_id INT UNSIGNED NULL,
  status ENUM('draft','sent','client_signed','signed','cancelled') NOT NULL DEFAULT 'draft',
  title VARCHAR(190) NOT NULL DEFAULT 'Website Design and Services Agreement',
  effective_date DATE NOT NULL,
  special_terms TEXT NULL,
  client_signer_name VARCHAR(150) NOT NULL DEFAULT '',
  client_signer_title VARCHAR(120) NOT NULL DEFAULT '',
  client_signer_email VARCHAR(190) NOT NULL DEFAULT '',
  body_html LONGTEXT NULL COMMENT 'Frozen agreement text, set when finalized',
  body_hash CHAR(64) NULL COMMENT 'SHA-256 of body_html',
  token CHAR(48) NULL COMMENT 'Secret for the public signing link',
  sent_at DATETIME NULL,
  client_consent_at DATETIME NULL,
  client_signature MEDIUMTEXT NULL COMMENT 'PNG data URI',
  client_signed_name VARCHAR(150) NULL,
  client_signed_title VARCHAR(120) NULL,
  client_signed_email VARCHAR(190) NULL,
  client_signed_at DATETIME NULL,
  client_ip VARCHAR(45) NULL,
  client_user_agent VARCHAR(255) NULL,
  company_signature MEDIUMTEXT NULL COMMENT 'PNG data URI',
  company_signer_name VARCHAR(150) NULL,
  company_signer_title VARCHAR(120) NULL,
  company_signed_at DATETIME NULL,
  company_signed_by INT UNSIGNED NULL,
  signed_file VARCHAR(190) NULL COMMENT 'Pen-signed copy uploaded (admin/storage/contracts)',
  signed_file_at DATETIME NULL,
  cancelled_at DATETIME NULL,
  cancel_reason VARCHAR(255) NULL,
  created_by INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_contracts_number (number),
  UNIQUE KEY uniq_contracts_token (token),
  KEY ix_contracts_client (client_id),
  CONSTRAINT fk_contracts_client FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE RESTRICT,
  CONSTRAINT fk_contracts_invoice FOREIGN KEY (invoice_id) REFERENCES invoices (id) ON DELETE SET NULL,
  CONSTRAINT fk_contracts_created_by FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL,
  CONSTRAINT fk_contracts_company_signed_by FOREIGN KEY (company_signed_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit trail (evidence for the electronic signature): who did what, when, from where.
CREATE TABLE IF NOT EXISTS contract_events (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  contract_id INT UNSIGNED NOT NULL,
  event VARCHAR(40) NOT NULL,
  details VARCHAR(500) NOT NULL DEFAULT '',
  ip VARCHAR(45) NULL,
  user_agent VARCHAR(255) NULL,
  user_id INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY ix_events_contract (contract_id, created_at),
  CONSTRAINT fk_events_contract FOREIGN KEY (contract_id) REFERENCES contracts (id) ON DELETE CASCADE,
  CONSTRAINT fk_events_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
