-- Briefs created automatically when an invoice with a website package is issued remember that invoice,
-- so voiding it can remove a brief the client never opened.

ALTER TABLE client_briefs
  ADD COLUMN invoice_id INT UNSIGNED NULL AFTER package_id,
  ADD KEY ix_briefs_invoice (invoice_id),
  ADD CONSTRAINT fk_briefs_invoice FOREIGN KEY (invoice_id) REFERENCES invoices (id) ON DELETE SET NULL;
