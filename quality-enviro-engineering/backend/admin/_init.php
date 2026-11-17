<?php
/**
 * Admin bootstrap – include at the top of every admin page.
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once QES_ROOT . '/middleware/auth.php';
require_once __DIR__ . '/partials/helpers.php';

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('X-Robots-Tag: noindex, nofollow');

admin_session_start();
