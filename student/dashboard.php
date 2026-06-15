<?php
require_once "../auth/auth_check.php";
requireRole(3);
$pageTitle = "My Dashboard";

$student = null;
if (!empty($_SESSION['ag_number'])) {
    $stmt = $conn->prepare("SELECT attendance, marks, grades FROM student_records WHERE ag_number=?");
    $stmt->bind_param("s", $_SESSION['ag_number']);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
}

$attendance = $student['attendance'] ?? 0;
$marks      = $student['marks']      ?? 'N/A';
$grades     = $student['grades']     ?? 'N/A';

// Decode marks JSON if applicable
$marksDecoded = json_decode($marks, true);
$isJson = is_array($marksDecoded);

$attFill  = $attendance >= 75 ? 'good' : ($attendance >= 50 ? 'warn' : 'bad');
$gradeClass = 'grade-A';
if ($grades === 'F') $gradeClass = 'grade-F';
elseif ($grades === 'B') $gradeClass = 'grade-B';
elseif ($grades === 'C') $gradeClass = 'grade-C';

include "../layouts/header.php";
?>

<div style="margin-bottom:24px">
  <h2 style="font-size:22px;font-weight:700">Welcome, <?= e($_SESSION['full_name']) ?> 👋</h2>
  <p style="color:var(--muted);font-size:14px;margin-top:4px">Here's your academic summary</p>
</div>

<div class="stats-grid">
  <div class="stat-card <?= $attendance >= 75 ? 'green' : ($attendance >= 50 ? 'amber' : 'accent') ?>">
    <div class="stat-icon <?= $attendance >= 75 ? 'green' : ($attendance >= 50 ? 'amber' : 'accent') ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="18" rx="2"/><polyline points="9 16 11 18 15 14"/></svg>
    </div>
    <div class="stat-value"><?= e($attendance) ?>%</div>
    <div class="stat-label">Attendance</div>
  </div>
  <div class="stat-card blue">
    <div class="stat-icon blue">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
    </div>
    <div class="stat-value"><?= $isJson ? count($marksDecoded) : ($marks !== 'N/A' ? $marks : '—') ?></div>
    <div class="stat-label"><?= $isJson ? 'Subjects' : 'Total Marks' ?></div>
  </div>
  <div class="stat-card <?= $grades === 'F' ? 'accent' : 'green' ?>">
    <div class="stat-icon <?= $grades === 'F' ? 'accent' : 'green' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11"/></svg>
    </div>
    <div class="stat-value"><?= e($grades) ?></div>
    <div class="stat-label">Current Grade</div>
  </div>
</div>

<div class="grid-2">
  <div class="card">
    <div class="card-title">Attendance Status</div>
    <div style="margin-bottom:12px">
      <div style="display:flex;justify-content:space-between;margin-bottom:8px">
        <span style="font-size:13px;color:var(--muted)">Your Attendance</span>
        <strong style="font-size:13px"><?= e($attendance) ?>%</strong>
      </div>
      <div class="progress-bar" style="height:14px">
        <div class="progress-fill <?= $attFill ?>" style="width:<?= e($attendance) ?>%"></div>
      </div>
    </div>
    <p style="font-size:12px;color:var(--muted)">
      <?php if ($attendance >= 75): ?>✅ Your attendance meets the required threshold.
      <?php elseif ($attendance >= 50): ?>⚠️ Your attendance is below 75%. Please attend more classes.
      <?php else: ?>❌ Critical: Attendance is very low. Contact your teacher immediately.
      <?php endif; ?>
    </p>
  </div>

  <div class="card">
    <div class="card-title">Subject Marks</div>
    <?php if ($isJson && !empty($marksDecoded)): ?>
      <ul class="info-list">
        <?php
          $total = 0; $cnt = count($marksDecoded);
          foreach ($marksDecoded as $subj => $mark):
            $total += intval($mark);
        ?>
          <li>
            <span class="key"><?= e($subj) ?></span>
            <span class="val"><?= e($mark) ?> / 60</span>
          </li>
        <?php endforeach; ?>
        <li style="border-top:1px solid var(--border);padding-top:12px">
          <span class="key" style="font-weight:600;color:var(--text)">Average</span>
          <span class="val"><?= round($total/$cnt, 1) ?></span>
        </li>
      </ul>
    <?php elseif ($marks !== 'N/A'): ?>
      <p style="font-size:24px;font-weight:700;color:var(--text)"><?= e($marks) ?> <span style="font-size:14px;color:var(--muted)">/ 60</span></p>
    <?php else: ?>
      <div class="empty-state"><p>No marks recorded yet.</p></div>
    <?php endif; ?>
  </div>
</div>

<?php include "../layouts/footer.php"; ?>
