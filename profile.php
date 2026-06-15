<?php
require "auth/auth_check.php";
requireRole([1,2,3,4]);
$pageTitle = "My Profile";

$msg = $err = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name   = trim($_POST['full_name']);
    $current_pw  = $_POST['current_password'];
    $new_pw      = $_POST['new_password'];
    $confirm_pw  = $_POST['confirm_password'];

    if ($full_name === '') {
        $err = "Full name cannot be empty.";
    } else {
        // Fetch current hash
        $stmt = $conn->prepare("SELECT password FROM users WHERE user_id=?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        $changingPassword = ($new_pw !== '');

        if ($changingPassword) {
            if (!password_verify($current_pw, $row['password'])) {
                $err = "Current password is incorrect.";
            } elseif (strlen($new_pw) < 6) {
                $err = "New password must be at least 6 characters.";
            } elseif ($new_pw !== $confirm_pw) {
                $err = "New passwords do not match.";
            } else {
                $hashed = password_hash($new_pw, PASSWORD_BCRYPT);
                $stmt2  = $conn->prepare("UPDATE users SET full_name=?,password=? WHERE user_id=?");
                $stmt2->bind_param("ssi", $full_name, $hashed, $_SESSION['user_id']);
                $stmt2->execute();
                $_SESSION['full_name'] = $full_name;
                logAudit($conn, $_SESSION['user_id'], "Changed profile and password");
                $msg = "Profile and password updated successfully.";
            }
        } else {
            $stmt2 = $conn->prepare("UPDATE users SET full_name=? WHERE user_id=?");
            $stmt2->bind_param("si", $full_name, $_SESSION['user_id']);
            $stmt2->execute();
            $_SESSION['full_name'] = $full_name;
            logAudit($conn, $_SESSION['user_id'], "Updated profile name");
            $msg = "Profile updated successfully.";
        }
    }
}

