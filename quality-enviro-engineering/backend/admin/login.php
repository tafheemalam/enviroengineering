<?php
declare(strict_types=1);
require __DIR__ . '/_init.php';
require __DIR__ . '/partials/auth_layout.php';

if (current_admin()) {
    redirect('index.php');
}

$max = (int) config('security.login_max');
$window = (int) config('security.login_window');
$error = null;
$email = '';

if (is_post()) {
    verify_csrf();
    $email = strtolower(clean_str($_POST['email'] ?? '', 190));
    $password = (string) ($_POST['password'] ?? '');

    if (rate_limited('login', $max, $window)) {
        $error = 'Too many failed attempts. Please wait ' . (int) ($window / 60) . ' minutes and try again.';
    } else {
        $admin = filter_var($email, FILTER_VALIDATE_EMAIL)
            ? db_one('SELECT * FROM admins WHERE email = ? AND is_active = 1', [$email])
            : null;
        // Always run password_verify to keep timing similar for unknown users
        $hash = $admin['password_hash'] ?? '$2y$10$dCJWrygcg.A7i96ZbYCc4.U1sjn3L1LV9UlIlg76eTO2rOkYQ5pJG';
        if (password_verify($password, $hash) && $admin) {
            if (password_needs_rehash($admin['password_hash'], PASSWORD_DEFAULT)) {
                db_exec('UPDATE admins SET password_hash = ? WHERE id = ?', [password_hash($password, PASSWORD_DEFAULT), $admin['id']]);
            }
            rate_limit_clear('login');
            login_admin($admin);
            redirect('index.php');
        }
        rate_limit_hit('login', $max, $window);
        $error = 'Invalid email or password.';
    }
}

auth_head('Sign in');
?>
<?php if ($error): ?><div class="alert alert-danger" role="alert"><?= e($error) ?></div><?php endif; ?>
<form method="post" novalidate>
  <?= csrf_field() ?>
  <div class="mb-3">
    <label for="email" class="form-label">Email address</label>
    <input type="email" class="form-control form-control-lg" id="email" name="email" value="<?= e($email) ?>" required autocomplete="username" autofocus>
  </div>
  <div class="mb-3">
    <label for="password" class="form-label">Password</label>
    <div class="input-group">
      <input type="password" class="form-control form-control-lg" id="password" name="password" required autocomplete="current-password">
      <button class="btn btn-outline-secondary" type="button" onclick="const p=document.getElementById('password');p.type=p.type==='password'?'text':'password'" aria-label="Show or hide password"><i class="bi bi-eye"></i></button>
    </div>
  </div>
  <button type="submit" class="btn btn-brand btn-lg w-100">Sign in</button>
  <div class="text-center mt-3"><a href="forgot-password.php" class="small">Forgot your password?</a></div>
</form>
<?php auth_foot();
