<?php
require "../auth/auth_check.php";
requireRole(1);
$pageTitle = "Roles";

$roles = $conn->query("SELECT r.*, COUNT(u.user_id) as user_count FROM roles r LEFT JOIN users u ON r.role_id=u.role_id GROUP BY r.role_id");

include "../layouts/header.php";
?>

<div class="page-header">
  <div><h2>System Roles</h2><p>All roles defined in the portal</p></div>
</div>

<div class="card" style="padding:0;overflow:hidden">
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>#</th><th>Role Name</th><th>Description</th><th>Users</th></tr>
      </thead>
      <tbody>
        <?php
        $colors = [1=>'badge-purple',2=>'badge-blue',3=>'badge-green',4=>'badge-amber'];
        while ($r = $roles->fetch_assoc()):
          $bc = $colors[$r['role_id']] ?? 'badge-blue';
        ?>
        <tr>
          <td class="mono" style="color:var(--muted)"><?= $r['role_id'] ?></td>
          <td><span class="badge <?= $bc ?>"><?= e($r['role_name']) ?></span></td>
          <td style="color:var(--muted)"><?= e($r['description'] ?? '—') ?></td>
          <td><strong><?= $r['user_count'] ?></strong></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include "../layouts/footer.php"; ?>