// Fetch fresh user data
$stmt = $conn->prepare("SELECT u.*,r.role_name FROM users u JOIN roles r ON u.role_id=r.role_id WHERE u.user_id=?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Redirect back to correct folder for layout
$role = $_SESSION['role_id'];
$base = match((int)$role) {1=>'admin',2=>'teacher',3=>'student',4=>'faculty',default=>'admin'};
// Override header path for root-level file
$_SERVER['PHP_SELF'] = '/profile.php'; // trick sidebar active state

// Include layout inline since we're at root
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>My Profile | CS Department</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="layout">
<?php
// Inline sidebar (adjusted paths for root)
$svgDash='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>';
$svgUsers='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="9" cy="7" r="4"/><path d="M3 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/><path d="M21 21v-2a4 4 0 0 0-3-3.87"/></svg>';
$svgRoles='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>';
$svgPerms='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>';
$svgMatrix='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M3 3h18v18H3z"/><path d="M3 9h18M3 15h18M9 3v18M15 3v18"/></svg>';
$svgLogs='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>';
$svgStudents='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>';
$svgMarks='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>';
$svgAttend='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><polyline points="9 16 11 18 15 14"/></svg>';
$svgGrades='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11"/></svg>';
$svgSched='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>';
$svgLogout='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>';

$fname   = $_SESSION['full_name'] ?? 'User';
$initials= strtoupper(substr($fname,0,1));
$roleNames=[1=>'Administrator',2=>'Teacher',3=>'Student',4=>'Faculty'];
$roleLabel=$roleNames[$role]??'User';
?>
<aside class="sidebar">
  <div class="sidebar-brand">
    <div class="logo">
      <div class="logo-icon">CS</div>
      <div class="logo-text">CS Department<span>RBAC Portal</span></div>
    </div>
  </div>
  <nav class="sidebar-nav">
    <?php if ($role==1): ?>
      <div class="nav-label">Overview</div>
      <a href="admin/dashboard.php" class="nav-link"><?= $svgDash ?><span>Dashboard</span></a>
      <div class="nav-label">Management</div>
      <a href="admin/users.php" class="nav-link"><?= $svgUsers ?><span>Users</span></a>
      <a href="admin/roles.php" class="nav-link"><?= $svgRoles ?><span>Roles</span></a>
      <a href="admin/permissions.php" class="nav-link"><?= $svgPerms ?><span>Permissions</span></a>
      <a href="admin/role_permissions.php" class="nav-link"><?= $svgMatrix ?><span>RBAC Matrix</span></a>
      <div class="nav-label">Monitoring</div>
      <a href="admin/audit_logs.php" class="nav-link"><?= $svgLogs ?><span>Audit Logs</span></a>
    <?php elseif ($role==2): ?>
      <div class="nav-label">Overview</div>
      <a href="teacher/dashboard.php" class="nav-link"><?= $svgDash ?><span>Dashboard</span></a>
      <div class="nav-label">Academics</div>
      <a href="teacher/students.php" class="nav-link"><?= $svgStudents ?><span>My Students</span></a>
      <a href="teacher/marks.php" class="nav-link"><?= $svgMarks ?><span>Marks</span></a>
    <?php elseif ($role==3): ?>
      <div class="nav-label">Overview</div>
      <a href="student/dashboard.php" class="nav-link"><?= $svgDash ?><span>Dashboard</span></a>
      <div class="nav-label">My Records</div>
      <a href="student/attendence.php" class="nav-link"><?= $svgAttend ?><span>Attendance</span></a>
      <a href="student/grades.php" class="nav-link"><?= $svgGrades ?><span>Grades</span></a>
    <?php elseif ($role==4): ?>
      <div class="nav-label">Faculty</div>
      <a href="faculty/lecture_schedule.php" class="nav-link"><?= $svgSched ?><span>Lecture Schedule</span></a>
    <?php endif; ?>
    <div class="nav-label">Account</div>
    <a href="profile.php" class="nav-link active"><?= $svgPerms ?><span>My Profile</span></a>
  </nav>
  <div class="sidebar-footer">
    <div class="user-card">
      <div class="user-avatar"><?= $initials ?></div>
      <div class="user-info">
        <div class="name"><?= e($fname) ?></div>
        <div class="role"><?= $roleLabel ?></div>
      </div>
      <form method="get" action="auth/logout.php" style="margin:0">
        <button type="submit" class="logout-btn" title="Logout"><?= $svgLogout ?></button>
      </form>
    </div>
  </div>
</aside>

<div class="main-content">
  <div class="topbar"><div><h1>My Profile</h1></div></div>

  <?php if ($msg): ?><div class="alert alert-success"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg><?= e($msg) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="alert alert-danger"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg><?= e($err) ?></div><?php endif; ?>

  <div class="grid-2">
    <!-- Profile Info Card -->
    <div class="card">
      <div class="card-title">Account Information</div>
      <ul class="info-list">
        <li><span class="key">Full Name</span><span class="val"><?= e($user['full_name']) ?></span></li>
        <li><span class="key">Username</span><span class="val mono"><?= e($user['username']) ?></span></li>
        <li><span class="key">Role</span>
          <span class="val">
            <?php $bm=[1=>'badge-purple',2=>'badge-blue',3=>'badge-green',4=>'badge-amber']; ?>
            <span class="badge <?= $bm[$user['role_id']]??'badge-blue' ?>"><?= e($user['role_name']) ?></span>
          </span>
        </li>
        <li><span class="key">Status</span>
          <span class="val">
            <span class="badge <?= $user['status']==='active'?'badge-green':'badge-red' ?>"><?= e($user['status']) ?></span>
          </span>
        </li>
        <?php if ($user['ag_number']): ?>
        <li><span class="key">AG Number</span><span class="val mono"><?= e($user['ag_number']) ?></span></li>
        <?php endif; ?>
        <li><span class="key">Member Since</span><span class="val"><?= date('d M Y', strtotime($user['created_at'])) ?></span></li>
      </ul>
    </div>

    <!-- Edit Form -->
    <div class="card">
      <div class="card-title">Update Profile</div>
      <form method="post" autocomplete="off">
        <div class="form-group">
          <label>Full Name</label>
          <input type="text" name="full_name" value="<?= e($user['full_name']) ?>" required>
        </div>
        <div class="sep"></div>
        <div class="card-sub" style="margin-bottom:12px;font-size:13px">Change Password <span style="color:var(--muted)">(leave blank to keep current)</span></div>
        <div class="form-group">
          <label>Current Password</label>
          <input type="password" name="current_password" placeholder="Your current password" autocomplete="current-password">
        </div>
        <div class="form-group">
          <label>New Password</label>
          <input type="password" name="new_password" placeholder="Min. 6 characters" autocomplete="new-password">
        </div>
        <div class="form-group">
          <label>Confirm New Password</label>
          <input type="password" name="confirm_password" placeholder="Repeat new password" autocomplete="new-password">
        </div>
        <div style="display:flex;justify-content:flex-end">
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>
</div>
</body>
</html>
