<?php
require "../auth/auth_check.php";
requireRole(3);
$pageTitle = "My Attendance";

$student = null;
if (!empty($_SESSION['ag_number'])) {
    $stmt = $conn->prepare("SELECT attendance, marks, grades FROM student_records WHERE ag_number=?");
    $stmt->bind_param("s", $_SESSION['ag_number']);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
}

$attendance = $student['attendance'] ?? 0;
$attFill    = $attendance >= 75 ? 'good' : ($attendance >= 50 ? 'warn' : 'bad');

include "../layouts/header.php";
?>

<div class="page-header">
  <div><h2>My Attendance</h2><p>Your class attendance record</p></div>
</div>

<div class="grid-2">
  <div class="card">
    <div class="card-title">Attendance Overview</div>
    <div style="text-align:center;padding:20px 0">
      <div style="font-size:60px;font-weight:700;color:var(--text);line-height:1"><?= e($attendance) ?><span style="font-size:28px;color:var(--muted)">%</span></div>
      <p style="color:var(--muted);font-size:14px;margin-top:8px">Overall Attendance</p>
    </div>
    <div class="progress-bar" style="height:16px;margin-bottom:16px">
      <div class="progress-fill <?= $attFill ?>" style="width:<?= e($attendance) ?>%"></div>
    </div>
    <ul class="info-list">
      <li>
        <span class="key">Status</span>
        <span class="val">
          <?php if ($attendance >= 75): ?>
            <span class="badge badge-green">Good Standing</span>
          <?php elseif ($attendance >= 50): ?>
            <span class="badge badge-amber">At Risk</span>
          <?php else: ?>
            <span class="badge badge-red">Critical</span>
          <?php endif; ?>
        </span>
      </li>
      <li><span class="key">Required Minimum</span><span class="val">75%</span></li>
      <li>
        <span class="key">Shortfall</span>
        <span class="val">
          <?php if ($attendance >= 75): ?>
            <span style="color:var(--green)">None ✓</span>
          <?php else: ?>
            <span style="color:var(--red)"><?= 75 - $attendance ?>% below required</span>
          <?php endif; ?>
        </span>
      </li>
    </ul>
  </div>

  <div class="card">
    <div class="card-title">Attendance Guide</div>
    <ul class="info-list">
      <li>
        <span class="key"><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:var(--green);margin-right:6px"></span>75% – 100%</span>
        <span class="badge badge-green">Good</span>
      </li>
      <li>
        <span class="key"><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:var(--amber);margin-right:6px"></span>50% – 74%</span>
        <span class="badge badge-amber">Warning</span>
      </li>
      <li>
        <span class="key"><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:var(--red);margin-right:6px"></span>Below 50%</span>
        <span class="badge badge-red">Critical</span>
      </li>
    </ul>
    <div class="sep"></div>
    <p style="font-size:13px;color:var(--muted)">If your attendance is below 75%, please contact your teacher or department coordinator immediately.</p>
  </div>
</div>

<?php include "../layouts/footer.php"; ?>
