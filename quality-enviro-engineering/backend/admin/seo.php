<?php
declare(strict_types=1);
require __DIR__ . '/_init.php';
$admin = require_admin();

$pagesMap = [
    'home' => ['Homepage', '/'], 'about' => ['About Us', '/about'], 'services' => ['Services listing', '/services'],
    'contact' => ['Contact Us', '/contact'], 'quote' => ['Request a Quote', '/request-a-quote'], 'insights' => ['Insights', '/insights'],
    'privacy' => ['Privacy Policy', '/privacy-policy'], 'terms' => ['Terms & Conditions', '/terms-and-conditions'],
];
$robotsOptions = ['index, follow', 'noindex, follow', 'index, nofollow', 'noindex, nofollow'];
$key = array_key_exists($_GET['page'] ?? '', $pagesMap) ? $_GET['page'] : 'home';
$row = db_one('SELECT * FROM seo_settings WHERE page_key = ?', [$key]) ?? [
    'seo_title' => '', 'meta_description' => '', 'meta_keywords' => '', 'canonical_url' => '', 'og_title' => '', 'og_description' => '', 'og_image' => null, 'robots' => 'index, follow',
];
$errors = [];

if (is_post()) {
    verify_csrf();
    $data = [
        'seo_title'        => clean_str($_POST['seo_title'] ?? '', 190),
        'meta_description' => clean_str($_POST['meta_description'] ?? '', 320),
        'meta_keywords'    => clean_str($_POST['meta_keywords'] ?? '', 320),
        'canonical_url'    => clean_str($_POST['canonical_url'] ?? '', 255),
        'og_title'         => clean_str($_POST['og_title'] ?? '', 190),
        'og_description'   => clean_str($_POST['og_description'] ?? '', 320),
        'robots'           => in_array($_POST['robots'] ?? '', $robotsOptions, true) ? $_POST['robots'] : 'index, follow',
    ];
    if ($data['canonical_url'] !== '' && !preg_match('#^https?://#i', $data['canonical_url'])) {
        $errors[] = 'Canonical URL must be a full URL starting with https://';
    }
    if (!$errors) {
        try {
            $data['og_image'] = process_image_field('og_image', $row['og_image'], 'pages', $admin);
        } catch (RuntimeException $ex) {
            $errors[] = $ex->getMessage();
        }
    }
    if (!$errors) {
        $cols = array_keys($data);
        $vals = array_map(fn($v) => $v === '' ? null : $v, array_values($data));
        db_exec('INSERT INTO seo_settings (page_key, ' . implode(', ', $cols) . ') VALUES (?' . str_repeat(', ?', count($cols)) . ')
                 ON DUPLICATE KEY UPDATE ' . implode(', ', array_map(fn($c) => "$c = VALUES($c)", $cols)), [$key, ...$vals]);
        flash('success', 'SEO settings saved for ' . $pagesMap[$key][0] . '.');
        redirect('seo.php?page=' . $key);
    }
    $row = array_merge($row, $data);
}

$pageTitle = 'SEO Management';
$active = 'seo';
require __DIR__ . '/partials/header.php';
$previewTitle = $row['seo_title'] ?: setting('company_name');
$previewDesc = $row['meta_description'] ?: setting('tagline');
?>
<div class="row g-4">
  <div class="col-lg-3">
    <div class="list-group shadow-sm">
      <?php foreach ($pagesMap as $k => [$label]): ?>
        <a href="seo.php?page=<?= $k ?>" class="list-group-item list-group-item-action<?= $k === $key ? ' active' : '' ?>"<?= $k === $key ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
      <?php endforeach; ?>
    </div>
    <div class="card shadow-sm border-0 mt-4">
      <div class="card-body small">
        <h2 class="h6">Automatic SEO</h2>
        <ul class="ps-3 mb-2">
          <li><a href="/sitemap.xml" target="_blank" rel="noopener">XML sitemap</a> (auto-updated)</li>
          <li><a href="/robots.txt" target="_blank" rel="noopener">robots.txt</a></li>
          <li>Organization, LocalBusiness, Service, Article &amp; Breadcrumb structured data</li>
          <li>Canonical, Open Graph &amp; Twitter tags</li>
        </ul>
        <p class="text-muted mb-0">Individual services and insights have their own SEO fields on their edit screens.</p>
      </div>
    </div>
  </div>
  <div class="col-lg-9">
    <?php foreach ($errors as $err): ?><div class="alert alert-danger py-2"><?= e($err) ?></div><?php endforeach; ?>
    <form method="post" enctype="multipart/form-data" class="card shadow-sm border-0">
      <?= csrf_field() ?>
      <div class="card-header bg-white d-flex align-items-center">
        <h2 class="h6 mb-0"><?= e($pagesMap[$key][0]) ?></h2>
        <a href="<?= e($pagesMap[$key][1]) ?>" target="_blank" rel="noopener" class="ms-auto small">View page <i class="bi bi-box-arrow-up-right"></i></a>
      </div>
      <div class="card-body">
        <div class="serp-preview mb-4" aria-label="Google search preview">
          <div class="serp-url"><?= e(site_url($pagesMap[$key][1])) ?></div>
          <div class="serp-title" id="serpTitle"><?= e($previewTitle) ?></div>
          <div class="serp-desc" id="serpDesc"><?= e($previewDesc) ?></div>
        </div>
        <div class="mb-3"><label class="form-label fw-semibold" for="seo_title">SEO title</label><input class="form-control" id="seo_title" name="seo_title" value="<?= e($row['seo_title']) ?>" maxlength="190" data-counter data-ideal="60" data-preview="#serpTitle"><div class="form-text">Ideal: 50–60 characters, primary keyword near the start.</div></div>
        <div class="mb-3"><label class="form-label fw-semibold" for="meta_description">Meta description</label><textarea class="form-control" id="meta_description" name="meta_description" rows="3" maxlength="320" data-counter data-ideal="160" data-preview="#serpDesc"><?= e($row['meta_description']) ?></textarea><div class="form-text">Ideal: 120–160 characters. Write for people – it's your search-result pitch.</div></div>
        <div class="mb-3"><label class="form-label fw-semibold" for="meta_keywords">Meta keywords</label><input class="form-control" id="meta_keywords" name="meta_keywords" value="<?= e($row['meta_keywords']) ?>" maxlength="320" placeholder="comma, separated"></div>
        <div class="row g-3 mb-3">
          <div class="col-md-8"><label class="form-label fw-semibold" for="canonical_url">Canonical URL</label><input class="form-control" id="canonical_url" name="canonical_url" value="<?= e($row['canonical_url']) ?>" maxlength="255" placeholder="<?= e(site_url($pagesMap[$key][1])) ?>"><div class="form-text">Leave blank to use the page's own URL (recommended).</div></div>
          <div class="col-md-4"><label class="form-label fw-semibold" for="robots">Robots</label>
            <select class="form-select" id="robots" name="robots"><?php foreach ($robotsOptions as $o): ?><option<?= $row['robots'] === $o ? ' selected' : '' ?>><?= $o ?></option><?php endforeach; ?></select></div>
        </div>
        <hr>
        <h3 class="h6 mb-3">Social sharing (Open Graph)</h3>
        <div class="mb-3"><label class="form-label fw-semibold" for="og_title">OG title</label><input class="form-control" id="og_title" name="og_title" value="<?= e($row['og_title']) ?>" maxlength="190" placeholder="Defaults to SEO title"></div>
        <div class="mb-3"><label class="form-label fw-semibold" for="og_description">OG description</label><textarea class="form-control" id="og_description" name="og_description" rows="2" maxlength="320" placeholder="Defaults to meta description"><?= e($row['og_description']) ?></textarea></div>
        <?= image_field('og_image', $row['og_image'], 'OG image', 'Recommended 1200×630 px. Defaults to the company logo.') ?>
      </div>
      <div class="card-footer bg-white"><button class="btn btn-brand"><i class="bi bi-check2"></i> Save SEO settings</button></div>
    </form>
  </div>
</div>
<?= media_library_modal() ?>
<?php require __DIR__ . '/partials/footer.php';
