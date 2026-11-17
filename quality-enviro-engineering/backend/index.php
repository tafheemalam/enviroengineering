<?php
/**
 * SEO-aware SPA shell.
 *
 * Serves the built React app (build/index.html) for every public route, but
 * first injects – server side, from the database – the page <title>, meta
 * description, canonical, robots, Open Graph / Twitter tags, JSON-LD
 * structured data, analytics and a crawlable HTML fallback of the main content.
 * Search engines therefore receive complete metadata without needing JS,
 * while visitors get the fast React experience.
 */
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
require_once QES_ROOT . '/includes/repository.php';

$template = __DIR__ . '/build/index.html';
if (!is_file($template)) {
    http_response_code(503);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><meta charset="utf-8"><title>Build required</title><body style="font-family:sans-serif;padding:40px">'
        . '<h1>Frontend not built</h1><p>Run <code>npm run build</code> in <code>/frontend</code>. It outputs to <code>/backend/build</code>.</p>'
        . '<p>Admin panel: <a href="/admin/">/admin</a></p></body>';
    exit;
}

$path = '/' . trim(rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'), '/');
$segments = $path === '/' ? [] : explode('/', trim($path, '/'));
$company = setting('company_name', 'Quality Enviro & Engineering Services');
$status = 200;
$crumbs = [['Home', '/']];
$extraSchema = [];
$content = '';   // crawlable fallback HTML (replaced by React on load)

try {
    $settings = public_settings();
    $seo = null;

    switch ($segments[0] ?? '') {
        case '':
            $seo = get_seo('home');
            $home = get_page('home');
            $hero = $home['sections']->hero ?? null;
            $content = '<h1>' . e($hero['title'] ?? $company) . '</h1>' . (isset($hero['subtitle']) ? '<p>' . e($hero['subtitle']) . '</p>' : '');
            $content .= services_list_html(get_services(false));
            break;

        case 'about':
            $seo = get_seo('about');
            $page = get_page('about');
            $crumbs[] = ['About Us', '/about'];
            $content = '<h1>' . e($page['sections']->intro['title'] ?? 'About Us') . '</h1>';
            foreach ((array) ($page['sections'] ?? []) as $s) {
                foreach ($s['paragraphs'] as $p) {
                    $content .= '<p>' . e($p) . '</p>';
                }
            }
            break;

        case 'services':
            if (isset($segments[1])) {
                $slug = $segments[1];
                $row = db_one("SELECT slug, page_type FROM services WHERE slug = ? AND status = 'published'", [$slug]);
                if ($row && $row['page_type'] === 'contact') {
                    header('Location: /contact?service=' . rawurlencode($slug), true, 301);
                    exit;
                }
                $service = $row ? get_service($slug) : null;
                if (!$service || count($segments) > 2) {
                    $status = 404;
                    break;
                }
                $seo = [
                    'title' => $service['seo']['title'], 'description' => $service['seo']['description'],
                    'keywords' => $service['seo']['keywords'], 'canonical' => null,
                    'og_title' => $service['title'], 'og_description' => $service['seo']['description'],
                    'og_image' => $service['seo']['og_image'], 'robots' => 'index, follow',
                ];
                $crumbs[] = ['Services', '/services'];
                $crumbs[] = [$service['title'], '/services/' . $service['slug']];
                $extraSchema[] = [
                    '@context' => 'https://schema.org', '@type' => 'Service',
                    'name' => $service['title'], 'description' => $service['short_description'],
                    'serviceType' => $service['title'],
                    'url' => site_url('/services/' . $service['slug']),
                    'image' => upload_url($service['image'], true),
                    'provider' => ['@type' => 'Organization', 'name' => $company, 'url' => site_url('/')],
                ];
                $content = '<h1>' . e($service['title']) . '</h1><p>' . e($service['short_description']) . '</p>' . $service['full_description'];
                break;
            }
            $seo = get_seo('services');
            $crumbs[] = ['Services', '/services'];
            $content = '<h1>Environmental &amp; Engineering Services</h1>' . services_list_html(get_services(false));
            break;

        case 'contact':
            $seo = get_seo('contact');
            $crumbs[] = ['Contact Us', '/contact'];
            $content = '<h1>Contact Us</h1><p>' . e($settings['address'] ?? '') . '</p><p>' . e($settings['phone'] ?? '') . ' · ' . e($settings['company_email'] ?? '') . '</p>';
            break;

        case 'request-a-quote':
            $seo = get_seo('quote');
            $crumbs[] = ['Request a Quote', '/request-a-quote'];
            $content = '<h1>Request a Quotation</h1>';
            break;

        case 'insights':
            if (!$settings['show_insights']) {
                $status = 404;
                break;
            }
            if (isset($segments[1])) {
                $post = get_post($segments[1]);
                if (!$post || count($segments) > 2) {
                    $status = 404;
                    break;
                }
                $seo = [
                    'title' => $post['seo']['title'], 'description' => $post['seo']['description'],
                    'keywords' => $post['seo']['keywords'], 'canonical' => null,
                    'og_title' => $post['title'], 'og_description' => $post['seo']['description'],
                    'og_image' => $post['seo']['og_image'] ?: upload_url(setting('logo'), true), 'robots' => 'index, follow',
                    'og_type' => 'article',
                ];
                $crumbs[] = ['Insights', '/insights'];
                $crumbs[] = [$post['title'], '/insights/' . $post['slug']];
                $extraSchema[] = [
                    '@context' => 'https://schema.org', '@type' => 'Article',
                    'headline' => $post['title'], 'description' => $post['seo']['description'],
                    'image' => $post['seo']['og_image'],
                    'datePublished' => $post['published_at'] ? gmdate('c', strtotime($post['published_at'] . ' UTC')) : null,
                    'dateModified' => gmdate('c', strtotime($post['updated_at'] . ' UTC')),
                    'author' => ['@type' => 'Organization', 'name' => $post['author_name'] ?: $company],
                    'publisher' => ['@type' => 'Organization', 'name' => $company, 'logo' => ['@type' => 'ImageObject', 'url' => upload_url(setting('logo'), true)]],
                    'mainEntityOfPage' => site_url('/insights/' . $post['slug']),
                ];
                $content = '<h1>' . e($post['title']) . '</h1>' . $post['content'];
                break;
            }
            $seo = get_seo('insights');
            $crumbs[] = ['Insights', '/insights'];
            $content = '<h1>Insights &amp; Updates</h1><ul>';
            foreach (get_posts(20)['items'] as $p) {
                $content .= '<li><a href="/insights/' . e($p['slug']) . '">' . e($p['title']) . '</a></li>';
            }
            $content .= '</ul>';
            break;

        case 'privacy-policy':
        case 'terms-and-conditions':
            $key = $segments[0] === 'privacy-policy' ? 'privacy' : 'terms';
            $page = get_page($key);
            if (!$page || count($segments) > 1) {
                $status = 404;
                break;
            }
            $seo = get_seo($key);
            $crumbs[] = [$page['page']['title'], '/' . $segments[0]];
            $content = '<h1>' . e($page['page']['title']) . '</h1>' . $page['page']['content'];
            break;

        default:
            $status = 404;
    }
} catch (Throwable $e) {
    error_log('[QES shell] ' . $e->getMessage());
    $settings = ['company_name' => $company];
    $seo = null;
    $status = 200; // still serve the app; it will show its own error state
}

if ($status === 404) {
    $seo = [
        'title' => 'Page Not Found | ' . $company, 'description' => 'The page you are looking for could not be found.',
        'keywords' => null, 'canonical' => null, 'og_title' => 'Page Not Found', 'og_description' => '',
        'og_image' => upload_url(setting('logo'), true), 'robots' => 'noindex, follow',
    ];
    $crumbs = [];
    $content = '<h1>Page not found</h1><p><a href="/">Return to the homepage</a></p>';
}
$seo ??= [
    'title' => $company, 'description' => (string) setting('tagline', ''), 'keywords' => null, 'canonical' => null,
    'og_title' => $company, 'og_description' => '', 'og_image' => null, 'robots' => 'index, follow',
];

// ---- Build <head> tags ----------------------------------------------
$canonical = $seo['canonical'] ?: site_url($path === '/' ? '/' : $path);
$head = [];
$head[] = '<title>' . e($seo['title']) . '</title>';
$head[] = '<meta name="description" content="' . e($seo['description']) . '">';
if (!empty($seo['keywords'])) {
    $head[] = '<meta name="keywords" content="' . e($seo['keywords']) . '">';
}
$head[] = '<meta name="robots" content="' . e($seo['robots']) . '">';
if ($status === 200) {
    $head[] = '<link rel="canonical" href="' . e($canonical) . '">';
}
$head[] = '<meta property="og:type" content="' . e($seo['og_type'] ?? 'website') . '">';
$head[] = '<meta property="og:site_name" content="' . e($company) . '">';
$head[] = '<meta property="og:title" content="' . e($seo['og_title']) . '">';
$head[] = '<meta property="og:description" content="' . e($seo['og_description']) . '">';
$head[] = '<meta property="og:url" content="' . e($canonical) . '">';
if (!empty($seo['og_image'])) {
    $head[] = '<meta property="og:image" content="' . e($seo['og_image']) . '">';
    $head[] = '<meta name="twitter:image" content="' . e($seo['og_image']) . '">';
}
$head[] = '<style>.seo-fallback{max-width:1100px;margin:0 auto;padding:140px 16px 60px;font-family:system-ui,sans-serif;color:#1f2d3d;line-height:1.6}.seo-fallback a{color:#1b6b34}</style>';
$head[] = '<meta name="twitter:card" content="summary_large_image">';
$head[] = '<meta name="twitter:title" content="' . e($seo['og_title']) . '">';
$head[] = '<meta name="twitter:description" content="' . e($seo['og_description']) . '">';

$favicon = upload_url(setting('favicon') ?: setting('logo'));
if ($favicon) {
    $head[] = '<link rel="icon" href="' . e($favicon) . '">';
    $head[] = '<link rel="apple-touch-icon" href="' . e($favicon) . '">';
}
if ($v = setting('gsc_verification')) {
    $head[] = '<meta name="google-site-verification" content="' . e($v) . '">';
}

// Structured data: Organization / LocalBusiness (every page)
$sameAs = array_column(array_filter(public_social_links(), fn($l) => $l['platform'] !== 'whatsapp'), 'url');
$org = [
    '@context' => 'https://schema.org',
    '@type' => ['Organization', 'LocalBusiness'],
    '@id' => site_url('/#organization'),
    'name' => $company,
    'url' => site_url('/'),
    'logo' => upload_url(setting('logo'), true),
    'image' => upload_url(setting('logo'), true),
    'description' => $settings['footer_description'] ?? setting('tagline'),
    'email' => setting('company_email'),
    'telephone' => setting('phone'),
    'areaServed' => setting('service_area'),
];
if (str_ends_with((string) setting('company_email'), '@example.com')) {
    unset($org['email']); // never publish the placeholder address in structured data
}
$addr = (string) setting('address', '');
if ($addr !== '' && stripos($addr, 'PLACEHOLDER') === false) {
    $org['address'] = ['@type' => 'PostalAddress', 'streetAddress' => $addr];
}
if ($sameAs) {
    $org['sameAs'] = array_values($sameAs);
}
// Site-wide schemas are kept by the SPA (data-keep); page-level ones are re-rendered by React Helmet.
$keepSchemas = [array_filter($org, fn($v) => $v !== null && $v !== '')];
if ($path === '/') {
    $keepSchemas[] = ['@context' => 'https://schema.org', '@type' => 'WebSite', 'name' => $company, 'url' => site_url('/')];
}
foreach ($keepSchemas as $schema) {
    $head[] = '<script type="application/ld+json" data-keep>' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) . '</script>';
}
$schemas = [];
if (count($crumbs) > 1) {
    $schemas[] = [
        '@context' => 'https://schema.org', '@type' => 'BreadcrumbList',
        'itemListElement' => array_map(fn($c, $i) => [
            '@type' => 'ListItem', 'position' => $i + 1, 'name' => $c[0], 'item' => site_url($c[1]),
        ], $crumbs, array_keys($crumbs)),
    ];
}
$schemas = array_merge($schemas, $extraSchema);
foreach ($schemas as $schema) {
    // JSON_HEX_TAG prevents "</script>" breaking out of the block
    $head[] = '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) . '</script>';
}

