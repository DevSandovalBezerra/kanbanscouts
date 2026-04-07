-- Migration 011: Project Secrets (per-project key/value store)

CREATE TABLE project_secrets (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  project_id BIGINT UNSIGNED NOT NULL,
  secret_key VARCHAR(190) NOT NULL,
  title VARCHAR(190) NULL,
  description TEXT NULL,
  secret_value_enc TEXT NOT NULL,
  created_by BIGINT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_project_secret_key (project_id, secret_key),
  KEY project_secrets_project_id_idx (project_id),
  CONSTRAINT project_secrets_project_fk FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  CONSTRAINT project_secrets_created_by_fk FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
