<?php
declare(strict_types=1);

/**
 * Read-side data access shared by the REST API and the SEO shell (index.php).
 * All returned arrays are safe to json_encode for the public site.
 */

function public_settings(): array
{
    $out = [];
    foreach (db_all('SELECT setting_key, setting_value FROM website_settings WHERE is_public = 1') as $r) {
        $out[$r['setting_key']] = $r['setting_value'];
    }
    $out['logo_url']    = upload_url($out['logo'] ?? null);
    $out['favicon_url'] = upload_url($out['favicon'] ?? null);
    $out['map_embed_url'] = safe_map_url($out['map_embed_url'] ?? '');
    foreach (['show_insights', 'show_testimonials', 'show_whatsapp'] as $flag) {
        $out[$flag] = ($out[$flag] ?? '0') === '1';
    }
    $footer = db_one("SELECT s.content FROM page_sections s JOIN pages p ON p.id = s.page_id WHERE p.page_key='home' AND s.section_key='footer'");
    $out['footer_description'] = $footer['content'] ?? '';
    return $out;
}

function public_social_links(): array
{
    $rows = db_all("SELECT platform, label, url FROM social_links WHERE is_active = 1 AND url IS NOT NULL AND url <> '' ORDER BY sort_order, id");
    return array_values(array_filter(array_map(function ($r) {
        $r['url'] = safe_link($r['url']);
        return $r;
    }, $rows), fn($r) => $r['url'] !== ''));
}

function format_section(array $s): array
{
    return [
        'key'          => $s['section_key'],
        'eyebrow'      => $s['eyebrow'],
        'title'        => $s['title'],
        'subtitle'     => $s['subtitle'],
        'paragraphs'   => paragraphs($s['content']),
        'image'        => upload_url($s['image']),
        'image_alt'    => $s['image_alt'] ?: ($s['title'] ?? ''),
        'button_text'  => $s['button_text'],
        'button_link'  => safe_link($s['button_link']),
        'button2_text' => $s['button2_text'],
        'button2_link' => safe_link($s['button2_link']),
        'items'        => array_values(array_map(fn($i) => [
            'title' => (string) ($i['title'] ?? ''),
            'text'  => (string) ($i['text'] ?? ''),
            'icon'  => preg_replace('/[^A-Za-z0-9]/', '', (string) ($i['icon'] ?? '')),
        ], json_list($s['items']))),
    ];
}

/** @return array{page: array, sections: array<string,array>}|null */
function get_page(string $key): ?array
{
    $page = db_one("SELECT id, page_key, title, slug, content, updated_at FROM pages WHERE page_key = ? AND status = 'published'", [$key]);
    if (!$page) {
        return null;
    }
    $sections = [];
    foreach (db_all('SELECT * FROM page_sections WHERE page_id = ? AND is_visible = 1 ORDER BY sort_order, id', [$page['id']]) as $s) {
        if ($s['section_key'] === 'footer') {
            continue;
        }
        $sections[$s['section_key']] = format_section($s);
    }
    return [
        'page' => [
            'key'        => $page['page_key'],
            'title'      => $page['title'],
            'slug'       => $page['slug'],
            'content'    => $page['content'], // sanitised on save
            'updated_at' => $page['updated_at'],
        ],
        'sections' => (object) $sections,
    ];
}

function format_service(array $s, bool $full = false): array
{
    $out = [
        'id'                => (int) $s['id'],
        'title'             => $s['title'],
        'slug'              => $s['slug'],
        'short_description' => $s['short_description'],
        'image'             => upload_url($s['image']),
        'image_alt'         => $s['image_alt'] ?: $s['title'],
        'icon'              => $s['icon'],
        'page_type'         => $s['page_type'],
        'is_featured'       => (bool) $s['is_featured'],
    ];
    if ($full) {
        $out['full_description'] = $s['full_description'];
        $out['benefits']         = json_list($s['benefits']);
        $out['key_features']     = json_list($s['key_features']);
        $out['seo'] = [
            'title'       => $s['seo_title'] ?: $s['title'] . ' | ' . setting('company_name'),
            'description' => $s['meta_description'] ?: str_limit($s['short_description'], 158),
            'keywords'    => $s['meta_keywords'],
            'og_image'    => upload_url($s['og_image'] ?: $s['image'], true),
        ];
        $out['updated_at'] = $s['updated_at'];
    }
    return $out;
}

