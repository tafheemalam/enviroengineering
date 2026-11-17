<?php
/**
 * Common bootstrap for API, admin and SEO shell.
 */
declare(strict_types=1);

define('QES_ROOT', dirname(__DIR__));
define('QES_BOOTSTRAPPED', true);

$GLOBALS['QES_CONFIG'] = require QES_ROOT . '/config/config.php';

require_once QES_ROOT . '/includes/db.php';
require_once QES_ROOT . '/includes/helpers.php';
require_once QES_ROOT . '/includes/sanitize.php';
require_once QES_ROOT . '/includes/mailer.php';
require_once QES_ROOT . '/includes/upload.php';
require_once QES_ROOT . '/middleware/rate_limit.php';

$debug = (bool) config('app.debug');
ini_set('display_errors', $debug ? '1' : '0');
error_reporting(E_ALL);

// Apply admin-configured timezone (falls back to UTC if DB unavailable)
try {
    $tz = setting('timezone', 'UTC');
    date_default_timezone_set(in_array($tz, timezone_identifiers_list(), true) ? $tz : 'UTC');
} catch (Throwable $e) {
    date_default_timezone_set('UTC');
}
