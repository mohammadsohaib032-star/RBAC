-- ============================================================
--  CS Department RBAC Portal — Complete Database
--  STEP 1: Import this file
--  STEP 2: Open browser → http://localhost/cs_department_rbac/setup.php
--  STEP 3: Delete setup.php after running it once
-- ============================================================

CREATE DATABASE IF NOT EXISTS cs_department_rbac
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cs_department_rbac;

-- ROLES
CREATE TABLE IF NOT EXISTS roles (
  role_id     INT AUTO_INCREMENT PRIMARY KEY,
  role_name   VARCHAR(50) NOT NULL UNIQUE,
  description VARCHAR(255)
) ENGINE=InnoDB;

INSERT INTO roles (role_name, description) VALUES
  ('Admin',   'Full system access — manages users, roles, permissions, audit logs'),
  ('Teacher', 'Manages student records, marks, attendance'),
  ('Student', 'Read-only access to own records — attendance and grades'),
  ('Faculty', 'Manages lecture timetable and schedule')
ON DUPLICATE KEY UPDATE description=VALUES(description);

-- PERMISSIONS
CREATE TABLE IF NOT EXISTS permissions (
  permission_id   INT AUTO_INCREMENT PRIMARY KEY,
  permission_name VARCHAR(100) NOT NULL UNIQUE,
  description     VARCHAR(255)
) ENGINE=InnoDB;

INSERT INTO permissions (permission_name, description) VALUES
  ('view',   'Can view records'),
  ('create', 'Can create new records'),
  ('edit',   'Can edit existing records'),
  ('delete', 'Can delete records')
ON DUPLICATE KEY UPDATE description=VALUES(description);

