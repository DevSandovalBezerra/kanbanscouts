PRAGMA foreign_keys = ON;

CREATE TABLE companies (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  status TEXT NOT NULL,
  created_at TEXT NOT NULL,
  updated_at TEXT NOT NULL
);

CREATE TABLE users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  company_id INTEGER NOT NULL,
  name TEXT NOT NULL,
  email TEXT NOT NULL,
  password TEXT NOT NULL,
  status TEXT NOT NULL,
  created_at TEXT NOT NULL,
  updated_at TEXT NOT NULL,
  FOREIGN KEY (company_id) REFERENCES companies(id)
);

CREATE UNIQUE INDEX users_company_email_unique ON users(company_id, email);

CREATE TABLE roles (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL
);

CREATE UNIQUE INDEX roles_name_unique ON roles(name);

CREATE TABLE permissions (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL
);

CREATE UNIQUE INDEX permissions_name_unique ON permissions(name);

CREATE TABLE role_permissions (
  role_id INTEGER NOT NULL,
  permission_id INTEGER NOT NULL,
  PRIMARY KEY (role_id, permission_id),
  FOREIGN KEY (role_id) REFERENCES roles(id),
  FOREIGN KEY (permission_id) REFERENCES permissions(id)
);

CREATE TABLE user_roles (
  user_id INTEGER NOT NULL,
  role_id INTEGER NOT NULL,
  PRIMARY KEY (user_id, role_id),
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (role_id) REFERENCES roles(id)
);

CREATE TABLE projects (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  company_id INTEGER NOT NULL,
  name TEXT NOT NULL,
  description TEXT NOT NULL,
  created_by INTEGER NOT NULL,
  created_at TEXT NOT NULL,
  updated_at TEXT NOT NULL,
  FOREIGN KEY (company_id) REFERENCES companies(id),
  FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE project_members (
  project_id INTEGER NOT NULL,
  user_id INTEGER NOT NULL,
  role_in_project TEXT NOT NULL,
  created_at TEXT NOT NULL,
  PRIMARY KEY (project_id, user_id),
  FOREIGN KEY (project_id) REFERENCES projects(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE boards (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  project_id INTEGER NOT NULL,
  name TEXT NOT NULL,
  created_by INTEGER NOT NULL,
  created_at TEXT NOT NULL,
  updated_at TEXT NOT NULL,
  FOREIGN KEY (project_id) REFERENCES projects(id),
  FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE columns (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  board_id INTEGER NOT NULL,
  name TEXT NOT NULL,
  position INTEGER NOT NULL,
  created_at TEXT NOT NULL,
  updated_at TEXT NOT NULL,
  FOREIGN KEY (board_id) REFERENCES boards(id)
);

CREATE UNIQUE INDEX columns_board_position_unique ON columns(board_id, position);
CREATE UNIQUE INDEX columns_board_name_unique ON columns(board_id, name);

CREATE TABLE tasks (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  column_id INTEGER NOT NULL,
  title TEXT NOT NULL,
  description TEXT NOT NULL,
  assigned_to INTEGER,
  priority TEXT NOT NULL,
  deadline TEXT,
  status TEXT NOT NULL,
  position INTEGER NOT NULL,
  created_by INTEGER NOT NULL,
  created_at TEXT NOT NULL,
  updated_at TEXT NOT NULL,
  FOREIGN KEY (column_id) REFERENCES columns(id),
  FOREIGN KEY (assigned_to) REFERENCES users(id),
  FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE INDEX tasks_column_position_idx ON tasks(column_id, position);

CREATE TABLE task_comments (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  task_id INTEGER NOT NULL,
  user_id INTEGER NOT NULL,
  body TEXT NOT NULL,
  created_at TEXT NOT NULL,
  FOREIGN KEY (task_id) REFERENCES tasks(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE task_history (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  task_id INTEGER NOT NULL,
  action TEXT NOT NULL,
  old_value TEXT NOT NULL,
  new_value TEXT NOT NULL,
  user_id INTEGER NOT NULL,
  created_at TEXT NOT NULL,
  FOREIGN KEY (task_id) REFERENCES tasks(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);
