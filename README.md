# CS Department RBAC Portal

A complete Role-Based Access Control (RBAC) web portal for a CS Department,
built with **pure PHP + MySQL** and custom CSS (no Tailwind, no Chart.js).

---

## 📁 Project Structure

```
cs_department_rbac/
├── index.php                  ← Role-based router
├── database.sql               ← Complete DB schema + sample data
├── config/
│   └── db.php                 ← MySQL connection + helpers
├── auth/
│   ├── login.php
│   ├── register.php
│   ├── logout.php
│   ├── forgot_password.php
│   └── auth_check.php         ← requireRole() guard
├── layouts/
│   ├── sidebar.php
│   ├── header.php
│   └── footer.php
├── assets/
│   └── style.css              ← All custom CSS
├── admin/
│   ├── dashboard.php
│   ├── users.php
│   ├── roles.php
│   ├── permissions.php
│   ├── role_permissions.php   ← RBAC Matrix
│   └── audit_logs.php
├── teacher/
│   ├── dashboard.php
│   ├── students.php
│   └── marks.php
├── student/
│   ├── dashboard.php
│   ├── attendence.php
│   └── grades.php
└── faculty/
    └── lecture_schedule.php
```

---

## ⚙️ Setup Instructions

### Step 1 — Import Database
Open **phpMyAdmin** → Import tab → select `database.sql` → click Go.

Or via terminal:
```bash
mysql -u root -p < database.sql
```

### Step 2 — Configure DB Connection
Edit `config/db.php` and update your credentials if needed:
```php
$conn = new mysqli("localhost", "root", "", "cs_department_rbac");
```

### Step 3 — Place Files
Copy the project folder to your web server root:
- **XAMPP:** `C:/xampp/htdocs/cs_department_rbac/`
- **WAMP:**  `C:/wamp64/www/cs_department_rbac/`

### Step 4 — Open in Browser
```
http://localhost/cs_department_rbac/
```

---

## 🔑 Default Login Credentials

| Role    | Username  | Password    |
|---------|-----------|-------------|
| Admin   | admin     | password123 |
| Teacher | teacher1  | password123 |
| Teacher | teacher2  | password123 |
| Student | student1  | password123 |
| Student | student2  | password123 |
| Student | student3  | password123 |
| Faculty | faculty1  | password123 |

---

## 👥 Role Capabilities

### 🔴 Admin (Role 1)
- View system dashboard with stats
- Add / Edit / Delete users
- View all roles and permissions
- View RBAC Matrix (role → module → permission)
- View audit logs

### 🔵 Teacher (Role 2)
- View dashboard with class stats
- View and update student attendance
- Enter/update subject marks (auto-calculates grade)
- Quick marks entry page

### 🟢 Student (Role 3)
- View personal dashboard (attendance + grade)
- View attendance with visual progress bar
- View subject-wise marks and grade scale

### 🟡 Faculty (Role 4)
- View lecture timetable
- Add new lectures (class, time, location, teacher)
- Delete lectures

---

## 🛡️ RBAC Implementation

- Every protected page calls `requireRole($roleId)` from `auth/auth_check.php`
- Unauthorized access returns a styled 403 page
- All write operations are logged to `audit_logs` table
- Passwords stored as bcrypt hashes via `password_hash()`
- All output escaped with `e()` (htmlspecialchars wrapper)

---

## 🧰 Tech Stack

| Layer    | Technology          |
|----------|---------------------|
| Backend  | PHP 8.x (MySQLi)    |
| Database | MySQL 5.7+          |
| Frontend | Pure CSS (custom)   |
| Fonts    | Google Fonts (Outfit, JetBrains Mono) |
| Icons    | Inline SVG          |
