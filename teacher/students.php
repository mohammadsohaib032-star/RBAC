<?php
require "../auth/auth_check.php";
requireRole(2);
$pageTitle = "My Students";

$msg = $err = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id'])) {
    $studentId  = intval($_POST['student_id']);
    $attendance = min(100, max(0, intval($_POST['attendance'])));

    // Build marks array from parallel subject/mark inputs
    $subjects = $_POST['subjects'] ?? [];
    $markVals = $_POST['mark_vals'] ?? [];
    $marksArr = [];
    for ($i = 0; $i < count($subjects); $i++) {
        $subj = trim($subjects[$i]);
        $mark = isset($markVals[$i]) ? min(60, max(0, intval($markVals[$i]))) : 0;
        if ($subj !== '') {
            $marksArr[$subj] = $mark;
        }
    }
    $marksJson = !empty($marksArr) ? json_encode($marksArr) : null;

    // Auto-calculate grade
    if (!empty($marksArr)) {
        $avgScore = array_sum($marksArr) / count($marksArr);
        $percent  = $avgScore / 60 * 100;
        if      ($percent >= 90) $grade = 'A+';
        elseif  ($percent >= 80) $grade = 'A';
        elseif  ($percent >= 70) $grade = 'B';
        elseif  ($percent >= 60) $grade = 'C';
        else                    $grade = 'F';
    } else {
        $grade = null;
    }

    $stmt = $conn->prepare("UPDATE student_records SET attendance=?,marks=?,grades=? WHERE student_id=? AND teacher_id=?");
    $stmt->bind_param("issii", $attendance, $marksJson, $grade, $studentId, $_SESSION['user_id']);
    if ($stmt->execute() && $stmt->affected_rows >= 0) {
        logAudit($conn, $_SESSION['user_id'], "Updated records for student_id=$studentId");
        $msg = "Record saved. Grade auto-calculated: " . ($grade ?? '—');
    } else {
        $err = "Save failed. " . $conn->error;
    }
}