-- MODULES
CREATE TABLE IF NOT EXISTS modules (
  module_id   INT AUTO_INCREMENT PRIMARY KEY,
  module_name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

INSERT INTO modules (module_name) VALUES
  ('Users'),('Roles'),('Permissions'),('Student Records'),('Audit Logs'),('Lecture Schedule')
ON DUPLICATE KEY UPDATE module_name=VALUES(module_name);

-- USERS
CREATE TABLE IF NOT EXISTS users (
  user_id    INT AUTO_INCREMENT PRIMARY KEY,
  username   VARCHAR(100) NOT NULL UNIQUE,
  password   VARCHAR(255) NOT NULL,
  full_name  VARCHAR(150) NOT NULL,
  ag_number  VARCHAR(50)  DEFAULT NULL UNIQUE,
  role_id    INT          NOT NULL DEFAULT 3,
  status     ENUM('active','inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (role_id) REFERENCES roles(role_id)
) ENGINE=InnoDB;

-- Passwords are set by setup.php as bcrypt of 'password123'
INSERT INTO users (username, password, full_name, ag_number, role_id, status) VALUES
  ('admin',    'SETUP_NEEDED', 'System Administrator', NULL,      1, 'active'),
  ('teacher1', 'SETUP_NEEDED', 'Dr. Usman Tariq',      NULL,      2, 'active'),
  ('teacher2', 'SETUP_NEEDED', 'Ms. Sana Malik',       NULL,      2, 'active'),
  ('student1', 'SETUP_NEEDED', 'Ali Hassan',           'AG-1001', 3, 'active'),
  ('student2', 'SETUP_NEEDED', 'Sara Ahmed',           'AG-1002', 3, 'active'),
  ('student3', 'SETUP_NEEDED', 'Bilal Khan',           'AG-1003', 3, 'active'),
  ('faculty1', 'SETUP_NEEDED', 'Prof. Tariq Mehmood',  NULL,      4, 'active')
ON DUPLICATE KEY UPDATE full_name=VALUES(full_name);

-- ROLE PERMISSIONS
CREATE TABLE IF NOT EXISTS role_permissions (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  role_id       INT NOT NULL,
  module_id     INT NOT NULL,
  permission_id INT NOT NULL,
  UNIQUE KEY unique_rmp (role_id, module_id, permission_id),
  FOREIGN KEY (role_id)       REFERENCES roles(role_id)       ON DELETE CASCADE,
  FOREIGN KEY (module_id)     REFERENCES modules(module_id)   ON DELETE CASCADE,
  FOREIGN KEY (permission_id) REFERENCES permissions(permission_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Admin: all permissions on all modules
INSERT IGNORE INTO role_permissions (role_id, module_id, permission_id)
SELECT 1, m.module_id, p.permission_id FROM modules m CROSS JOIN permissions p;

-- Teacher: view+create+edit on Student Records
INSERT IGNORE INTO role_permissions (role_id, module_id, permission_id)
SELECT 2, m.module_id, p.permission_id FROM modules m, permissions p
WHERE m.module_name='Student Records' AND p.permission_name IN ('view','create','edit');

-- Student: view-only on Student Records
INSERT IGNORE INTO role_permissions (role_id, module_id, permission_id)
SELECT 3, m.module_id, p.permission_id FROM modules m, permissions p
WHERE m.module_name='Student Records' AND p.permission_name='view';

-- Faculty: all on Lecture Schedule
INSERT IGNORE INTO role_permissions (role_id, module_id, permission_id)
SELECT 4, m.module_id, p.permission_id FROM modules m, permissions p
WHERE m.module_name='Lecture Schedule' AND p.permission_name IN ('view','create','edit','delete');

-- STUDENT RECORDS (UNIQUE on ag_number)
CREATE TABLE IF NOT EXISTS student_records (
  student_id INT AUTO_INCREMENT PRIMARY KEY,
  ag_number  VARCHAR(50) NOT NULL UNIQUE,
  teacher_id INT         NOT NULL,
  attendance INT         DEFAULT 0,
  marks      TEXT,
  grades     VARCHAR(5)  DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (teacher_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO student_records (ag_number, teacher_id, attendance, marks, grades)
SELECT 'AG-1001',(SELECT user_id FROM users WHERE username='teacher1'),85,'{"Data Structures":88,"Algorithms":76,"OOP":92,"DBMS":80}','A'
ON DUPLICATE KEY UPDATE attendance=VALUES(attendance),marks=VALUES(marks),grades=VALUES(grades);

INSERT INTO student_records (ag_number, teacher_id, attendance, marks, grades)
SELECT 'AG-1002',(SELECT user_id FROM users WHERE username='teacher1'),62,'{"Data Structures":55,"Algorithms":60,"OOP":70,"DBMS":58}','C'
ON DUPLICATE KEY UPDATE attendance=VALUES(attendance),marks=VALUES(marks),grades=VALUES(grades);

INSERT INTO student_records (ag_number, teacher_id, attendance, marks, grades)
SELECT 'AG-1003',(SELECT user_id FROM users WHERE username='teacher2'),45,'{"Data Structures":40,"Algorithms":35,"OOP":50,"DBMS":42}','F'
ON DUPLICATE KEY UPDATE attendance=VALUES(attendance),marks=VALUES(marks),grades=VALUES(grades);

-- LECTURE SCHEDULE
CREATE TABLE IF NOT EXISTS lecture_schedule (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  class_name       VARCHAR(150) NOT NULL,
  lecture_time     VARCHAR(100) NOT NULL,
  lecture_location VARCHAR(100) NOT NULL,
  faculty_id       INT,
  created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (faculty_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO lecture_schedule (class_name, lecture_time, lecture_location, faculty_id)
SELECT 'Data Structures','Mon & Wed 10:00–11:30','Room 301',user_id FROM users WHERE username='teacher1';
INSERT INTO lecture_schedule (class_name, lecture_time, lecture_location, faculty_id)
SELECT 'Database Systems','Tue & Thu 09:00–10:30','Lab 2',user_id FROM users WHERE username='teacher2';
INSERT INTO lecture_schedule (class_name, lecture_time, lecture_location, faculty_id)
SELECT 'Object Oriented Programming','Mon & Fri 02:00–03:30','Room 205',user_id FROM users WHERE username='teacher1';

-- AUDIT LOGS
CREATE TABLE IF NOT EXISTS audit_logs (
  audit_id   INT AUTO_INCREMENT PRIMARY KEY,
  user_id    INT,
  action     TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;
