CREATE TABLE project_secrets (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  project_id INTEGER NOT NULL,
  secret_key TEXT NOT NULL,
  title TEXT NULL,
  description TEXT NULL,
  secret_value_enc TEXT NOT NULL,
  created_by INTEGER NOT NULL,
  created_at TEXT NOT NULL,
  updated_at TEXT NOT NULL,
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE UNIQUE INDEX uq_project_secret_key ON project_secrets(project_id, secret_key);
CREATE INDEX project_secrets_project_id_idx ON project_secrets(project_id);
