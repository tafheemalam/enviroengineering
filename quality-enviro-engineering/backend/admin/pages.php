<?php
declare(strict_types=1);
require __DIR__ . '/_init.php';
$admin = require_admin();

if (is_post()) {
    verify_csrf();
    if (($_POST['action'] ?? '') === 'toggle_section') {
        db_exec('UPDATE page_sections SET is_visible = 1 - is_visible WHERE id = ?', [(int) ($_POST['id'] ?? 0)]);
        flash('success', 'Section visibility updated.');
    }
    redirect('pages.php');
}

$pages = db_all("SELECT * FROM pages ORDER BY FIELD(page_key, 'home','about','services','contact','quote','insights','privacy','terms'), id");
$sections = [];
foreach (db_all('SELECT id, page_id, section_key, label, title, is_visible, updated_at FROM page_sections ORDER BY sort_order, id') as $sec) {
    $sections[$sec['page_id']][] = $sec;
}
$publicPath = ['home' => '/', 'about' => '/about', 'services' => '/services', 'contact' => '/contact', 'quote' => '/request-a-quote',
    'insights' => '/insights', 'privacy' => '/privacy-policy', 'terms' => '/terms-and-conditions'];

$pageTitle = 'Page Content';
$active = 'pages';
require __DIR__ . '/partials/header.php';
?>
<p class="text-muted">Edit the text, images and lists shown on each page of the website. Changes appear immediately.</p>

<div class="row g-4">
<?php foreach ($pages as $p): ?>
  <div class="col-lg-6">
    <div class="card shadow-sm border-0 h-100">
      <div class="card-header bg-white d-flex align-items-center gap-2">
        <h2 class="h6 mb-0"><?= e($p['title']) ?></h2>
        <?= status_badge($p['status']) ?>
        <a href="<?= e($publicPath[$p['page_key']] ?? '/') ?>" target="_blank" rel="noopener" class="ms-auto small">View <i class="bi bi-box-arrow-up-right"></i></a>
        <?php if (in_array($p['page_key'], ['privacy', 'terms'], true)): ?>
          <a href="page-edit.php?id=<?= (int) $p['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Edit page</a>
        <?php endif; ?>
      </div>
      <?php if (!empty($sections[$p['id']])): ?>
      <ul class="list-group list-group-flush">
        <?php foreach ($sections[$p['id']] as $sec): ?>
          <li class="list-group-item d-flex align-items-center gap-2">
            <div class="me-auto">
              <a href="section-edit.php?id=<?= (int) $sec['id'] ?>" class="fw-semibold"><?= e($sec['label']) ?></a>
              <?php if (!$sec['is_visible']): ?><span class="badge text-bg-secondary ms-1">Hidden</span><?php endif; ?>
              <div class="small text-muted text-truncate" style="max-width:380px"><?= e($sec['title'] ?: '—') ?></div>
            </div>
            <form method="post" class="m-0">
              <?= csrf_field() ?><input type="hidden" name="action" value="toggle_section"><input type="hidden" name="id" value="<?= (int) $sec['id'] ?>">
              <button class="btn btn-sm btn-light" title="<?= $sec['is_visible'] ? 'Hide section' : 'Show section' ?>" aria-label="<?= $sec['is_visible'] ? 'Hide' : 'Show' ?> <?= e($sec['label']) ?>"><i class="bi bi-<?= $sec['is_visible'] ? 'eye' : 'eye-slash' ?>"></i></button>
            </form>
            <a href="section-edit.php?id=<?= (int) $sec['id'] ?>" class="btn btn-sm btn-outline-primary" aria-label="Edit <?= e($sec['label']) ?>"><i class="bi bi-pencil"></i></a>
          </li>
        <?php endforeach; ?>
      </ul>
      <?php elseif (in_array($p['page_key'], ['privacy', 'terms'], true)): ?>
        <div class="card-body small text-muted">Rich-text page. Last updated <?= e(format_dt($p['updated_at'])) ?>.</div>
      <?php endif; ?>
    </div>
  </div>
<?php endforeach; ?>
</div>
<?php require __DIR__ . '/partials/footer.php';
