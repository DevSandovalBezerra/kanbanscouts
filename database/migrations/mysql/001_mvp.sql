CREATE TABLE companies (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  status VARCHAR(32) NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  company_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  password VARCHAR(255) NOT NULL,
  status VARCHAR(32) NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY users_company_email_unique (company_id, email),
  KEY users_company_id_idx (company_id),
  CONSTRAINT users_company_id_fk FOREIGN KEY (company_id) REFERENCES companies(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE roles (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(128) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY roles_name_unique (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE permissions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY permissions_name_unique (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE role_permissions (
  role_id BIGINT UNSIGNED NOT NULL,
  permission_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (role_id, permission_id),
  KEY role_permissions_permission_id_idx (permission_id),
  CONSTRAINT role_permissions_role_id_fk FOREIGN KEY (role_id) REFERENCES roles(id),
  CONSTRAINT role_permissions_permission_id_fk FOREIGN KEY (permission_id) REFERENCES permissions(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_roles (
  user_id BIGINT UNSIGNED NOT NULL,
  role_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (user_id, role_id),
  KEY user_roles_role_id_idx (role_id),
  CONSTRAINT user_roles_user_id_fk FOREIGN KEY (user_id) REFERENCES users(id),
  CONSTRAINT user_roles_role_id_fk FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE projects (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  company_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  created_by BIGINT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY projects_company_id_idx (company_id),
  KEY projects_created_by_idx (created_by),
  CONSTRAINT projects_company_id_fk FOREIGN KEY (company_id) REFERENCES companies(id),
  CONSTRAINT projects_created_by_fk FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE project_members (
  project_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  role_in_project VARCHAR(32) NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (project_id, user_id),
  KEY project_members_user_id_idx (user_id),
  CONSTRAINT project_members_project_id_fk FOREIGN KEY (project_id) REFERENCES projects(id),
  CONSTRAINT project_members_user_id_fk FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE boards (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  project_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(255) NOT NULL,
  created_by BIGINT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY boards_project_id_idx (project_id),
  KEY boards_created_by_idx (created_by),
  CONSTRAINT boards_project_id_fk FOREIGN KEY (project_id) REFERENCES projects(id),
  CONSTRAINT boards_created_by_fk FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE columns (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  board_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(255) NOT NULL,
  position INT NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY columns_board_position_unique (board_id, position),
  UNIQUE KEY columns_board_name_unique (board_id, name),
  KEY columns_board_id_idx (board_id),
  CONSTRAINT columns_board_id_fk FOREIGN KEY (board_id) REFERENCES boards(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tasks (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  column_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  assigned_to BIGINT UNSIGNED NULL,
  priority VARCHAR(16) NOT NULL,
  deadline DATETIME NULL,
  status VARCHAR(16) NOT NULL,
  position INT NOT NULL,
  created_by BIGINT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY tasks_column_position_idx (column_id, position),
  KEY tasks_assigned_to_idx (assigned_to),
  KEY tasks_created_by_idx (created_by),
  CONSTRAINT tasks_column_id_fk FOREIGN KEY (column_id) REFERENCES columns(id),
  CONSTRAINT tasks_assigned_to_fk FOREIGN KEY (assigned_to) REFERENCES users(id),
  CONSTRAINT tasks_created_by_fk FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE task_comments (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  task_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  body TEXT NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY task_comments_task_id_idx (task_id),
  KEY task_comments_user_id_idx (user_id),
  CONSTRAINT task_comments_task_id_fk FOREIGN KEY (task_id) REFERENCES tasks(id),
  CONSTRAINT task_comments_user_id_fk FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE task_history (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  task_id BIGINT UNSIGNED NOT NULL,
  action VARCHAR(64) NOT NULL,
  old_value TEXT NOT NULL,
  new_value TEXT NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY task_history_task_id_idx (task_id),
  KEY task_history_user_id_idx (user_id),
  CONSTRAINT task_history_task_id_fk FOREIGN KEY (task_id) REFERENCES tasks(id),
  CONSTRAINT task_history_user_id_fk FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