// Google Analytics (GA4)
$ga = (string) setting('ga_measurement_id', '');
if (preg_match('/^G-[A-Z0-9]{4,20}$/', $ga)) {
    $head[] = '<script async src="https://www.googletagmanager.com/gtag/js?id=' . e($ga) . '"></script>';
    $head[] = '<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag("js",new Date());gtag("config",' . json_encode($ga) . ');</script>';
}

// Bootstrap data so the SPA renders without an extra API round-trip
$boot = null;
try {
    $boot = site_bootstrap();
} catch (Throwable $e) {
}
$bootScript = '<script>window.__QES_BOOT__=' . json_encode($boot, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';</script>';

$html = file_get_contents($template);
// remove the static <title> from the template; ours replaces it
$html = preg_replace('#<title>.*?</title>#s', '', $html, 1);
$html = str_replace('<!--SEO-->', implode("\n    ", $head) . "\n    " . $bootScript, $html);
$fallback = '<div class="seo-fallback">' . $content . '</div>';
$html = str_replace('<!--APP-->', $fallback, $html);

http_response_code($status);
header('Content-Type: text/html; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Cache-Control: no-cache');
echo $html;

// ---------------------------------------------------------------------
function services_list_html(array $services): string
{
    $out = '<h2>Our Services</h2><ul>';
    foreach ($services as $s) {
        $href = $s['page_type'] === 'detail' ? '/services/' . $s['slug'] : '/contact?service=' . $s['slug'];
        $out .= '<li><a href="' . e($href) . '">' . e($s['title']) . '</a> – ' . e($s['short_description']) . '</li>';
    }
    return $out . '</ul>';
}
