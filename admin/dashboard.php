<?php
require "../auth/auth_check.php";
requireRole(1);

$pageTitle = "Dashboard";

$usersCount    = $conn->query("SELECT COUNT(*) c FROM users")->fetch_assoc()['c'];
$rolesCount    = $conn->query("SELECT COUNT(*) c FROM roles")->fetch_assoc()['c'];
$studentsCount = $conn->query("SELECT COUNT(*) c FROM users WHERE role_id=3")->fetch_assoc()['c'];
$logsCount     = $conn->query("SELECT COUNT(*) c FROM audit_logs")->fetch_assoc()['c'];
$teachersCount = $conn->query("SELECT COUNT(*) c FROM users WHERE role_id=2")->fetch_assoc()['c'];
$facultyCount  = $conn->query("SELECT COUNT(*) c FROM users WHERE role_id=4")->fetch_assoc()['c'];

$logs = $conn->query("SELECT al.*, u.full_name FROM audit_logs al LEFT JOIN users u ON al.user_id=u.user_id ORDER BY al.created_at DESC LIMIT 8");

// Role distribution for pure CSS chart
$roleData = [
    ['label' => 'Admins',   'count' => (int)$conn->query("SELECT COUNT(*) c FROM users WHERE role_id=1")->fetch_assoc()['c'], 'color' => '#6c63ff'],
    ['label' => 'Teachers', 'count' => (int)$teachersCount, 'color' => '#60a5fa'],
    ['label' => 'Students', 'count' => (int)$studentsCount, 'color' => '#22d3a0'],
    ['label' => 'Faculty',  'count' => (int)$facultyCount,  'color' => '#fbbf24'],
];
$maxCount = max(array_column($roleData, 'count')) ?: 1;

include "../layouts/header.php";
?>

<div class="stats-grid">
  <div class="stat-card accent">
    <div class="stat-icon accent">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
    </div>
    <div class="stat-value"><?= $usersCount ?></div>
    <div class="stat-label">Total Users</div>
  </div>
  <div class="stat-card green">
    <div class="stat-icon green">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
    </div>
    <div class="stat-value"><?= $studentsCount ?></div>
    <div class="stat-label">Students</div>
  </div>
  <div class="stat-card blue">
    <div class="stat-icon blue">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
    </div>
    <div class="stat-value"><?= $rolesCount ?></div>
    <div class="stat-label">Roles</div>
  </div>
  <div class="stat-card amber">
    <div class="stat-icon amber">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
    </div>
    <div class="stat-value"><?= $logsCount ?></div>
    <div class="stat-label">Audit Logs</div>
  </div>
</div>

<div class="grid-2" style="margin-bottom:24px">
  <!-- Role Distribution Bar Chart (pure CSS) -->
  <div class="card">
    <div class="card-title">User Role Distribution</div>
    <div style="display:flex;flex-direction:column;gap:14px;margin-top:8px">
      <?php foreach ($roleData as $r): ?>
        <div>
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
            <span style="font-size:13px;color:var(--muted)"><?= $r['label'] ?></span>
            <span style="font-size:13px;font-weight:600;color:var(--text)"><?= $r['count'] ?></span>
          </div>
          <div class="progress-bar">
            <div class="progress-fill" style="width:<?= $usersCount > 0 ? round($r['count']/$usersCount*100) : 0 ?>%;background:<?= $r['color'] ?>"></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="sep"></div>
    <div style="display:flex;gap:16px;flex-wrap:wrap">
      <?php foreach ($roleData as $r): ?>
        <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--muted)">
          <span style="width:10px;height:10px;border-radius:50%;background:<?= $r['color'] ?>;display:inline-block"></span>
          <?= $r['label'] ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Quick Summary -->
  <div class="card">
    <div class="card-title">System Summary</div>
    <ul class="info-list">
      <li><span class="key">Administrators</span><span class="val"><span class="badge badge-purple"><?= $roleData[0]['count'] ?></span></span></li>
      <li><span class="key">Teachers</span><span class="val"><span class="badge badge-blue"><?= $teachersCount ?></span></span></li>
      <li><span class="key">Students</span><span class="val"><span class="badge badge-green"><?= $studentsCount ?></span></span></li>
      <li><span class="key">Faculty</span><span class="val"><span class="badge badge-amber"><?= $facultyCount ?></span></span></li>
      <li><span class="key">Total Roles</span><span class="val"><?= $rolesCount ?></span></li>
      <li><span class="key">Total Audit Logs</span><span class="val"><?= $logsCount ?></span></li>
    </ul>
  </div>
</div>

<!-- Recent Audit Logs -->
<div class="card">
  <div class="page-header" style="margin-bottom:16px">
    <div>
      <div class="card-title" style="margin-bottom:0">Recent Activity</div>
      <p style="font-size:12px;color:var(--muted);margin-top:2px">Last 8 audit events</p>
    </div>
    <a href="audit_logs.php" class="btn btn-secondary btn-sm">View All</a>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>User</th>
          <th>Action</th>
          <th>Timestamp</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($logs && $logs->num_rows): ?>
          <?php while ($l = $logs->fetch_assoc()): ?>
            <tr>
              <td><span style="font-weight:500"><?= e($l['full_name'] ?? 'Unknown') ?></span></td>
              <td><?= e($l['action']) ?></td>
              <td><span class="mono" style="color:var(--muted)"><?= e($l['created_at']) ?></span></td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="3"><div class="empty-state"><p>No audit logs yet</p></div></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include "../layouts/footer.php"; ?>
