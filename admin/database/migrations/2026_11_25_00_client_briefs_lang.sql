-- client_briefs.lang (language the client fills the brief in). Added to sql/brief.sql in Sept 2026, but the
-- production table was created before that, so the column may be missing. MySQL 5.7 has no
-- "ADD COLUMN IF NOT EXISTS", so the ALTER runs only when information_schema says the column is absent.
-- Sorted first (…_00_…) so it runs before the other client_briefs changes.

SET @ninubo_has_lang = (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'client_briefs' AND COLUMN_NAME = 'lang');

SET @ninubo_sql = IF(@ninubo_has_lang = 0,
  'ALTER TABLE client_briefs ADD COLUMN lang VARCHAR(2) NOT NULL DEFAULT ''es'' COMMENT ''Idioma en que el cliente llena el formulario (es/en)'' AFTER status',
  'DO 0');

PREPARE ninubo_stmt FROM @ninubo_sql;
EXECUTE ninubo_stmt;
DEALLOCATE PREPARE ninubo_stmt;
