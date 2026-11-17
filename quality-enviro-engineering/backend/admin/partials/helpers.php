<?php
declare(strict_types=1);

/** Icons offered in the admin (must exist in frontend/src/utils/icons.js). */
function admin_icons(): array
{
    return ['Leaf', 'Activity', 'ShieldCheck', 'ClipboardCheck', 'HardHat', 'Wrench', 'Recycle', 'Droplets', 'Wind',
        'Target', 'Eye', 'Award', 'BadgeCheck', 'ThumbsUp', 'Smile', 'Cog', 'MessagesSquare', 'Search', 'ClipboardList',
        'Scale', 'Users', 'Lightbulb', 'FileText', 'Clock', 'Factory', 'Gauge', 'Building2', 'Globe', 'Sprout',
        'FlaskConical', 'Zap', 'Settings', 'Handshake', 'TreePine', 'Waves', 'Hammer', 'Ruler', 'HeartHandshake',
        'Microscope', 'Truck', 'CheckCircle2', 'Star', 'Flame', 'ShieldAlert', 'Thermometer'];
}

function icon_select(string $name, ?string $current, string $attrs = ''): string
{
    $html = '<select name="' . e($name) . '" class="form-select" ' . $attrs . '><option value="">— none —</option>';
    foreach (admin_icons() as $i) {
        $html .= '<option value="' . e($i) . '"' . ($i === $current ? ' selected' : '') . '>' . e($i) . '</option>';
    }
    return $html . '</select>';
}

function status_badge(string $status): string
{
    $map = [
        'published' => 'success', 'draft' => 'secondary',
        'new' => 'primary', 'in_progress' => 'warning', 'responded' => 'info', 'closed' => 'dark',
    ];
    $label = ucwords(str_replace('_', ' ', $status));
    return '<span class="badge text-bg-' . ($map[$status] ?? 'secondary') . '">' . e($label) . '</span>';
}

/** Simple pagination helper. Returns [offset, html]. */
function paginate(int $total, int $perPage, int $page, array $query = []): array
{
    $pages = max(1, (int) ceil($total / $perPage));
    $page = min(max(1, $page), $pages);
    $html = '';
    if ($pages > 1) {
        $html .= '<nav aria-label="Pagination"><ul class="pagination pagination-sm mb-0">';
        for ($i = 1; $i <= $pages; $i++) {
            if ($pages > 10 && $i > 2 && $i < $pages - 1 && abs($i - $page) > 2) {
                if ($i === 3 || $i === $pages - 2) {
                    $html .= '<li class="page-item disabled"><span class="page-link">…</span></li>';
                }
                continue;
            }
            $q = http_build_query(array_merge($query, ['page' => $i]));
            $html .= '<li class="page-item' . ($i === $page ? ' active' : '') . '"><a class="page-link" href="?' . e($q) . '">' . $i . '</a></li>';
        }
        $html .= '</ul></nav>';
    }
    return [($page - 1) * $perPage, $html];
}

/** Upload helper used by edit forms: returns new relative path, the chosen library path, or the existing value. */
function process_image_field(string $field, ?string $current, string $folder, array $admin, string $alt = ''): ?string
{
    if (!empty($_POST[$field . '_remove'])) {
        return null;
    }
    if (!empty($_FILES[$field]) && ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $up = handle_image_upload($_FILES[$field], $folder, (int) $admin['id'], $alt);
        return $up['path'];
    }
    $picked = clean_str($_POST[$field . '_library'] ?? '', 255);
    if ($picked !== '' && db_value('SELECT COUNT(*) FROM media WHERE file_path = ?', [$picked])) {
        return $picked;
    }
    return $current;
}

/** Image input with preview, library picker and remove checkbox. */
function image_field(string $field, ?string $current, string $label = 'Image', string $help = ''): string
{
    $id = 'img_' . preg_replace('/\W/', '', $field);
    $url = upload_url($current);
    $html = '<div class="mb-3 image-field" data-field="' . e($field) . '">';
    $html .= '<label class="form-label fw-semibold" for="' . $id . '">' . e($label) . '</label>';
    $html .= '<div class="d-flex gap-3 align-items-start flex-wrap">';
    $html .= '<div class="img-preview' . ($url ? '' : ' empty') . '">' . ($url ? '<img src="' . e($url) . '" alt="">' : '<i class="bi bi-image"></i>') . '</div>';
    $html .= '<div class="flex-grow-1" style="min-width:220px">';
    $html .= '<input type="file" class="form-control" id="' . $id . '" name="' . e($field) . '" accept=".jpg,.jpeg,.png,.webp,.gif">';
    $html .= '<input type="hidden" name="' . e($field) . '_library" value="">';
    $html .= '<div class="d-flex gap-2 mt-2 flex-wrap">';
    $html .= '<button type="button" class="btn btn-sm btn-outline-secondary js-open-library"><i class="bi bi-images"></i> Choose from library</button>';
    if ($current) {
        $html .= '<div class="form-check ms-1 mt-1"><input class="form-check-input" type="checkbox" name="' . e($field) . '_remove" value="1" id="' . $id . '_rm"><label class="form-check-label small" for="' . $id . '_rm">Remove image</label></div>';
    }
    $html .= '</div>';
    $html .= '<div class="form-text">' . e($help ?: 'JPG, PNG, WebP or GIF, max ' . round(config('uploads.max_bytes') / 1048576, 1) . ' MB. WebP recommended for speed.') . '</div>';
    $html .= '</div></div></div>';
    return $html;
}

