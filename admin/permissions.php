<?php
require "../auth/auth_check.php";
requireRole(1);
$pageTitle = "Permissions";

$perms = $conn->query("SELECT * FROM permissions ORDER BY permission_id");
include "../layouts/header.php";
?>

<div class="page-header">
  <div><h2>Permissions</h2><p>All permissions registered in the system</p></div>
</div>

<div class="card" style="padding:0;overflow:hidden">
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>#</th><th>Permission Name</th><th>Description</th></tr>
      </thead>
      <tbody>
        <?php while ($p = $perms->fetch_assoc()): ?>
        <tr>
          <td class="mono" style="color:var(--muted)"><?= $p['permission_id'] ?></td>
          <td><span class="badge badge-purple"><?= e($p['permission_name']) ?></span></td>
          <td style="color:var(--muted)"><?= e($p['description'] ?? '—') ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include "../layouts/footer.php"; ?>
