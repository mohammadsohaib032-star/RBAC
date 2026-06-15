<?php
require "../auth/auth_check.php";
requireRole(1);
$pageTitle = "Audit Logs";

// Search
$search  = trim($_GET['search'] ?? '');
$limit   = 100;
$where   = $search ? "WHERE al.action LIKE '%".($conn->real_escape_string($search))."%' OR u.full_name LIKE '%".($conn->real_escape_string($search))."%'" : "";
$total   = $conn->query("SELECT COUNT(*) c FROM audit_logs al LEFT JOIN users u ON al.user_id=u.user_id $where")->fetch_assoc()['c'];
$logs    = $conn->query("SELECT al.*, u.full_name FROM audit_logs al LEFT JOIN users u ON al.user_id=u.user_id $where ORDER BY al.audit_id DESC LIMIT $limit");

include "../layouts/header.php";
?>

<div class="page-header">
  <div><h2>Audit Logs</h2><p><?= $total ?> record(s) found<?= $search ? " for \"".e($search)."\"" : "" ?></p></div>
  <form method="get" style="display:flex;gap:8px;align-items:center">
    <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search action or user…" style="width:220px">
    <button type="submit" class="btn btn-secondary btn-sm">Search</button>
    <?php if ($search): ?><a href="audit_logs.php" class="btn btn-secondary btn-sm">Clear</a><?php endif; ?>
  </form>
</div>

<div class="card" style="padding:0;overflow:hidden">
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>#</th><th>User</th><th>Action</th><th>Timestamp</th></tr>
      </thead>
      <tbody>
        <?php if ($logs && $logs->num_rows):
          $i = 1;
          while ($l = $logs->fetch_assoc()): ?>
          <tr>
            <td class="mono" style="color:var(--muted)"><?= $i++ ?></td>
            <td style="font-weight:500"><?= e($l['full_name'] ?? 'System') ?></td>
            <td><?= e($l['action']) ?></td>
            <td class="mono" style="color:var(--muted);font-size:12px;white-space:nowrap"><?= e($l['created_at']) ?></td>
          </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="4"><div class="empty-state"><p>No logs found.</p></div></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php if ($total > $limit): ?>
<p style="font-size:12px;color:var(--muted);margin-top:10px;text-align:right">Showing latest <?= $limit ?> of <?= $total ?> logs.</p>
<?php endif; ?>

<?php include "../layouts/footer.php"; ?>
