<?php
/**
 * CS Department RBAC Portal — Setup Script
 * Run this ONCE after importing database.sql
 * Then DELETE this file for security.
 */
require "config/db.php";

$password = 'password123';
$hash     = password_hash($password, PASSWORD_BCRYPT);

$usernames = ['admin','teacher1','teacher2','student1','student2','student3','faculty1'];
$updated = 0;
foreach ($usernames as $u) {
    $stmt = $conn->prepare("UPDATE users SET password=? WHERE username=?");
    $stmt->bind_param("ss", $hash, $u);
    $stmt->execute();
    $updated += $stmt->affected_rows;
}

// Verify
$users = $conn->query("SELECT username, role_id, status FROM users ORDER BY role_id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Setup | CS Department</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body class="auth-body">
<div class="auth-card" style="max-width:520px">
  <div class="auth-logo">CS</div>
  <h2>Setup Complete</h2>
  <p class="sub">CS Department RBAC Portal</p>

  <div class="alert alert-success" style="margin-top:16px">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg>
    Passwords hashed successfully for <?= $updated ?> user(s).
  </div>

  <div class="card" style="margin-top:20px;padding:0;">
    <table>
      <thead><tr><th>Username</th><th>Role</th><th>Password</th><th>Status</th></tr></thead>
      <tbody>
        <?php while ($u = $users->fetch_assoc()): ?>
        <tr>
          <td class="mono"><?= htmlspecialchars($u['username']) ?></td>
          <td><?php $rn=[1=>'Admin',2=>'Teacher',3=>'Student',4=>'Faculty']; echo $rn[$u['role_id']]??'?'; ?></td>
          <td class="mono">password123</td>
          <td><span class="badge badge-green"><?= $u['status'] ?></span></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <div class="alert alert-danger" style="margin-top:16px">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <strong>Delete this file (setup.php) now!</strong> It is a security risk if left on the server.
  </div>

  <a href="auth/login.php" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:8px">
    Go to Login →
  </a>
</div>
</body>
</html>