$students = $conn->query("
    SELECT u.user_id, u.full_name, u.ag_number,
           s.student_id, s.attendance, s.marks, s.grades
    FROM users u
    JOIN student_records s ON u.ag_number = s.ag_number
    WHERE u.role_id = 3 AND s.teacher_id = {$_SESSION['user_id']}
    ORDER BY u.full_name
");

include "../layouts/header.php";
?>

<?php if ($msg): ?><div class="alert alert-success"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg><?= e($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg><?= e($err) ?></div><?php endif; ?>

<div class="page-header">
  <div><h2>My Students</h2><p>Update attendance and subject marks — grade is auto-calculated</p></div>
</div>

<?php if ($students && $students->num_rows): ?>
<div style="display:flex;flex-direction:column;gap:20px">
  <?php while ($s = $students->fetch_assoc()):
    $marks = json_decode($s['marks'] ?? '{}', true);
    if (!is_array($marks)) $marks = [];
    $gradeClass = match($s['grades']) { 'F'=>'grade-F','B'=>'grade-B','C'=>'grade-C', default=>'grade-A' };
    $attFill = $s['attendance'] >= 75 ? 'good' : ($s['attendance'] >= 50 ? 'warn' : 'bad');
  ?>
  <div class="card">
    <!-- Student Header -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
      <div style="display:flex;align-items:center;gap:12px">
        <div style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--green));display:flex;align-items:center;justify-content:center;font-weight:700;font-size:18px;color:#fff">
          <?= strtoupper(substr($s['full_name'],0,1)) ?>
        </div>
        <div>
          <div style="font-weight:600;font-size:16px"><?= e($s['full_name']) ?></div>
          <div class="mono" style="font-size:12px;color:var(--muted)"><?= e($s['ag_number'] ?? '—') ?></div>
        </div>
      </div>
      <div style="text-align:right">
        <div style="font-size:11px;color:var(--muted);margin-bottom:4px">Current Grade</div>
        <span class="grade-badge <?= $gradeClass ?>" style="font-size:18px;width:44px;height:44px;border-radius:10px">
          <?= e($s['grades'] ?: '—') ?>
        </span>
      </div>
    </div>

    <form method="post">
      <input type="hidden" name="student_id" value="<?= $s['student_id'] ?>">

      <div class="grid-2">
        <!-- Attendance -->
        <div>
          <div class="form-group">
            <label>Attendance (%)</label>
            <input type="number" name="attendance"
                   value="<?= e($s['attendance']) ?>"
                   min="0" max="100"
                   oninput="updateBar(this, 'bar-<?= $s['student_id'] ?>')">
          </div>
          <div class="progress-bar" style="height:10px;margin-top:-6px;margin-bottom:12px">
            <div id="bar-<?= $s['student_id'] ?>" class="progress-fill <?= $attFill ?>" style="width:<?= e($s['attendance']) ?>%"></div>
          </div>
          <p style="font-size:12px;color:var(--muted)">
            <?php if ($s['attendance'] >= 75): ?>✅ Satisfactory
            <?php elseif ($s['attendance'] >= 50): ?>⚠️ Below threshold
            <?php else: ?>❌ Critical — below 50%<?php endif; ?>
          </p>
        </div>

        <!-- Marks -->
        <div>
          <label style="display:block;font-size:12px;font-weight:600;color:var(--muted);margin-bottom:8px;letter-spacing:.05em;text-transform:uppercase">
            Subject Marks <span style="color:var(--accent2);font-weight:400">(each out of 60, auto-grades on save)</span>
          </label>
          <div id="marks-container-<?= $s['student_id'] ?>" style="display:flex;flex-direction:column;gap:8px">
            <?php if (!empty($marks)):
              foreach ($marks as $subj => $mark): ?>
              <div style="display:flex;gap:8px;align-items:center">
                <input type="text"   name="subjects[]"   value="<?= e($subj) ?>" placeholder="Subject" style="flex:1">
                <input type="number" name="mark_vals[]"  value="<?= e($mark) ?>" min="0" max="60" style="width:80px">
                <button type="button" onclick="this.parentElement.remove()" style="background:transparent;border:none;color:var(--red);cursor:pointer;font-size:16px;padding:0 4px">×</button>
              </div>
            <?php endforeach; else: ?>
              <div style="display:flex;gap:8px;align-items:center">
                <input type="text"   name="subjects[]"  placeholder="Subject name" style="flex:1">
                <input type="number" name="mark_vals[]" placeholder="0" min="0" max="60" style="width:80px">
                <button type="button" onclick="this.parentElement.remove()" style="background:transparent;border:none;color:var(--red);cursor:pointer;font-size:16px;padding:0 4px">×</button>
              </div>
            <?php endif; ?>
          </div>
          <button type="button" class="btn btn-secondary btn-sm" style="margin-top:8px"
                  onclick="addRow('marks-container-<?= $s['student_id'] ?>')">
            + Add Subject
          </button>
        </div>
      </div>

      <div style="display:flex;justify-content:flex-end;margin-top:16px;padding-top:16px;border-top:1px solid var(--border)">
        <button type="submit" class="btn btn-success">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
          Save &amp; Auto-Grade
        </button>
      </div>
    </form>
  </div>
  <?php endwhile; ?>
</div>
<?php else: ?>
<div class="card">
  <div class="empty-state">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
    <p>No students are assigned to you yet. Ask the admin to assign students.</p>
  </div>
</div>
<?php endif; ?>

<script>
function updateBar(input, barId) {
  const val = Math.min(100, Math.max(0, parseInt(input.value) || 0));
  const bar = document.getElementById(barId);
  bar.style.width = val + '%';
  bar.className = 'progress-fill ' + (val >= 75 ? 'good' : val >= 50 ? 'warn' : 'bad');
}
function addRow(containerId) {
  const c = document.getElementById(containerId);
  const div = document.createElement('div');
  div.style.cssText = 'display:flex;gap:8px;align-items:center';
  div.innerHTML = `<input type="text" name="subjects[]" placeholder="Subject name" style="flex:1">
                   <input type="number" name="mark_vals[]" placeholder="0" min="0" max="60" style="width:80px">
                   <button type="button" onclick="this.parentElement.remove()" style="background:transparent;border:none;color:var(--red);cursor:pointer;font-size:16px;padding:0 4px">×</button>`;
  c.appendChild(div);
}
</script>

<?php include "../layouts/footer.php"; ?>
