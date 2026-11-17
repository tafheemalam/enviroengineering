<?php
declare(strict_types=1);
require __DIR__ . '/_init.php';

// Logout is POST-only with CSRF so it cannot be triggered by a third-party link
if (is_post()) {
    verify_csrf();
    logout_admin();
    admin_session_start();
    flash('success', 'You have been signed out.');
}
redirect('login.php');
