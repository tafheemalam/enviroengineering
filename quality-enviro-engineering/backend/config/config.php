<?php
/**
 * Application configuration.
 * Values come from a .env file (backend/.env or the project root .env).
 * Never commit a real .env – copy .env.example and fill it in.
 */
declare(strict_types=1);

if (!function_exists('qes_load_env')) {
    function qes_load_env(array $paths): void
    {
        foreach ($paths as $path) {
            if (!is_file($path) || !is_readable($path)) {
                continue;
            }
            foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                $line = trim($line);
                if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
                    continue;
                }
                [$k, $v] = array_map('trim', explode('=', $line, 2));
                if (strlen($v) >= 2 && ($v[0] === '"' || $v[0] === "'") && $v[-1] === $v[0]) {
                    $v = substr($v, 1, -1);
                }
                if (getenv($k) === false) {
                    putenv("$k=$v");
                    $_ENV[$k] = $v;
                }
            }
            return; // first file found wins
        }
    }
}

qes_load_env([
    dirname(__DIR__) . '/.env',
    dirname(__DIR__, 2) . '/.env',
]);

$env = static fn(string $k, $default = null) => ($v = getenv($k)) === false || $v === '' ? $default : $v;

return [
    'app' => [
        'env'       => $env('APP_ENV', 'production'),
        'debug'     => filter_var($env('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN),
        'url'       => rtrim((string) $env('APP_URL', ''), '/'),
        'key'       => $env('APP_KEY', 'change-this-to-a-long-random-string'),
        // Comma-separated list of origins allowed to call the API (dev: Vite server)
        'cors'      => array_filter(array_map('trim', explode(',', (string) $env('CORS_ORIGINS', 'http://localhost:5173')))),
    ],
    'db' => [
        'host'    => $env('DB_HOST', '127.0.0.1'),
        'port'    => (int) $env('DB_PORT', 3306),
        'name'    => $env('DB_NAME', 'qes_website'),
        'user'    => $env('DB_USER', 'root'),
        'pass'    => $env('DB_PASS', ''),
        'charset' => 'utf8mb4',
    ],
    'uploads' => [
        'dir'       => dirname(__DIR__) . '/uploads',
        'url'       => '/uploads',
        'max_bytes' => (int) $env('UPLOAD_MAX_BYTES', 5 * 1024 * 1024),
        // extension => allowed mime types (SVG deliberately excluded – XSS risk)
        'allowed'   => [
            'jpg'  => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png'  => ['image/png'],
            'webp' => ['image/webp'],
            'gif'  => ['image/gif'],
            'ico'  => ['image/x-icon', 'image/vnd.microsoft.icon'],
        ],
    ],
    'security' => [
        'session_name'     => 'qes_admin',
        'session_lifetime' => 60 * 60 * 2,          // 2 hours idle
        'login_max'        => 5,                     // attempts …
        'login_window'     => 15 * 60,               // … per 15 minutes per IP
        'contact_max'      => 5,                     // submissions …
        'contact_window'   => 60 * 60,               // … per hour per IP
        'min_form_seconds' => 3,                     // bots submit faster than humans
    ],
];