/** Modal listing the media library – include once on pages using image_field(). */
function media_library_modal(): string
{
    $items = db_all("SELECT file_path, original_name FROM media WHERE mime_type <> 'image/x-icon' ORDER BY created_at DESC LIMIT 200");
    $html = '<div class="modal fade" id="mediaLibrary" tabindex="-1" aria-labelledby="mediaLibraryLabel" aria-hidden="true"><div class="modal-dialog modal-xl modal-dialog-scrollable"><div class="modal-content">';
    $html .= '<div class="modal-header"><h2 class="modal-title fs-5" id="mediaLibraryLabel">Media library</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body">';
    if (!$items) {
        $html .= '<p class="text-muted mb-0">No images uploaded yet. Upload files in <a href="media.php">Media</a>.</p>';
    } else {
        $html .= '<div class="media-grid">';
        foreach ($items as $m) {
            $html .= '<button type="button" class="media-pick" data-path="' . e($m['file_path']) . '" data-url="' . e(upload_url($m['file_path'])) . '" title="' . e($m['original_name']) . '"><img src="' . e(upload_url($m['file_path'])) . '" alt="" loading="lazy"><span>' . e($m['original_name']) . '</span></button>';
        }
        $html .= '</div>';
    }
    return $html . '</div></div></div></div>';
}

/** Parse the repeater JSON posted by the admin items editor. */
function posted_items(string $field = 'items_json'): array
{
    $raw = json_decode((string) ($_POST[$field] ?? '[]'), true);
    if (!is_array($raw)) {
        return [];
    }
    $out = [];
    foreach (array_slice($raw, 0, 30) as $r) {
        if (!is_array($r)) {
            continue;
        }
        $title = clean_str($r['title'] ?? '', 150);
        $text = clean_str($r['text'] ?? '', 600);
        $icon = in_array($r['icon'] ?? '', admin_icons(), true) ? $r['icon'] : '';
        if ($title !== '' || $text !== '') {
            $out[] = ['title' => $title, 'text' => $text, 'icon' => $icon];
        }
    }
    return $out;
}

/** Password policy. Returns a list of error messages (empty = OK). */
function password_errors(string $pw, string $confirm): array
{
    $errors = [];
    if (mb_strlen($pw) < 10) {
        $errors[] = 'Password must be at least 10 characters.';
    }
    if (!preg_match('/[A-Za-z]/', $pw) || !preg_match('/\d/', $pw)) {
        $errors[] = 'Password must contain at least one letter and one number.';
    }
    if (strtolower($pw) === 'changeme@2026') {
        $errors[] = 'Please choose a password different from the default.';
    }
    if ($pw !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }
    return $errors;
}

/** Lines textarea -> clean array (for benefits / features). */
function lines_to_array(?string $text, int $max = 30): array
{
    $lines = array_filter(array_map(fn($l) => clean_str($l, 300), preg_split('/\R/', (string) $text)));
    return array_values(array_slice($lines, 0, $max));
}

/** Rich text editor (contenteditable + HTML source) bound to a textarea. */
function rich_editor(string $name, ?string $value, int $rows = 12): string
{
    $id = 're_' . preg_replace('/\W/', '', $name);
    $btn = fn($cmd, $icon, $title, $arg = '') => '<button type="button" class="btn btn-sm btn-light" data-cmd="' . $cmd . '" data-arg="' . $arg . '" title="' . $title . '" aria-label="' . $title . '"><i class="bi bi-' . $icon . '"></i></button>';
    return '<div class="rich-editor" data-target="' . $id . '">'
        . '<div class="re-toolbar btn-toolbar gap-1 p-2 border-bottom bg-light" role="toolbar" aria-label="Formatting">'
        . $btn('formatBlock', 'paragraph', 'Paragraph', 'P') . $btn('formatBlock', 'type-h2', 'Heading 2', 'H2') . $btn('formatBlock', 'type-h3', 'Heading 3', 'H3')
        . $btn('bold', 'type-bold', 'Bold') . $btn('italic', 'type-italic', 'Italic')
        . $btn('insertUnorderedList', 'list-ul', 'Bullet list') . $btn('insertOrderedList', 'list-ol', 'Numbered list')
        . $btn('formatBlock', 'quote', 'Quote', 'BLOCKQUOTE') . $btn('createLink', 'link-45deg', 'Insert link') . $btn('unlink', 'link', 'Remove link')
        . $btn('removeFormat', 'eraser', 'Clear formatting')
        . '<button type="button" class="btn btn-sm btn-outline-secondary ms-auto" data-toggle-source><i class="bi bi-code-slash"></i> HTML</button>'
        . '</div>'
        . '<div class="re-area" contenteditable="true" role="textbox" aria-multiline="true"></div>'
        . '<textarea class="form-control re-source d-none font-monospace" id="' . $id . '" name="' . e($name) . '" rows="' . $rows . '">' . e($value) . '</textarea>'
        . '</div>';
}
