-- Track which CRM user created each website-brief link (client/package links come in the Clients phase).

ALTER TABLE client_briefs
  ADD COLUMN created_by INT UNSIGNED NULL AFTER client_label;
