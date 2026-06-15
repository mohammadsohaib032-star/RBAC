<?php
$role    = $_SESSION['role_id']    ?? 0;
$fname   = $_SESSION['full_name']  ?? 'User';
$initials = strtoupper(substr($fname, 0, 1));

$roleNames = [1=>'Administrator',2=>'Teacher',3=>'Student',4=>'Faculty'];
$roleLabel  = $roleNames[$role] ?? 'User';

function navLink($href, $label, $icon, $active=false) {
    $cls = $active ? 'nav-link active' : 'nav-link';
    echo "<a href=\"$href\" class=\"$cls\">$icon <span>$label</span></a>";
}

$svgDash    ='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>';
$svgUsers   ='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="9" cy="7" r="4"/><path d="M3 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/><path d="M21 21v-2a4 4 0 0 0-3-3.87"/></svg>';
$svgRoles   ='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>';
$svgPerms   ='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>';
$svgMatrix  ='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M3 3h18v18H3z"/><path d="M3 9h18M3 15h18M9 3v18M15 3v18"/></svg>';
$svgLogs    ='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>';
$svgStudents='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>';
$svgMarks   ='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>';
$svgAttend  ='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><polyline points="9 16 11 18 15 14"/></svg>';
$svgGrades  ='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11"/></svg>';
$svgSched   ='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>';
$svgProfile ='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>';
$svgLogout  ='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>';

$cur = basename($_SERVER['PHP_SELF']);
// Profile page is at root level
$profileHref = ($role == 1) ? '../profile.php' : (($role == 2) ? '../profile.php' : (($role == 3) ? '../profile.php' : '../profile.php'));
?>
<aside class="sidebar">
  <div class="sidebar-brand">
    <div class="logo">
      <div class="logo-icon">CS</div>
      <div class="logo-text">CS Department<span>RBAC Portal</span></div>
    </div>
  </div>

  <nav class="sidebar-nav">
    <?php if ($role == 1): ?>
      <div class="nav-label">Overview</div>
      <?= navLink('dashboard.php',          'Dashboard',    $svgDash,     $cur=='dashboard.php') ?>
      <div class="nav-label">Management</div>
      <?= navLink('users.php',              'Users',        $svgUsers,    $cur=='users.php') ?>
      <?= navLink('roles.php',              'Roles',        $svgRoles,    $cur=='roles.php') ?>
      <?= navLink('permissions.php',        'Permissions',  $svgPerms,    $cur=='permissions.php') ?>
      <?= navLink('role_permissions.php',   'RBAC Matrix',  $svgMatrix,   $cur=='role_permissions.php') ?>
      <div class="nav-label">Monitoring</div>
      <?= navLink('audit_logs.php',         'Audit Logs',   $svgLogs,     $cur=='audit_logs.php') ?>
    <?php endif; ?>

    <?php if ($role == 2): ?>
      <div class="nav-label">Overview</div>
      <?= navLink('dashboard.php',  'Dashboard',    $svgDash,     $cur=='dashboard.php') ?>
      <div class="nav-label">Academics</div>
      <?= navLink('students.php',   'My Students',  $svgStudents, $cur=='students.php') ?>
      <?= navLink('marks.php',      'Marks',        $svgMarks,    $cur=='marks.php') ?>
    <?php endif; ?>

    <?php if ($role == 3): ?>
      <div class="nav-label">Overview</div>
      <?= navLink('dashboard.php',  'Dashboard',  $svgDash,   $cur=='dashboard.php') ?>
      <div class="nav-label">My Records</div>
      <?= navLink('attendence.php', 'Attendance', $svgAttend, $cur=='attendence.php') ?>
      <?= navLink('grades.php',     'Grades',     $svgGrades, $cur=='grades.php') ?>
    <?php endif; ?>

    <?php if ($role == 4): ?>
      <div class="nav-label">Faculty</div>
      <?= navLink('lecture_schedule.php', 'Lecture Schedule', $svgSched, $cur=='lecture_schedule.php') ?>
    <?php endif; ?>

    <div class="nav-label">Account</div>
    <?= navLink($profileHref, 'My Profile', $svgProfile, $cur=='profile.php') ?>
  </nav>

  <div class="sidebar-footer">
    <div class="user-card">
      <div class="user-avatar"><?= e($initials) ?></div>
      <div class="user-info">
        <div class="name"><?= e($fname) ?></div>
        <div class="role"><?= e($roleLabel) ?></div>
      </div>
      <form method="get" action="../auth/logout.php" style="margin:0">
        <button type="submit" class="logout-btn" title="Logout"><?= $svgLogout ?></button>
      </form>
    </div>
  </div>
</aside>
