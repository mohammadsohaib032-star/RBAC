<?php
require "../auth/auth_check.php";
requireRole(2);
$pageTitle = "Marks Overview";

// This page shows a read-only overview of all students + links to edit
$students = $conn->query("
    SELECT u.full_name, u.ag_number, s.student_id, s.attendance, s.marks, s.grades, s.teacher_id
    FROM users u
    JOIN student_records s ON u.ag_number = s.ag_number
    WHERE u.role_id = 3
    ORDER BY u.full_name
");

include "../layouts/header.php";
?>

<div class="page-header">
  <div><h2>All Students — Marks Overview</h2><p>Read-only summary. Edit from <a href="students.php">My Students</a>.</p></div>
</div>

<div class="card" style="padding:0;overflow:hidden">
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Student</th>
          <th>AG Number</th>
          <th>Attendance</th>
          <th>Subjects</th>
          <th>Grade</th>
          <th>Your Student</th>
        </tr>
      </thead>
      <tbody>
        <?php $i=1; while ($s = $students->fetch_assoc()):
          $marks = json_decode($s['marks'] ?? '{}', true);
          if (!is_array($marks)) $marks = [];
          $avg = count($marks) ? round(array_sum($marks)/count($marks),1) : null;
          $gradeClass = match($s['grades']) {'F'=>'grade-F','B'=>'grade-B','C'=>'grade-C',default=>'grade-A'};
          $attFill = $s['attendance'] >= 75 ? 'good' : ($s['attendance'] >= 50 ? 'warn' : 'bad');
          $isMyStudent = ($s['teacher_id'] == $_SESSION['user_id']);
        ?>
        <tr>
          <td class="mono" style="color:var(--muted)"><?= $i++ ?></td>
          <td style="font-weight:500"><?= e($s['full_name']) ?></td>
          <td class="mono" style="color:var(--muted)"><?= e($s['ag_number']) ?></td>
          <td>
            <div style="display:flex;align-items:center;gap:8px">
              <div class="progress-bar" style="width:70px;height:6px">
                <div class="progress-fill <?= $attFill ?>" style="width:<?= e($s['attendance']) ?>%"></div>
              </div>
              <span style="font-size:13px"><?= e($s['attendance']) ?>%</span>
            </div>
          </td>
          <td>
            <?php if (!empty($marks)): ?>
              <?php foreach ($marks as $subj => $mark): ?>
                <span class="badge badge-blue" style="margin:1px;font-size:10px"><?= e($subj) ?>: <?= e($mark) ?></span>
              <?php endforeach; ?>
              <?php if ($avg !== null): ?>
                <div style="font-size:11px;color:var(--muted);margin-top:4px">Avg: <?= $avg ?></div>
              <?php endif; ?>
            <?php else: ?>
              <span style="color:var(--muted);font-size:13px">No marks</span>
            <?php endif; ?>
          </td>
          <td>
            <?php if ($s['grades']): ?>
              <span class="grade-badge <?= $gradeClass ?>"><?= e($s['grades']) ?></span>
            <?php else: ?>
              <span style="color:var(--muted)">—</span>
            <?php endif; ?>
          </td>
          <td>
            <?php if ($isMyStudent): ?>
              <span class="badge badge-green">Yes</span>
            <?php else: ?>
              <span class="badge badge-amber">Other</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include "../layouts/footer.php"; ?>
