<?php
require "../auth/auth_check.php";
requireRole(1);

$pageTitle = "User Management";
$msg = $err = "";

// ── ADD ──────────────────────────────────────────────────────
if (isset($_POST['add_user'])) {
    $full_name  = trim($_POST['full_name']);
    $username   = trim($_POST['username']);
    $password   = $_POST['password'];
    $role_id    = intval($_POST['role_id']);
    $status     = $_POST['status'] === 'active' ? 'active' : 'inactive';
    $ag_number  = trim($_POST['ag_number']) ?: null;
    $teacher_id = intval($_POST['teacher_id'] ?? 0);

    if ($full_name==='' || $username==='' || $password==='') {
        $err = "Full name, username and password are required.";
    } else {
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare(
            "INSERT INTO users (username,password,full_name,ag_number,role_id,status) VALUES (?,?,?,?,?,?)"
        );
        $stmt->bind_param("ssssis", $username, $hashed, $full_name, $ag_number, $role_id, $status);
        if ($stmt->execute()) {
            $newId = $conn->insert_id;
            // Auto-create student_records for students
            if ($role_id == 3 && $ag_number) {
                $t = $conn->query("SELECT user_id FROM users WHERE role_id=2 ORDER BY user_id LIMIT 1")->fetch_assoc();
                $tid = $t ? $t['user_id'] : $newId;
                $sr = $conn->prepare("INSERT IGNORE INTO student_records (ag_number,teacher_id) VALUES (?,?)");
                $sr->bind_param("si", $ag_number, $tid);
                $sr->execute();
            }
            logAudit($conn, $_SESSION['user_id'], "Added user: $username (role_id=$role_id)");
            $msg = "User '$username' added successfully.";
        } else {
            $err = "Username or AG Number already exists.";
        }
    }
}

// ── DELETE ───────────────────────────────────────────────────
if (isset($_POST['delete'])) {
    $id = intval($_POST['id']);
    if ($id === intval($_SESSION['user_id'])) {
        $err = "You cannot delete your own account.";
    } else {
        $row = $conn->query("SELECT username FROM users WHERE user_id=$id")->fetch_assoc();
        $uname = $row['username'] ?? '';
        $conn->query("DELETE FROM users WHERE user_id=$id");
        logAudit($conn, $_SESSION['user_id'], "Deleted user: $uname");
        $msg = "User '$uname' deleted.";
    }
}

// ── UPDATE ───────────────────────────────────────────────────
if (isset($_POST['update'])) {
    $id        = intval($_POST['id']);
    $stmt      = $conn->prepare("SELECT username, ag_number, role_id FROM users WHERE user_id=?");
    $stmt->bind_param("i", $id); $stmt->execute();
    $old       = $stmt->get_result()->fetch_assoc();

    $username   = trim($_POST['username']);
    $full_name  = trim($_POST['full_name']);
    $role_id    = intval($_POST['role_id']);
    $status     = $_POST['status'] === 'active' ? 'active' : 'inactive';
    $ag_number  = trim($_POST['ag_number']) ?: null;
    $new_pass   = $_POST['new_password'] ?? '';
    $teacher_id = intval($_POST['teacher_id'] ?? 0);

    if ($new_pass !== '') {
        $hashed = password_hash($new_pass, PASSWORD_BCRYPT);
        $stmt2  = $conn->prepare("UPDATE users SET username=?,full_name=?,ag_number=?,role_id=?,status=?,password=? WHERE user_id=?");
        $stmt2->bind_param("sssisis", $username, $full_name, $ag_number, $role_id, $status, $hashed, $id);
    } else {
        $stmt2 = $conn->prepare("UPDATE users SET username=?,full_name=?,ag_number=?,role_id=?,status=? WHERE user_id=?");
        $stmt2->bind_param("sssisi", $username, $full_name, $ag_number, $role_id, $status, $id);
    }

    if ($stmt2->execute()) {
        // If now a student with ag_number, ensure student_records row exists and teacher assignment is saved
        if ($role_id == 3 && $ag_number) {
            if ($teacher_id <= 0) {
                $t = $conn->query("SELECT user_id FROM users WHERE role_id=2 ORDER BY user_id LIMIT 1")->fetch_assoc();
                $teacher_id = $t ? $t['user_id'] : $id;
            }
            $sr = $conn->prepare("INSERT INTO student_records (ag_number,teacher_id) VALUES (?,?) ON DUPLICATE KEY UPDATE teacher_id=VALUES(teacher_id)");
            $sr->bind_param("si", $ag_number, $teacher_id);
            $sr->execute();
        }
        logAudit($conn, $_SESSION['user_id'], "Updated user: {$old['username']} → $username");
        $msg = "User updated successfully.";
    } else {
        $err = "Update failed: " . $conn->error;
    }
}

