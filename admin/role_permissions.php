<?php
require "../auth/auth_check.php";
requireRole(1);
$pageTitle = "RBAC Matrix";

$msg = $err = "";

// Toggle permission
if (isset($_POST['toggle'])) {
    $rid = intval($_POST['role_id']);
    $mid = intval($_POST['module_id']);
    $pid = intval($_POST['perm_id']);
    $exists = $conn->query("SELECT id FROM role_permissions WHERE role_id=$rid AND module_id=$mid AND permission_id=$pid")->num_rows;
    if ($exists) {
        $conn->query("DELETE FROM role_permissions WHERE role_id=$rid AND module_id=$mid AND permission_id=$pid");
        $msg = "Permission removed.";
    } else {
        $conn->query("INSERT IGNORE INTO role_permissions (role_id,module_id,permission_id) VALUES ($rid,$mid,$pid)");
        $msg = "Permission granted.";
    }
    logAudit($conn, $_SESSION['user_id'], "Toggled permission: role=$rid module=$mid perm=$pid");
}

$roles = $conn->query("SELECT * FROM roles ORDER BY role_id")->fetch_all(MYSQLI_ASSOC);
$modules = $conn->query("SELECT * FROM modules ORDER BY module_id")->fetch_all(MYSQLI_ASSOC);
$perms   = $conn->query("SELECT * FROM permissions ORDER BY permission_id")->fetch_all(MYSQLI_ASSOC);

// Build granted set: [role_id][module_id][permission_id] = true
$granted = [];
$res = $conn->query("SELECT * FROM role_permissions");
while ($row = $res->fetch_assoc()) {
    $granted[$row['role_id']][$row['module_id']][$row['permission_id']] = true;
}

include "../layouts/header.php";
?>

<?php if ($msg): ?>
<div class="alert alert-success"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg><?= e($msg) ?></div>
<?php endif; ?>

<div class="page-header">
  <div><h2>RBAC Matrix</h2><p>Click any cell to grant or revoke a permission</p></div>
</div>

<?php foreach ($roles as $role): ?>
<div class="card" style="padding:0;overflow:hidden;margin-bottom:16px">
  <div style="padding:13px 20px;background:var(--bg3);border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px">
    <?php $badgeMap=[1=>'badge-purple',2=>'badge-blue',3=>'badge-green',4=>'badge-amber']; ?>
    <span class="badge <?= $badgeMap[$role['role_id']] ?? 'badge-blue' ?>"><?= e($role['role_name']) ?></span>
    <span style="font-size:13px;color:var(--muted)"><?= e($role['description'] ?? '') ?></span>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Module</th>
          <?php foreach ($perms as $p): ?>
            <th style="text-align:center"><?= ucfirst(e($p['permission_name'])) ?></th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($modules as $mod): ?>
        <tr>
          <td style="font-weight:500"><?= e($mod['module_name']) ?></td>
          <?php foreach ($perms as $p):
            $has = isset($granted[$role['role_id']][$mod['module_id']][$p['permission_id']]);
          ?>
          <td style="text-align:center">
            <form method="post" style="margin:0;display:inline">
              <input type="hidden" name="role_id"   value="<?= $role['role_id'] ?>">
              <input type="hidden" name="module_id" value="<?= $mod['module_id'] ?>">
              <input type="hidden" name="perm_id"   value="<?= $p['permission_id'] ?>">
              <button name="toggle" type="submit"
                style="width:30px;height:30px;border-radius:6px;border:2px solid <?= $has ? 'var(--green)' : 'var(--border)' ?>;background:<?= $has ? 'rgba(34,211,160,.15)' : 'transparent' ?>;cursor:pointer;font-size:14px;transition:all .15s"
                title="<?= $has ? 'Revoke' : 'Grant' ?> <?= e($p['permission_name']) ?>">
                <?= $has ? '✓' : '' ?>
              </button>
            </form>
          </td>
          <?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endforeach; ?>

<?php include "../layouts/footer.php"; ?>
