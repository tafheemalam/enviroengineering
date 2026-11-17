<?php
declare(strict_types=1);

/**
 * Admin session, authentication, CSRF and role checks.
 */

function admin_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name(config('security.session_name'));
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/admin',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();

    // idle timeout
    $life = (int) config('security.session_lifetime');
    if (isset($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > $life) {
        $_SESSION = [];
        session_regenerate_id(true);
        $_SESSION['flash'][] = ['warning', 'Your session expired. Please sign in again.'];
    }
    $_SESSION['last_activity'] = time();

    // bind session to user agent to make hijacking harder
    $ua = hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? '');
    if (isset($_SESSION['admin_id'], $_SESSION['ua']) && !hash_equals($_SESSION['ua'], $ua)) {
        $_SESSION = [];
        session_regenerate_id(true);
    }
}

function current_admin(): ?array
{
    static $admin = false;
    if ($admin !== false) {
        return $admin;
    }
    $admin = null;
    if (!empty($_SESSION['admin_id'])) {
        $admin = db_one('SELECT id, name, email, role, must_change_password FROM admins WHERE id = ? AND is_active = 1', [(int) $_SESSION['admin_id']]);
        if (!$admin) {
            unset($_SESSION['admin_id']);
        }
    }
    return $admin;
}

function login_admin(array $admin): void
{
    session_regenerate_id(true);           // prevent session fixation
    $_SESSION['admin_id'] = (int) $admin['id'];
    $_SESSION['ua'] = hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? '');
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
    db_exec('UPDATE admins SET last_login_at = UTC_TIMESTAMP() WHERE id = ?', [(int) $admin['id']]);
}

function logout_admin(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/** Guard for every protected admin page. */
function require_admin(?string $role = null): array
{
    $admin = current_admin();
    if (!$admin) {
        redirect('login.php');
    }
    $script = basename($_SERVER['SCRIPT_NAME'] ?? '');
    if ((int) $admin['must_change_password'] === 1 && !in_array($script, ['profile.php', 'logout.php'], true)) {
        flash('warning', 'Please change the default password before continuing.');
        redirect('profile.php');
    }
    if ($role === 'super_admin' && $admin['role'] !== 'super_admin') {
        http_response_code(403);
        flash('danger', 'You do not have permission to access that section.');
        redirect('index.php');
    }
    return $admin;
}

function is_super_admin(): bool
{
    return (current_admin()['role'] ?? '') === 'super_admin';
}

// ---- CSRF ------------------------------------------------------------
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!is_string($token) || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $token)) {
        http_response_code(419);
        flash('danger', 'Your form expired or was invalid. Please try again.');
        redirect($_SERVER['HTTP_REFERER'] ?? 'index.php', true);
    }
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

// ---- Flash + redirect ----------------------------------------------
function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = [$type, $message];
}

function take_flashes(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

function redirect(string $url, bool $fromReferer = false): never
{
    // only allow same-site relative redirects
    if ($fromReferer) {
        $parts = parse_url($url);
        $url = ($parts['path'] ?? 'index.php') . (isset($parts['query']) ? '?' . $parts['query'] : '');
        if (!str_starts_with($url, '/admin')) {
            $url = 'index.php';
        }
    } elseif (preg_match('#^(https?:)?//#i', $url)) {
        $url = 'index.php';
    }
    header('Location: ' . $url);
    exit;
}

function old(string $key, $default = '')
{
    return $_POST[$key] ?? $default;
}