$users = $conn->query("SELECT u.*,r.role_name,s.teacher_id FROM users u JOIN roles r ON u.role_id=r.role_id LEFT JOIN student_records s ON u.ag_number=s.ag_number ORDER BY u.role_id, u.full_name");
$roles = $conn->query("SELECT * FROM roles ORDER BY role_id");

$teachers = [];
$teacherQuery = $conn->query("SELECT user_id, full_name FROM users WHERE role_id=2 ORDER BY full_name");
while ($t = $teacherQuery->fetch_assoc()) {
    $teachers[] = $t;
}

include "../layouts/header.php";
?>

<?php if ($msg): ?>
<div class="alert alert-success">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg>
  <?= e($msg) ?>
</div>
<?php endif; ?>
<?php if ($err): ?>
<div class="alert alert-danger">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
  <?= e($err) ?>
</div>
<?php endif; ?>

<div class="page-header">
  <div>
    <h2>User Management</h2>
    <p>Add, edit, or remove system users and assign roles</p>
  </div>
  <button class="btn btn-primary" onclick="document.getElementById('addModal').classList.add('open')">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Add User
  </button>
</div>

<div class="card" style="padding:0;overflow:hidden">
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Full Name</th>
          <th>Username</th>
          <th>AG Number</th>
          <th>Role</th>
          <th>Status</th>
          <th>Joined</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php $i=1; while ($u = $users->fetch_assoc()):
          $badgeMap = [1=>'badge-purple',2=>'badge-blue',3=>'badge-green',4=>'badge-amber'];
          $bc = $badgeMap[$u['role_id']] ?? 'badge-blue';
          $isSelf = ($u['user_id'] == $_SESSION['user_id']);
        ?>
        <tr>
          <td class="mono" style="color:var(--muted)"><?= $i++ ?></td>
          <td>
            <div style="display:flex;align-items:center;gap:8px">
              <div style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--green));display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;flex-shrink:0">
                <?= strtoupper(substr($u['full_name'],0,1)) ?>
              </div>
              <span style="font-weight:500"><?= e($u['full_name']) ?></span>
              <?php if ($isSelf): ?><span class="badge badge-blue" style="font-size:9px">You</span><?php endif; ?>
            </div>
          </td>
          <td class="mono"><?= e($u['username']) ?></td>
          <td class="mono" style="color:var(--muted)"><?= $u['ag_number'] ? e($u['ag_number']) : '—' ?></td>
          <td><span class="badge <?= $bc ?>"><?= e($u['role_name']) ?></span></td>
          <td>
            <?php if ($u['status']==='active'): ?>
              <span class="badge badge-green">Active</span>
            <?php else: ?>
              <span class="badge badge-red">Inactive</span>
            <?php endif; ?>
          </td>
          <td style="color:var(--muted);font-size:12px"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
          <td>
            <div style="display:flex;gap:6px">
              <button class="btn btn-secondary btn-sm" onclick="openEdit(<?= htmlspecialchars(json_encode($u), ENT_QUOTES) ?>)">Edit</button>
              <?php if (!$isSelf): ?>
              <form method="post" onsubmit="return confirm('Delete user <?= e($u['username']) ?>?')" style="margin:0">
                <input type="hidden" name="id" value="<?= $u['user_id'] ?>">
                <button name="delete" class="btn btn-danger btn-sm">Delete</button>
              </form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ADD MODAL -->
