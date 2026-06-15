<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Forgot Password | CS Department</title>
  <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="auth-body">
  <div class="auth-card">
    <div class="auth-logo">CS</div>
    <h2>Forgot Password</h2>
    <p class="sub">Contact your system administrator to reset your password.</p>
    <div class="alert alert-info" style="margin-top:8px">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
      Please contact the admin at <strong>admin@csdept.edu</strong>
    </div>
    <a href="login.php" class="btn btn-secondary" style="width:100%;justify-content:center;margin-top:16px">Back to Login</a>
  </div>
</body>
</html>
