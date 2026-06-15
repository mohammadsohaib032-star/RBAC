<?php
require "../auth/auth_check.php";
requireRole(3);
$pageTitle = "My Grades";

$student = null;
if (!empty($_SESSION['ag_number'])) {
    $stmt = $conn->prepare("SELECT attendance, marks, grades FROM student_records WHERE ag_number=?");
    $stmt->bind_param("s", $_SESSION['ag_number']);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
}

$marks  = $student['marks']  ?? null;
$grades = $student['grades'] ?? null;

$marksDecoded = json_decode($marks, true);
$isJson = is_array($marksDecoded);

$gradeClass = 'grade-A';
if ($grades === 'F') $gradeClass = 'grade-F';
elseif ($grades === 'B') $gradeClass = 'grade-B';
elseif ($grades === 'C') $gradeClass = 'grade-C';

include "../layouts/header.php";
?>

<div class="page-header">
  <div><h2>My Grades</h2><p>Your academic marks and grade summary</p></div>
</div>

<div class="grid-2">
  <div class="card">
    <div class="card-title">Overall Grade</div>
    <div style="text-align:center;padding:24px 0">
      <?php if ($grades): ?>
        <div class="grade-badge <?= $gradeClass ?>" style="width:80px;height:80px;border-radius:16px;font-size:36px;margin:0 auto 16px">
          <?= e($grades) ?>
        </div>
        <p style="color:var(--muted);font-size:14px">
          <?php
            $gradeDesc = ['A+'=>'Outstanding','A'=>'Excellent','B'=>'Good','C'=>'Average','F'=>'Fail'];
            echo $gradeDesc[$grades] ?? 'N/A';
          ?>
        </p>
      <?php else: ?>
        <div class="empty-state"><p>No grade assigned yet.</p></div>
      <?php endif; ?>
    </div>
  </div>

  <div class="card">
    <div class="card-title">Subject-wise Marks</div>
    <?php if ($isJson && !empty($marksDecoded)): ?>
      <?php
        $total = 0; $cnt = count($marksDecoded);
        foreach ($marksDecoded as $m) $total += intval($m);
        $avg = round($total / $cnt, 1);
      ?>
      <ul class="info-list">
        <?php foreach ($marksDecoded as $subj => $mark):
          $pct = intval($mark) / 60 * 100;
          $fill = $pct >= 75 ? 'good' : ($pct >= 50 ? 'warn' : 'bad');
        ?>
          <li style="flex-direction:column;align-items:flex-start;gap:6px">
            <div style="display:flex;width:100%;justify-content:space-between">
              <span class="key"><?= e($subj) ?></span>
              <span class="val"><?= e($mark) ?> / 60</span>
            </div>
            <div class="progress-bar" style="width:100%">
              <div class="progress-fill <?= $fill ?>" style="width:<?= min(100, max(0, intval($mark) / 60 * 100)) ?>%"></div>
            </div>
          </li>
        <?php endforeach; ?>
        <li>
          <span class="key" style="font-weight:600;color:var(--text)">Average</span>
          <span class="val"><?= $avg ?></span>
        </li>
      </ul>
    <?php elseif ($marks && $marks !== 'null'): ?>
      <ul class="info-list">
        <li><span class="key">Total Marks</span><span class="val"><?= e($marks) ?></span></li>
      </ul>
    <?php else: ?>
      <div class="empty-state"><p>No marks recorded yet.</p></div>
    <?php endif; ?>
  </div>
</div>

<div class="card" style="margin-top:0">
  <div class="card-title">Grade Scale</div>
  <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:12px;text-align:center">
    <?php
      $scale = ['A+'=>['90–100','grade-A'],'A'=>['80–89','grade-A'],'B'=>['70–79','grade-B'],'C'=>['60–69','grade-C'],'F'=>['Below 60','grade-F']];
      foreach ($scale as $g => [$range, $cls]):
    ?>
      <div style="background:var(--bg3);border:1px solid var(--border);border-radius:10px;padding:14px 8px">
        <div class="grade-badge <?= $cls ?>" style="margin:0 auto 8px"><?= $g ?></div>
        <div style="font-size:11px;color:var(--muted)"><?= $range ?></div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php include "../layouts/footer.php"; ?>
