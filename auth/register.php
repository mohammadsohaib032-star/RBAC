<?php
session_start();
require "../config/db.php";

if (isset($_SESSION['role_id'])) {
    header("Location: ../index.php");
    exit;
}

$error = $success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = trim($_POST['full_name']);
    $username  = trim($_POST['username']);
    $ag_number = strtoupper(trim($_POST['ag_number']));
    $password  = $_POST['password'];
    $confirm   = $_POST['confirm_password'];

    if ($full_name === "" || $username === "" || $password === "" || $ag_number === "") {
        $error = "All fields are required.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (!preg_match('/^AG-\d+$/i', $ag_number)) {
        $error = "AG Number must be in format: AG-1001";
    } else {
        // Check ag_number not already used
        $chk = $conn->prepare("SELECT user_id FROM users WHERE ag_number=?");
        $chk->bind_param("s", $ag_number);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $error = "This AG Number is already registered.";
        } else {
            $hashed  = password_hash($password, PASSWORD_BCRYPT);
            $role_id = 3; // Student
            $status  = "active";

            $conn->begin_transaction();
            try {
                $stmt = $conn->prepare(
                    "INSERT INTO users (username, password, full_name, ag_number, role_id, status) VALUES (?, ?, ?, ?, ?, ?)"
                );
                $stmt->bind_param("ssssis", $username, $hashed, $full_name, $ag_number, $role_id, $status);
                $stmt->execute();
                $new_user_id = $conn->insert_id;

                // Auto-assign to first available teacher (lowest user_id with role 2)
                $t = $conn->query("SELECT user_id FROM users WHERE role_id=2 ORDER BY user_id LIMIT 1")->fetch_assoc();
                $teacher_id = $t ? $t['user_id'] : $new_user_id;

                $sr = $conn->prepare(
                    "INSERT INTO student_records (ag_number, teacher_id, attendance, marks, grades) VALUES (?, ?, 0, NULL, NULL)"
                );
                $sr->bind_param("si", $ag_number, $teacher_id);
                $sr->execute();

                logAudit($conn, $new_user_id, "New student registered: $username (AG: $ag_number)");
                $conn->commit();
                $success = "Account created! You can now login with your credentials.";
            } catch (Exception $e) {
                $conn->rollback();
                $error = "Username already taken. Please choose another.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register | CS Department</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="auth-body">
  <div class="auth-card">
    <div class="auth-logo">CS</div>
    <h2>Create Account</h2>
    <p class="sub">Student self-registration — Contact admin for other roles</p>

    <?php if ($error): ?>
      <div class="alert alert-danger">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <?= e($error) ?>
      </div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg>
        <?= e($success) ?>
      </div>
    <?php endif; ?>

    <?php if (!$success): ?>
    <form method="POST" autocomplete="off">
      <div class="form-group">
        <label>Full Name</label>
        <input type="text" name="full_name" value="<?= e($_POST['full_name'] ?? '') ?>" placeholder="Your full name" required>
      </div>
      <div class="form-group">
        <label>AG Number</label>
        <input type="text" name="ag_number" value="<?= e($_POST['ag_number'] ?? '') ?>" placeholder="e.g. AG-1001" required>
      </div>
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" value="<?= e($_POST['username'] ?? '') ?>" placeholder="Choose a username" required>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="Min. 6 characters" required>
      </div>
      <div class="form-group">
        <label>Confirm Password</label>
        <input type="password" name="confirm_password" placeholder="Repeat password" required>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:4px">
        Create Account
      </button>
    </form>
    <?php else: ?>
      <a href="login.php" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:16px">Go to Login</a>
    <?php endif; ?>

    <div style="text-align:center;margin-top:20px;font-size:13px;color:var(--muted)">
      Already have an account? <a href="login.php" style="color:var(--accent2);font-weight:600">Sign In</a>
    </div>
  </div>
</body>
</html>
