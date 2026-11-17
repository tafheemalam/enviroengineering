<?php
declare(strict_types=1);

/** Read a config value using dot notation, e.g. config('db.host'). */
function config(string $key, $default = null)
{
    $value = $GLOBALS['QES_CONFIG'] ?? [];
    foreach (explode('.', $key) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }
    return $value;
}

/** HTML-escape output (XSS protection). */
function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** All website settings as key => value (cached per request). */
function settings(bool $refresh = false): array
{
    static $cache = null;
    if ($cache === null || $refresh) {
        $cache = [];
        foreach (db_all('SELECT setting_key, setting_value FROM website_settings') as $r) {
            $cache[$r['setting_key']] = $r['setting_value'];
        }
    }
    return $cache;
}

function setting(string $key, $default = null)
{
    $s = settings();
    return isset($s[$key]) && $s[$key] !== '' ? $s[$key] : $default;
}

function save_setting(string $key, ?string $value, string $group = 'general', int $public = 1): void
{
    db_exec(
        'INSERT INTO website_settings (setting_key, setting_value, setting_group, is_public) VALUES (?,?,?,?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
        [$key, $value, $group, $public]
    );
}

/** Public base URL of the site (no trailing slash). */
function site_url(string $path = ''): string
{
    $base = rtrim((string) (config('app.url') ?: setting('site_url', '')), '/');
    if ($base === '') {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $base = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    }
    return $base . ($path === '' ? '' : '/' . ltrim($path, '/'));
}

/** Build a public URL for an uploaded file path (relative to /uploads). */
function upload_url(?string $path, bool $absolute = false): ?string
{
    if ($path === null || $path === '') {
        return null;
    }
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    $rel = rtrim(config('uploads.url'), '/') . '/' . ltrim($path, '/');
    return $absolute ? site_url($rel) : $rel;
}

/** Convert a string into a URL-safe slug. */
function slugify(string $text): string
{
    $text = strtolower(trim($text));
    if (function_exists('iconv')) {
        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        if ($converted !== false) {
            $text = strtolower($converted);
        }
    }
    $text = str_replace('&', ' and ', $text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim((string) $text, '-');
    return substr($text, 0, 180) ?: 'item';
}

/** Ensure slug is unique within a table (optionally excluding the current id). */
function unique_slug(string $table, string $slug, ?int $exceptId = null): string
{
    $allowed = ['services', 'posts'];
    if (!in_array($table, $allowed, true)) {
        throw new InvalidArgumentException('Invalid table');
    }
    $base = $slug;
    $i = 2;
    while (true) {
        $sql = "SELECT COUNT(*) FROM `$table` WHERE slug = ?" . ($exceptId ? ' AND id <> ?' : '');
        $params = $exceptId ? [$slug, $exceptId] : [$slug];
        if ((int) db_value($sql, $params) === 0) {
            return $slug;
        }
        $slug = $base . '-' . $i++;
    }
}

/** Decode a JSON column into an array. */
function json_list($value): array
{
    if (is_array($value)) {
        return $value;
    }
    if ($value === null || $value === '') {
        return [];
    }
    $d = json_decode((string) $value, true);
    return is_array($d) ? $d : [];
}

/** Client IP (REMOTE_ADDR only – forwarded headers are spoofable unless behind a trusted proxy). */
function client_ip(): string
{
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'), 0, 45);
}

/** Format a UTC DB timestamp in the configured timezone. */
function format_dt(?string $dt, string $format = 'd M Y, H:i'): string
{
    if (!$dt) {
        return '';
    }
    try {
        $d = new DateTime($dt, new DateTimeZone('UTC'));
        $d->setTimezone(new DateTimeZone(date_default_timezone_get()));
        return $d->format($format);
    } catch (Throwable $e) {
        return (string) $dt;
    }
}

/** Trim + length-limit a scalar input. */
function clean_str($value, int $max = 255): string
{
    if (is_array($value)) {
        return '';
    }
    $v = trim(str_replace("\0", '', (string) $value));
    return mb_substr($v, 0, $max);
}

/** Validate an internal link or http(s) URL used in CMS buttons. */
function safe_link(?string $url): string
{
    $url = trim((string) $url);
    if ($url === '') {
        return '';
    }
    if ($url[0] === '/' && (strlen($url) === 1 || $url[1] !== '/')) {
        return $url;                       // internal path
    }
    if (preg_match('#^(https?://|mailto:|tel:)#i', $url) && filter_var(preg_replace('#^(mailto:|tel:)#i', 'http://x/', $url), FILTER_VALIDATE_URL)) {
        return $url;
    }
    return '';
}

/** Allow only Google Maps embed URLs (prevents arbitrary iframes). */
function safe_map_url(?string $url): string
{
    $url = trim((string) $url);
    // Accept a pasted <iframe> snippet too
    if (stripos($url, '<iframe') !== false && preg_match('/src=["\']([^"\']+)["\']/i', $url, $m)) {
        $url = html_entity_decode($m[1]);
    }
    if (preg_match('#^https://(www\.)?google\.[a-z.]+/maps(/embed)?[/?]#i', $url) || preg_match('#^https://maps\.google\.[a-z.]+/#i', $url)) {
        return $url;
    }
    return '';
}

/** Split plain text into paragraphs. */
function paragraphs(?string $text): array
{
    $text = trim(str_replace("\r\n", "\n", (string) $text));
    if ($text === '') {
        return [];
    }
    return array_values(array_filter(array_map('trim', preg_split("/\n\s*\n/", $text))));
}

function str_limit(?string $text, int $len = 160): string
{
    $text = trim(preg_replace('/\s+/', ' ', strip_tags((string) $text)));
    return mb_strlen($text) > $len ? rtrim(mb_substr($text, 0, $len - 1)) . '…' : $text;
}
