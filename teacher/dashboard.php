<?php
require "../auth/auth_check.php";
requireRole(2);
$pageTitle = "Teacher Dashboard";

$myStudents    = (int)$conn->query("SELECT COUNT(*) c FROM student_records WHERE teacher_id={$_SESSION['user_id']}")->fetch_assoc()['c'];
$totalStudents = (int)$conn->query("SELECT COUNT(*) c FROM users WHERE role_id=3")->fetch_assoc()['c'];
$avgAttendance = (float)($conn->query("SELECT COALESCE(AVG(attendance),0) a FROM student_records WHERE teacher_id={$_SESSION['user_id']}")->fetch_assoc()['a']);
$avgAttendance = round($avgAttendance, 1);

// Count students by grade under my care
$gradeStats = [];
$res = $conn->query("SELECT grades, COUNT(*) c FROM student_records WHERE teacher_id={$_SESSION['user_id']} AND grades IS NOT NULL GROUP BY grades");
while ($r = $res->fetch_assoc()) $gradeStats[$r['grades']] = $r['c'];

include "../layouts/header.php";
?>

<div class="stats-grid">
  <div class="stat-card green">
    <div class="stat-icon green">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
    </div>
    <div class="stat-value"><?= $myStudents ?></div>
    <div class="stat-label">My Students</div>
  </div>
  <div class="stat-card blue">
    <div class="stat-icon blue">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
    </div>
    <div class="stat-value"><?= $totalStudents ?></div>
    <div class="stat-label">Total Students</div>
  </div>
  <div class="stat-card <?= $avgAttendance >= 75 ? 'green' : ($avgAttendance >= 50 ? 'amber' : 'accent') ?>">
    <div class="stat-icon <?= $avgAttendance >= 75 ? 'green' : ($avgAttendance >= 50 ? 'amber' : 'accent') ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="18" rx="2"/><polyline points="9 16 11 18 15 14"/></svg>
    </div>
    <div class="stat-value"><?= $avgAttendance ?>%</div>
    <div class="stat-label">Class Avg. Attendance</div>
  </div>
</div>

<div class="grid-2">
  <div class="card">
    <div class="card-title">Quick Actions</div>
    <div style="display:flex;flex-direction:column;gap:10px">
      <a href="students.php" class="btn btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        Update Student Records
      </a>
      <a href="marks.php" class="btn btn-secondary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        View All Marks
      </a>
      <a href="../profile.php" class="btn btn-secondary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
        My Profile
      </a>
    </div>
  </div>

  <div class="card">
    <div class="card-title">Grade Distribution</div>
    <?php if (!empty($gradeStats)): ?>
      <?php
        $gradeColors = ['A+'=>'#22d3a0','A'=>'#22d3a0','B'=>'#60a5fa','C'=>'#fbbf24','F'=>'#f87171'];
        foreach ($gradeStats as $g => $cnt):
          $pct = $myStudents > 0 ? round($cnt / $myStudents * 100) : 0;
          $col = $gradeColors[$g] ?? '#6c63ff';
      ?>
        <div style="margin-bottom:12px">
          <div style="display:flex;justify-content:space-between;margin-bottom:5px">
            <span style="font-size:13px;font-weight:600;color:var(--text)">Grade <?= e($g) ?></span>
            <span style="font-size:13px;color:var(--muted)"><?= $cnt ?> student(s) — <?= $pct ?>%</span>
          </div>
          <div class="progress-bar">
            <div class="progress-fill" style="width:<?= $pct ?>%;background:<?= $col ?>"></div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="empty-state"><p>No grades recorded yet.</p></div>
    <?php endif; ?>
  </div>
</div>

<!-- Attendance overview -->
<div class="card">
  <div class="card-title">Class Attendance Status</div>
  <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap">
    <div style="flex:1;min-width:200px">
      <div style="display:flex;justify-content:space-between;margin-bottom:8px">
        <span style="color:var(--muted);font-size:13px">Average</span>
        <strong><?= $avgAttendance ?>%</strong>
      </div>
      <?php $fc = $avgAttendance >= 75 ? 'good' : ($avgAttendance >= 50 ? 'warn' : 'bad'); ?>
      <div class="progress-bar" style="height:14px">
        <div class="progress-fill <?= $fc ?>" style="width:<?= $avgAttendance ?>%"></div>
      </div>
    </div>
    <div>
      <?php if ($avgAttendance >= 75): ?>
        <span class="badge badge-green" style="font-size:13px;padding:6px 14px">✅ Class is on track</span>
      <?php elseif ($avgAttendance >= 50): ?>
        <span class="badge badge-amber" style="font-size:13px;padding:6px 14px">⚠️ Needs attention</span>
      <?php else: ?>
        <span class="badge badge-red" style="font-size:13px;padding:6px 14px">❌ Critically low</span>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include "../layouts/footer.php"; ?>
