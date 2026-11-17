<?php
/** Dynamic robots.txt – served at /robots.txt */
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: public, max-age=3600');

echo "User-agent: *\n";
echo "Disallow: /admin/\n";
echo "Disallow: /api/\n";
echo "Allow: /\n\n";
echo 'Sitemap: ' . site_url('/sitemap.xml') . "\n";
