<?php
/** Dynamic XML sitemap – served at /sitemap.xml */
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/xml; charset=utf-8');
header('Cache-Control: public, max-age=3600');

$urls = [];
$add = function (string $path, ?string $lastmod, string $freq, string $prio) use (&$urls) {
    $urls[] = [site_url($path), $lastmod ? gmdate('Y-m-d', strtotime($lastmod . ' UTC')) : null, $freq, $prio];
};

$pageMap = ['home' => '/', 'about' => '/about', 'services' => '/services', 'contact' => '/contact', 'quote' => '/request-a-quote'];
if (setting('show_insights', '1') === '1') {
    $pageMap['insights'] = '/insights';
}
$pages = [];
foreach (db_all("SELECT page_key, updated_at FROM pages WHERE status = 'published'") as $p) {
    $pages[$p['page_key']] = $p['updated_at'];
}
// skip pages marked noindex in SEO settings
$noindex = array_column(db_all("SELECT page_key FROM seo_settings WHERE robots LIKE '%noindex%'"), 'page_key');

foreach ($pageMap as $key => $path) {
    if (isset($pages[$key]) && !in_array($key, $noindex, true)) {
        $add($path, $pages[$key], $key === 'home' ? 'weekly' : 'monthly', $key === 'home' ? '1.0' : '0.8');
    }
}
foreach (db_all("SELECT slug, updated_at FROM services WHERE status = 'published' AND page_type = 'detail' ORDER BY sort_order") as $s) {
    $add('/services/' . $s['slug'], $s['updated_at'], 'monthly', '0.9');
}
if (setting('show_insights', '1') === '1') {
    foreach (db_all("SELECT slug, updated_at FROM posts WHERE status = 'published' AND (published_at IS NULL OR published_at <= UTC_TIMESTAMP()) ORDER BY published_at DESC") as $p) {
        $add('/insights/' . $p['slug'], $p['updated_at'], 'yearly', '0.6');
    }
}
foreach (['privacy' => '/privacy-policy', 'terms' => '/terms-and-conditions'] as $key => $path) {
    if (isset($pages[$key]) && !in_array($key, $noindex, true)) {
        $add($path, $pages[$key], 'yearly', '0.3');
    }
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as [$loc, $lastmod, $freq, $prio]) {
    echo '  <url><loc>' . htmlspecialchars($loc, ENT_XML1) . '</loc>'
        . ($lastmod ? "<lastmod>$lastmod</lastmod>" : '')
        . "<changefreq>$freq</changefreq><priority>$prio</priority></url>\n";
}
echo '</urlset>' . "\n";
