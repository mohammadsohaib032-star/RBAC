<?php
session_start();
require "../config/db.php";

if (isset($_SESSION['role_id'])) {
    header("Location: ../index.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "All fields are required.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username=? AND status='active' LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id']   = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role_id']   = $user['role_id'];
                $_SESSION['ag_number'] = $user['ag_number'] ?? '';

                logAudit($conn, $user['user_id'], "User logged in");

                switch ($user['role_id']) {
                    case 1: header("Location: ../admin/dashboard.php");          break;
                    case 2: header("Location: ../teacher/dashboard.php");         break;
                    case 3: header("Location: ../student/dashboard.php");         break;
                    case 4: header("Location: ../faculty/lecture_schedule.php");  break;
                    default: header("Location: ../index.php");
                }
                exit;
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "User not found or account inactive.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login | CS Department</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="auth-body">
  <div class="auth-card">
    <div class="auth-logo">CS</div>
    <h2>Welcome Back</h2>
    <p class="sub">CS Department Portal — Sign in to continue</p>

    <?php if ($error): ?>
      <div class="alert alert-danger">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <?= e($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" value="<?= e($_POST['username'] ?? '') ?>" placeholder="Enter your username" required autofocus>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="Enter your password" required>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:4px">
        Sign In
      </button>
    </form>

    <div style="text-align:center;margin-top:20px;font-size:13px;color:var(--muted)">
      Don't have an account?
      <a href="register.php" style="color:var(--accent2);font-weight:600">Register</a>
    </div>
    <div style="text-align:center;margin-top:8px;font-size:13px">
      <a href="forgot_password.php" style="color:var(--muted)">Forgot Password?</a>
    </div>
  </div>
</body>
</html>
