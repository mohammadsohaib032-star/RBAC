<?php
require "../auth/auth_check.php";
requireRole(4);
$pageTitle = "Lecture Schedule";

$msg = $err = "";

if (isset($_POST['add_lecture'])) {
    $class_name       = $conn->real_escape_string(trim($_POST['class_name']));
    $lecture_time     = $conn->real_escape_string(trim($_POST['lecture_time']));
    $lecture_location = $conn->real_escape_string(trim($_POST['lecture_location']));
    $teacher_id       = intval($_POST['teacher_id']);

    if ($conn->query("INSERT INTO lecture_schedule (class_name,lecture_time,lecture_location,faculty_id) VALUES ('$class_name','$lecture_time','$lecture_location',$teacher_id)")) {
        logAudit($conn, $_SESSION['user_id'], "Added lecture: $class_name");
        $msg = "Lecture '$class_name' added.";
    } else {
        $err = "Failed to add lecture: " . $conn->error;
    }
}

if (isset($_POST['delete_lecture'])) {
    $lid = intval($_POST['lid']);
    $conn->query("DELETE FROM lecture_schedule WHERE id=$lid");
    logAudit($conn, $_SESSION['user_id'], "Deleted lecture id=$lid");
    $msg = "Lecture deleted.";
}

$schedules = $conn->query("
    SELECT ls.*, u.full_name AS teacher_name
    FROM lecture_schedule ls
    LEFT JOIN users u ON ls.faculty_id = u.user_id
    ORDER BY ls.id DESC
");

$teachers = $conn->query("SELECT user_id, full_name FROM users WHERE role_id=2 ORDER BY full_name");

include "../layouts/header.php";
?>

<?php if ($msg): ?><div class="alert alert-success"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg><?= e($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg><?= e($err) ?></div><?php endif; ?>

<div class="page-header">
  <div><h2>Lecture Timetable</h2><p>Manage class schedule and assignments</p></div>
  <button class="btn btn-primary" onclick="document.getElementById('addModal').classList.add('open')">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Add Lecture
  </button>
</div>

<?php if ($schedules && $schedules->num_rows): ?>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px">
    <?php while ($l = $schedules->fetch_assoc()): ?>
      <div class="card card-sm" style="border-left:3px solid var(--accent)">
        <div style="display:flex;justify-content:space-between;align-items:flex-start">
          <div>
            <div style="font-size:15px;font-weight:600;color:var(--text);margin-bottom:8px"><?= e($l['class_name']) ?></div>
            <div style="font-size:13px;color:var(--muted);display:flex;flex-direction:column;gap:4px">
              <span>🕐 <?= e($l['lecture_time']) ?></span>
              <span>📍 <?= e($l['lecture_location']) ?></span>
              <span>👨‍🏫 <?= e($l['teacher_name'] ?? 'Unassigned') ?></span>
            </div>
          </div>
          <form method="post" onsubmit="return confirm('Delete this lecture?')" style="margin:0">
            <input type="hidden" name="lid" value="<?= $l['id'] ?>">
            <button name="delete_lecture" class="btn btn-danger btn-sm">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
            </button>
          </form>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
<?php else: ?>
  <div class="card"><div class="empty-state">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
    <p>No lectures scheduled yet. Add one to get started.</p>
  </div></div>
<?php endif; ?>

<!-- ADD MODAL -->
<div id="addModal" class="modal-overlay">
  <div class="modal">
    <div class="modal-title">Add New Lecture</div>
    <form method="post">
      <div class="form-group">
        <label>Class Name</label>
        <input type="text" name="class_name" placeholder="e.g. Data Structures" required>
      </div>
      <div class="form-group">
        <label>Lecture Time</label>
        <input type="text" name="lecture_time" placeholder="e.g. Mon 10:00–12:00" required>
      </div>
      <div class="form-group">
        <label>Location / Room</label>
        <input type="text" name="lecture_location" placeholder="e.g. Room 301" required>
      </div>
      <div class="form-group">
        <label>Assign Teacher</label>
        <select name="teacher_id">
          <?php while ($t = $teachers->fetch_assoc()): ?>
            <option value="<?= $t['user_id'] ?>"><?= e($t['full_name']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn btn-secondary" onclick="document.getElementById('addModal').classList.remove('open')">Cancel</button>
        <button type="submit" name="add_lecture" class="btn btn-primary">Add Lecture</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('open'));
});
</script>

<?php include "../layouts/footer.php"; ?>