function get_services(?bool $featuredOnly = false, ?int $limit = null): array
{
    $sql = "SELECT * FROM services WHERE status = 'published'" . ($featuredOnly ? ' AND is_featured = 1' : '') . ' ORDER BY sort_order, title';
    if ($limit) {
        $sql .= ' LIMIT ' . max(1, min(100, $limit));
    }
    return array_map(fn($s) => format_service($s), db_all($sql));
}

function get_service(string $slug): ?array
{
    $s = db_one("SELECT * FROM services WHERE slug = ? AND status = 'published'", [$slug]);
    return $s ? format_service($s, true) : null;
}

function format_post(array $p, bool $full = false): array
{
    $out = [
        'id'           => (int) $p['id'],
        'title'        => $p['title'],
        'slug'         => $p['slug'],
        'excerpt'      => $p['excerpt'] ?: str_limit($p['content'], 180),
        'image'        => upload_url($p['image']),
        'image_alt'    => $p['image_alt'] ?: $p['title'],
        'author_name'  => $p['author_name'],
        'published_at' => $p['published_at'],
    ];
    if ($full) {
        $out['content'] = $p['content'];
        $out['seo'] = [
            'title'       => $p['seo_title'] ?: $p['title'] . ' | ' . setting('company_name'),
            'description' => $p['meta_description'] ?: str_limit($out['excerpt'], 158),
            'keywords'    => $p['meta_keywords'],
            'og_image'    => upload_url($p['image'], true),
        ];
        $out['updated_at'] = $p['updated_at'];
    }
    return $out;
}

function get_posts(int $limit = 12, int $page = 1): array
{
    $limit = max(1, min(50, $limit));
    $offset = max(0, ($page - 1) * $limit);
    $rows = db_all("SELECT * FROM posts WHERE status = 'published' AND (published_at IS NULL OR published_at <= UTC_TIMESTAMP())
                    ORDER BY published_at DESC, id DESC LIMIT $limit OFFSET $offset");
    $total = (int) db_value("SELECT COUNT(*) FROM posts WHERE status = 'published' AND (published_at IS NULL OR published_at <= UTC_TIMESTAMP())");
    return ['items' => array_map(fn($p) => format_post($p), $rows), 'total' => $total, 'page' => $page, 'per_page' => $limit];
}

function get_post(string $slug): ?array
{
    $p = db_one("SELECT * FROM posts WHERE slug = ? AND status = 'published' AND (published_at IS NULL OR published_at <= UTC_TIMESTAMP())", [$slug]);
    return $p ? format_post($p, true) : null;
}

function get_testimonials(): array
{
    return array_map(fn($t) => [
        'id'          => (int) $t['id'],
        'client_name' => $t['client_name'],
        'client_role' => $t['client_role'],
        'company'     => $t['company'],
        'content'     => $t['content'],
        'rating'      => (int) $t['rating'],
    ], db_all("SELECT * FROM testimonials WHERE status = 'published' ORDER BY sort_order, id"));
}

/** SEO record for a static page key, with sensible fallbacks. */
function get_seo(string $key): array
{
    $r = db_one('SELECT * FROM seo_settings WHERE page_key = ?', [$key]) ?? [];
    $company = setting('company_name', 'Quality Enviro & Engineering Services');
    $title = $r['seo_title'] ?? null ?: $company;
    $desc  = $r['meta_description'] ?? null ?: (string) setting('tagline', '');
    return [
        'title'          => $title,
        'description'    => $desc,
        'keywords'       => $r['meta_keywords'] ?? null,
        'canonical'      => $r['canonical_url'] ?? null,
        'og_title'       => ($r['og_title'] ?? null) ?: $title,
        'og_description' => ($r['og_description'] ?? null) ?: $desc,
        'og_image'       => upload_url(($r['og_image'] ?? null) ?: setting('logo'), true),
        'robots'         => ($r['robots'] ?? null) ?: 'index, follow',
    ];
}

/** Everything the SPA needs on first load (one request). */
function site_bootstrap(): array
{
    $settings = public_settings();
    return [
        'settings'     => $settings,
        'social'       => public_social_links(),
        'nav_services' => get_services(false, 12),
    ];
}