<div id="addModal" class="modal-overlay">
  <div class="modal" style="max-width:500px">
    <div class="modal-title">Add New User</div>
    <form method="post" autocomplete="off">
      <div class="grid-2">
        <div class="form-group"><label>Full Name</label><input type="text" name="full_name" placeholder="Full name" required></div>
        <div class="form-group"><label>Username</label><input type="text" name="username" placeholder="Username" required autocomplete="new-password"></div>
      </div>
      <div class="grid-2">
        <div class="form-group"><label>Password</label><input type="password" name="password" placeholder="Password" required autocomplete="new-password"></div>
        <div class="form-group" id="add_ag_row" style="display:none"><label>AG Number <span style="color:var(--muted);font-weight:400">(students only)</span></label><input type="text" name="ag_number" placeholder="e.g. AG-1004"></div>
      </div>
      <div class="grid-2">
        <div class="form-group" id="add_teacher_row" style="display:none">
          <label>Assign Teacher <span style="color:var(--muted);font-weight:400">(students only)</span></label>
          <select name="teacher_id">
            <?php foreach ($teachers as $t): ?>
              <option value="<?= $t['user_id'] ?>"><?= e($t['full_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Role</label>
          <select name="role_id" id="add_role_id" onchange="toggleStudentFields(this,'add_ag_row','add_teacher_row')">
            <?php $roles->data_seek(0); while($r=$roles->fetch_assoc()): ?>
              <option value="<?= $r['role_id'] ?>"><?= e($r['role_name']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
      </div>
      <div class="grid-2">
        <div class="form-group">
          <label>Status</label>
          <select name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select>
        </div>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn btn-secondary" onclick="document.getElementById('addModal').classList.remove('open')">Cancel</button>
        <button type="submit" name="add_user" class="btn btn-primary">Add User</button>
      </div>
    </form>
  </div>
</div>

<!-- EDIT MODAL -->
<div id="editModal" class="modal-overlay">
  <div class="modal" style="max-width:500px">
    <div class="modal-title">Edit User</div>
    <form method="post" autocomplete="off">
      <input type="hidden" name="id" id="edit_id">
      <div class="grid-2">
        <div class="form-group"><label>Full Name</label><input type="text" name="full_name" id="edit_full_name" required></div>
        <div class="form-group"><label>Username</label><input type="text" name="username" id="edit_username" required></div>
      </div>
      <div class="grid-2">
        <div class="form-group"><label>New Password <span style="color:var(--muted);font-weight:400">(leave blank to keep)</span></label><input type="password" name="new_password" placeholder="New password" autocomplete="new-password"></div>
        <div class="form-group" id="edit_ag_row" style="display:none"><label>AG Number</label><input type="text" name="ag_number" id="edit_ag_number" placeholder="e.g. AG-1001"></div>
      </div>
      <div class="grid-2">
        <div class="form-group" id="edit_teacher_row" style="display:none">
          <label>Assign Teacher <span style="color:var(--muted);font-weight:400">(students only)</span></label>
          <select name="teacher_id" id="edit_teacher_id">
            <?php foreach ($teachers as $t): ?>
              <option value="<?= $t['user_id'] ?>"><?= e($t['full_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Role</label>
          <select name="role_id" id="edit_role_id" onchange="toggleStudentFields(this,'edit_ag_row','edit_teacher_row')">
            <?php $roles->data_seek(0); while($r=$roles->fetch_assoc()): ?>
              <option value="<?= $r['role_id'] ?>"><?= e($r['role_name']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Status</label>
          <select name="status" id="edit_status">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn btn-secondary" onclick="document.getElementById('editModal').classList.remove('open')">Cancel</button>
        <button type="submit" name="update" class="btn btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
function toggleStudentFields(select, agRowId, teacherRowId) {
  const show = parseInt(select.value, 10) === 3;
  const agRow = document.getElementById(agRowId);
  const teacherRow = document.getElementById(teacherRowId);
  if (agRow) agRow.style.display = show ? '' : 'none';
  if (teacherRow) teacherRow.style.display = show ? '' : 'none';
}

function openEdit(u) {
  document.getElementById('edit_id').value         = u.user_id;
  document.getElementById('edit_full_name').value  = u.full_name;
  document.getElementById('edit_username').value   = u.username;
  document.getElementById('edit_role_id').value    = u.role_id;
  document.getElementById('edit_status').value     = u.status;
  document.getElementById('edit_ag_number').value  = u.ag_number || '';
  const teacherSelect = document.getElementById('edit_teacher_id');
  teacherSelect.value = u.teacher_id || teacherSelect.options[0]?.value || '';
  toggleStudentFields(document.getElementById('edit_role_id'), 'edit_ag_row', 'edit_teacher_row');
  document.getElementById('editModal').classList.add('open');
}
document.addEventListener('keydown', ev => {
  if (ev.key === 'Escape') document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('open'));
});

const addRole = document.getElementById('add_role_id');
if (addRole) toggleStudentFields(addRole, 'add_ag_row', 'add_teacher_row');
</script>
<?php include "../layouts/footer.php"; ?>
