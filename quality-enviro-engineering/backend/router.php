<?php
/**
 * Router for PHP's built-in development server – mirrors .htaccess rules.
 *   cd backend && php -S localhost:8000 router.php
 * Not used by Apache/Nginx in production.
 */
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$file = __DIR__ . $path;

// Block access to private folders
if (preg_match('#^/(config|includes|middleware|admin/partials|build/.vite)(/|$)|/\.env|\.sql$|\.md$#i', $path)) {
    http_response_code(403);
    exit('Forbidden');
}
// Never execute PHP from uploads
if (str_starts_with($path, '/uploads/') && preg_match('/\.ph(p\d?|tml|ar)$/i', $path)) {
    http_response_code(403);
    exit('Forbidden');
}
if ($path === '/sitemap.xml') {
    require __DIR__ . '/sitemap.php';
    return true;
}
if ($path === '/robots.txt') {
    require __DIR__ . '/robots.php';
    return true;
}
if (str_starts_with($path, '/api')) {
    $_GET['route'] = substr($path, 4);
    require __DIR__ . '/api/index.php';
    return true;
}
if ($path === '/admin') {
    header('Location: /admin/');
    return true;
}
if (str_starts_with($path, '/admin/')) {
    if (is_dir($file)) {
        $file = rtrim($file, '/') . '/index.php';
    }
    if (is_file($file) && str_ends_with($file, '.php')) {
        $_SERVER['SCRIPT_NAME'] = substr($file, strlen(__DIR__));
        chdir(dirname($file));
        require $file;
        return true;
    }
    return is_file($file) ? false : (http_response_code(404) && print('Not found'));
}
if ($path !== '/' && is_file($file)) {
    return false; // static asset
}
require __DIR__ . '/index.php';
return true;
