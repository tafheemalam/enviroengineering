<?php
declare(strict_types=1);
require __DIR__ . '/_init.php';
$admin = require_admin('super_admin');

$errors = [];
if (is_post()) {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'create') {
        $name = clean_str($_POST['name'] ?? '', 100);
        $email = strtolower(clean_str($_POST['email'] ?? '', 190));
        $role = ($_POST['role'] ?? '') === 'super_admin' ? 'super_admin' : 'editor';
        $pw = (string) ($_POST['password'] ?? '');
        if (mb_strlen($name) < 2) {
            $errors[] = 'Name is required.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email is required.';
        } elseif (db_value('SELECT COUNT(*) FROM admins WHERE email = ?', [$email])) {
            $errors[] = 'An admin with that email already exists.';
        }
        $errors = array_merge($errors, password_errors($pw, $pw));
        if (!$errors) {
            // new users must choose their own password at first login
            db_exec('INSERT INTO admins (name, email, password_hash, role, must_change_password) VALUES (?,?,?,?,1)',
                [$name, $email, password_hash($pw, PASSWORD_DEFAULT), $role]);
            flash('success', "Admin $email created. They will be asked to change the temporary password on first login.");
            redirect('admins.php');
        }
    } elseif ($id && $id !== (int) $admin['id']) {
        // (the acting super admin can't modify themselves, so at least one super admin always remains)
        $target = db_one('SELECT * FROM admins WHERE id = ?', [$id]);
        if ($target) {
            if ($action === 'toggle') {
                db_exec('UPDATE admins SET is_active = 1 - is_active WHERE id = ?', [$id]);
                flash('success', 'Account ' . ($target['is_active'] ? 'disabled' : 'enabled') . '.');
            } elseif ($action === 'role') {
                $role = $target['role'] === 'super_admin' ? 'editor' : 'super_admin';
                db_exec('UPDATE admins SET role = ? WHERE id = ?', [$role, $id]);
                flash('success', 'Role changed to ' . str_replace('_', ' ', $role) . '.');
            } elseif ($action === 'delete') {
                db_exec('DELETE FROM admins WHERE id = ?', [$id]);
                flash('success', 'Admin deleted.');
            }
        }
        redirect('admins.php');
    } else {
        flash('warning', 'You cannot change your own account here. Use Profile instead.');
        redirect('admins.php');
    }
}

$admins = db_all('SELECT id, name, email, role, is_active, last_login_at, created_at FROM admins ORDER BY created_at');
$pageTitle = 'Admin Users';
$active = 'admins';
require __DIR__ . '/partials/header.php';
?>
<?php foreach ($errors as $err): ?><div class="alert alert-danger py-2"><?= e($err) ?></div><?php endforeach; ?>
<div class="row g-4">
  <div class="col-lg-8">
    <div class="card shadow-sm border-0"><div class="table-responsive"><table class="table align-middle mb-0">
      <thead class="table-light"><tr><th>User</th><th>Role</th><th>Status</th><th>Last login</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
      <?php foreach ($admins as $a): $self = (int) $a['id'] === (int) $admin['id']; ?>
        <tr>
          <td><strong><?= e($a['name']) ?></strong><?= $self ? ' <span class="badge text-bg-light border">You</span>' : '' ?><div class="small text-muted"><?= e($a['email']) ?></div></td>
          <td><?= $a['role'] === 'super_admin' ? '<span class="badge text-bg-dark">Super admin</span>' : '<span class="badge text-bg-secondary">Editor</span>' ?></td>
          <td><?= $a['is_active'] ? '<span class="badge text-bg-success">Active</span>' : '<span class="badge text-bg-danger">Disabled</span>' ?></td>
          <td class="small text-muted"><?= e(format_dt($a['last_login_at'])) ?: 'Never' ?></td>
          <td class="text-end text-nowrap">
            <?php if (!$self): ?>
              <form method="post" class="d-inline"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $a['id'] ?>"><button name="action" value="role" class="btn btn-sm btn-light" title="Toggle role"><i class="bi bi-person-gear"></i></button></form>
              <form method="post" class="d-inline"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $a['id'] ?>"><button name="action" value="toggle" class="btn btn-sm btn-light" title="<?= $a['is_active'] ? 'Disable' : 'Enable' ?>"><i class="bi bi-<?= $a['is_active'] ? 'pause-circle' : 'play-circle' ?>"></i></button></form>
              <form method="post" class="d-inline"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $a['id'] ?>"><button name="action" value="delete" class="btn btn-sm btn-outline-danger" data-confirm="Delete admin <?= e($a['email']) ?>?" title="Delete"><i class="bi bi-trash"></i></button></form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div></div>
    <p class="small text-muted mt-2"><strong>Editors</strong> can manage all content and inquiries. <strong>Super admins</strong> can also manage admin users.</p>
  </div>
  <div class="col-lg-4">
    <form method="post" class="card shadow-sm border-0">
      <?= csrf_field() ?><input type="hidden" name="action" value="create">
      <div class="card-header bg-white"><h2 class="h6 mb-0">Add admin user</h2></div>
      <div class="card-body">
        <div class="mb-3"><label class="form-label" for="name">Name</label><input class="form-control" id="name" name="name" value="<?= e(old('name')) ?>" required></div>
        <div class="mb-3"><label class="form-label" for="email">Email</label><input type="email" class="form-control" id="email" name="email" value="<?= e(old('email')) ?>" required></div>
        <div class="mb-3"><label class="form-label" for="role">Role</label><select class="form-select" id="role" name="role"><option value="editor">Editor</option><option value="super_admin">Super admin</option></select></div>
        <div class="mb-3"><label class="form-label" for="password">Temporary password</label><input type="text" class="form-control font-monospace" id="password" name="password" required minlength="10" autocomplete="off"><div class="form-text">Share securely; they must change it at first login.</div></div>
        <button class="btn btn-brand w-100"><i class="bi bi-person-plus"></i> Create admin</button>
      </div>
    </form>
  </div>
</div>
<?php require __DIR__ . '/partials/footer.php';
