<?php
declare(strict_types=1);
require __DIR__ . '/_init.php';
require __DIR__ . '/partials/auth_layout.php';

$token = (string) ($_GET['token'] ?? $_POST['token'] ?? '');
$reset = preg_match('/^[a-f0-9]{64}$/', $token)
    ? db_one('SELECT r.id, r.admin_id FROM password_resets r JOIN admins a ON a.id = r.admin_id
              WHERE r.token_hash = ? AND r.used_at IS NULL AND r.expires_at > UTC_TIMESTAMP() AND a.is_active = 1', [hash('sha256', $token)])
    : null;

$errors = [];
if ($reset && is_post()) {
    verify_csrf();
    $pw = (string) ($_POST['password'] ?? '');
    $pw2 = (string) ($_POST['password_confirm'] ?? '');
    $errors = password_errors($pw, $pw2);
    if (!$errors) {
        db_exec('UPDATE admins SET password_hash = ?, must_change_password = 0 WHERE id = ?', [password_hash($pw, PASSWORD_DEFAULT), $reset['admin_id']]);
        db_exec('UPDATE password_resets SET used_at = UTC_TIMESTAMP() WHERE admin_id = ? AND used_at IS NULL', [$reset['admin_id']]);
        flash('success', 'Your password has been reset. Please sign in.');
        redirect('login.php');
    }
}

auth_head('Choose a new password');
if (!$reset): ?>
  <div class="alert alert-warning" role="alert">This reset link is invalid or has expired.</div>
  <a href="forgot-password.php" class="btn btn-brand w-100">Request a new link</a>
<?php else: ?>
  <?php foreach ($errors as $err): ?><div class="alert alert-danger py-2" role="alert"><?= e($err) ?></div><?php endforeach; ?>
  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="token" value="<?= e($token) ?>">
    <div class="mb-3">
      <label for="password" class="form-label">New password</label>
      <input type="password" class="form-control form-control-lg" id="password" name="password" required minlength="10" autocomplete="new-password" aria-describedby="pwHelp">
      <div id="pwHelp" class="form-text">At least 10 characters, including a letter and a number.</div>
    </div>
    <div class="mb-3">
      <label for="password_confirm" class="form-label">Confirm password</label>
      <input type="password" class="form-control form-control-lg" id="password_confirm" name="password_confirm" required autocomplete="new-password">
    </div>
    <button type="submit" class="btn btn-brand btn-lg w-100">Reset password</button>
  </form>
<?php endif;
auth_foot();
