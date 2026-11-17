<?php
declare(strict_types=1);
require __DIR__ . '/_init.php';
$admin = require_admin();

$errors = [];
if (is_post()) {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    if ($action === 'profile') {
        $name = clean_str($_POST['name'] ?? '', 100);
        $email = strtolower(clean_str($_POST['email'] ?? '', 190));
        if (mb_strlen($name) < 2) {
            $errors[] = 'Please enter your name.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email.';
        } elseif (db_value('SELECT COUNT(*) FROM admins WHERE email = ? AND id <> ?', [$email, $admin['id']])) {
            $errors[] = 'That email is already used by another admin.';
        }
        if (!$errors) {
            db_exec('UPDATE admins SET name = ?, email = ? WHERE id = ?', [$name, $email, $admin['id']]);
            flash('success', 'Profile updated.');
            redirect('profile.php');
        }
    } elseif ($action === 'password') {
        $row = db_one('SELECT password_hash FROM admins WHERE id = ?', [$admin['id']]);
        if (!password_verify((string) ($_POST['current_password'] ?? ''), $row['password_hash'])) {
            $errors[] = 'Your current password is incorrect.';
        }
        $errors = array_merge($errors, password_errors((string) ($_POST['password'] ?? ''), (string) ($_POST['password_confirm'] ?? '')));
        if (!$errors) {
            db_exec('UPDATE admins SET password_hash = ?, must_change_password = 0 WHERE id = ?',
                [password_hash((string) $_POST['password'], PASSWORD_DEFAULT), $admin['id']]);
            session_regenerate_id(true);
            flash('success', 'Password changed successfully.');
            redirect('index.php');
        }
    }
}

$pageTitle = 'Profile & Password';
$active = '';
require __DIR__ . '/partials/header.php';
?>
<?php if ((int) $admin['must_change_password'] === 1): ?>
  <div class="alert alert-warning"><i class="bi bi-shield-exclamation"></i> For security, you must replace the default password before using the admin panel.</div>
<?php endif; ?>
<?php foreach ($errors as $err): ?><div class="alert alert-danger py-2"><?= e($err) ?></div><?php endforeach; ?>

<div class="row g-4">
  <div class="col-lg-6">
    <form method="post" class="card shadow-sm border-0">
      <div class="card-header bg-white"><h2 class="h6 mb-0">Profile</h2></div>
      <div class="card-body">
        <?= csrf_field() ?><input type="hidden" name="action" value="profile">
        <div class="mb-3"><label class="form-label" for="name">Name</label><input class="form-control" id="name" name="name" value="<?= e($admin['name']) ?>" required maxlength="100"></div>
        <div class="mb-3"><label class="form-label" for="email">Email</label><input class="form-control" type="email" id="email" name="email" value="<?= e($admin['email']) ?>" required maxlength="190"></div>
        <button class="btn btn-brand">Save profile</button>
      </div>
    </form>
  </div>
  <div class="col-lg-6">
    <form method="post" class="card shadow-sm border-0">
      <div class="card-header bg-white"><h2 class="h6 mb-0">Change password</h2></div>
      <div class="card-body">
        <?= csrf_field() ?><input type="hidden" name="action" value="password">
        <div class="mb-3"><label class="form-label" for="cp">Current password</label><input class="form-control" type="password" id="cp" name="current_password" required autocomplete="current-password"></div>
        <div class="mb-3"><label class="form-label" for="np">New password</label><input class="form-control" type="password" id="np" name="password" required minlength="10" autocomplete="new-password" aria-describedby="npHelp"><div id="npHelp" class="form-text">At least 10 characters with a letter and a number.</div></div>
        <div class="mb-3"><label class="form-label" for="np2">Confirm new password</label><input class="form-control" type="password" id="np2" name="password_confirm" required autocomplete="new-password"></div>
        <button class="btn btn-brand">Change password</button>
      </div>
    </form>
  </div>
</div>
<?php require __DIR__ . '/partials/footer.php';
