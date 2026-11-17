<?php
declare(strict_types=1);

/**
 * Secure image upload handler.
 *  - validates upload error, size, extension AND real MIME type (finfo)
 *  - verifies the file is a real image (getimagesize)
 *  - stores it under a random name in a sub-folder of /uploads
 *  - records it in the media table
 *
 * @return array{path:string, id:int}
 * @throws RuntimeException with a user-friendly message
 */
function handle_image_upload(array $file, string $folder = 'media', ?int $adminId = null, string $alt = ''): array
{
    $folder = preg_replace('/[^a-z0-9_-]/', '', strtolower($folder)) ?: 'media';

    if (!isset($file['error']) || is_array($file['error'])) {
        throw new RuntimeException('Invalid upload.');
    }
    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            throw new RuntimeException('No file was selected.');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new RuntimeException('The file is too large.');
        default:
            throw new RuntimeException('Upload failed (code ' . (int) $file['error'] . ').');
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('Invalid upload source.');
    }

    $max = (int) config('uploads.max_bytes');
    if ($file['size'] <= 0 || $file['size'] > $max) {
        throw new RuntimeException('Images must be smaller than ' . round($max / 1048576, 1) . ' MB.');
    }

    $allowed = config('uploads.allowed');
    $ext = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
    if (!isset($allowed[$ext])) {
        throw new RuntimeException('Allowed file types: ' . implode(', ', array_keys($allowed)) . '.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = (string) $finfo->file($file['tmp_name']);
    if (!in_array($mime, $allowed[$ext], true)) {
        throw new RuntimeException('The file content does not match its extension.');
    }

    $width = $height = null;
    if ($ext !== 'ico') {
        $info = @getimagesize($file['tmp_name']);
        if ($info === false) {
            throw new RuntimeException('The file is not a valid image.');
        }
        [$width, $height] = $info;
    }

    $dir = rtrim(config('uploads.dir'), '/\\') . DIRECTORY_SEPARATOR . $folder;
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        throw new RuntimeException('Upload folder is not writable.');
    }
    $name = date('Ymd') . '-' . bin2hex(random_bytes(8)) . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
    $dest = $dir . DIRECTORY_SEPARATOR . $name;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new RuntimeException('Could not save the uploaded file.');
    }
    @chmod($dest, 0644);

    $rel = $folder . '/' . $name;
    db_exec(
        'INSERT INTO media (file_path, original_name, mime_type, file_size, width, height, alt_text, uploaded_by) VALUES (?,?,?,?,?,?,?,?)',
        [$rel, clean_str(basename((string) $file['name']), 255), $mime, (int) $file['size'], $width, $height, clean_str($alt, 255) ?: null, $adminId]
    );
    return ['path' => $rel, 'id' => (int) db()->lastInsertId()];
}

/** Is a media path referenced anywhere in content? */
function media_usage(string $path): array
{
    $uses = [];
    $like = '%' . $path . '%';
    foreach (db_all('SELECT title FROM services WHERE image = ? OR og_image = ? OR full_description LIKE ?', [$path, $path, $like]) as $r) {
        $uses[] = 'Service: ' . $r['title'];
    }
    foreach (db_all('SELECT label FROM page_sections WHERE image = ?', [$path]) as $r) {
        $uses[] = 'Section: ' . $r['label'];
    }
    foreach (db_all('SELECT title FROM posts WHERE image = ? OR content LIKE ?', [$path, $like]) as $r) {
        $uses[] = 'Insight: ' . $r['title'];
    }
    foreach (db_all('SELECT page_key FROM seo_settings WHERE og_image = ?', [$path]) as $r) {
        $uses[] = 'SEO: ' . $r['page_key'];
    }
    foreach (db_all('SELECT setting_key FROM website_settings WHERE setting_value = ?', [$path]) as $r) {
        $uses[] = 'Setting: ' . $r['setting_key'];
    }
    return $uses;
}

function delete_upload_file(string $rel): void
{
    $base = realpath(config('uploads.dir'));
    $full = realpath(config('uploads.dir') . '/' . $rel);
    if ($base && $full && str_starts_with($full, $base . DIRECTORY_SEPARATOR) && is_file($full)) {
        @unlink($full);
    }
}
