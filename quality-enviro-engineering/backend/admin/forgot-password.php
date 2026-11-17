<?php
declare(strict_types=1);
require __DIR__ . '/_init.php';
require __DIR__ . '/partials/auth_layout.php';

$sent = false;
if (is_post()) {
    verify_csrf();
    $email = strtolower(clean_str($_POST['email'] ?? '', 190));
    // Rate limit per IP regardless of whether the email exists
    if (rate_limit_hit('pw_reset', 5, 3600)) {
        $admin = filter_var($email, FILTER_VALIDATE_EMAIL) ? db_one('SELECT id, name, email FROM admins WHERE email = ? AND is_active = 1', [$email]) : null;
        if ($admin) {
            $token = bin2hex(random_bytes(32));
            db_exec('UPDATE password_resets SET used_at = UTC_TIMESTAMP() WHERE admin_id = ? AND used_at IS NULL', [$admin['id']]);
            db_exec('INSERT INTO password_resets (admin_id, token_hash, expires_at) VALUES (?, ?, UTC_TIMESTAMP() + INTERVAL 1 HOUR)',
                [$admin['id'], hash('sha256', $token)]);
            $link = site_url('/admin/reset-password.php?token=' . $token);
            send_mail($admin['email'], 'Password reset request',
                email_layout('Reset your password',
                    '<p>Hello ' . e($admin['name']) . ',</p><p>We received a request to reset your admin password. Click the button below to choose a new password. This link expires in 1 hour.</p>'
                    . '<p><a href="' . e($link) . '" style="display:inline-block;background:#1b6b34;color:#fff;padding:12px 22px;border-radius:6px;text-decoration:none">Reset password</a></p>'
                    . '<p style="font-size:12px;color:#5b6b78">If you did not request this, you can ignore this email.</p>'));
        }
    }
    $sent = true; // same response whether or not the account exists
}

auth_head('Forgot password');
if ($sent): ?>
  <div class="alert alert-success" role="status">If an account exists for that email, a password reset link has been sent. Please check your inbox.</div>
  <a href="login.php" class="btn btn-outline-secondary w-100">Back to sign in</a>
<?php else: ?>
  <p class="text-muted">Enter your admin email address and we will send you a link to reset your password.</p>
  <form method="post">
    <?= csrf_field() ?>
    <div class="mb-3">
      <label for="email" class="form-label">Email address</label>
      <input type="email" class="form-control form-control-lg" id="email" name="email" required autocomplete="username" autofocus>
    </div>
    <button type="submit" class="btn btn-brand btn-lg w-100">Send reset link</button>
    <div class="text-center mt-3"><a href="login.php" class="small">Back to sign in</a></div>
  </form>
<?php endif;
auth_foot();
