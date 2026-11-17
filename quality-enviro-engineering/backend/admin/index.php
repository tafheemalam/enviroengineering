<?php
declare(strict_types=1);
require __DIR__ . '/_init.php';
$admin = require_admin();

$stats = [
    'services'  => (int) db_value('SELECT COUNT(*) FROM services'),
    'published' => (int) db_value("SELECT COUNT(*) FROM services WHERE status = 'published'"),
    'inquiries' => (int) db_value('SELECT COUNT(*) FROM contact_inquiries'),
    'unread'    => (int) db_value('SELECT COUNT(*) FROM contact_inquiries WHERE is_read = 0'),
    'quotes'    => (int) db_value("SELECT COUNT(*) FROM contact_inquiries WHERE inquiry_type = 'quote'"),
    'month'     => (int) db_value('SELECT COUNT(*) FROM contact_inquiries WHERE created_at >= UTC_TIMESTAMP() - INTERVAL 30 DAY'),
    'posts'     => (int) db_value("SELECT COUNT(*) FROM posts WHERE status = 'published'"),
];
$recent = db_all('SELECT id, inquiry_type, full_name, email, service_title, subject, status, is_read, created_at FROM contact_inquiries ORDER BY created_at DESC LIMIT 8');
$byStatus = [];
foreach (db_all('SELECT status, COUNT(*) c FROM contact_inquiries GROUP BY status') as $r) {
    $byStatus[$r['status']] = (int) $r['c'];
}

// Setup checklist – things still using placeholder values
$checks = [
    ['Company phone number', setting('phone') && setting('phone') !== '+00 000 000 0000', 'settings.php'],
    ['Company email address', setting('company_email') && !str_ends_with((string) setting('company_email'), '@example.com'), 'settings.php'],
    ['Office address', setting('address') && stripos((string) setting('address'), 'PLACEHOLDER') === false, 'settings.php'],
    ['Google Maps location', (bool) setting('map_embed_url'), 'settings.php#contact'],
    ['SMTP email delivery', (bool) setting('smtp_host'), 'settings.php#email'],
    ['Website URL (for SEO)', !str_contains((string) setting('site_url'), 'localhost') || str_contains((string) config('app.url'), 'https://'), 'settings.php'],
    ['At least one social link', (int) db_value("SELECT COUNT(*) FROM social_links WHERE url <> '' AND is_active = 1") > 0, 'social.php'],
    ['About page background (remove placeholder)', !db_value("SELECT COUNT(*) FROM page_sections WHERE content LIKE '%[PLACEHOLDER]%'"), 'pages.php'],
];
$pending = array_filter($checks, fn($c) => !$c[1]);

$pageTitle = 'Dashboard';
$active = 'dashboard';
require __DIR__ . '/partials/header.php';
?>
<p class="text-muted mb-4">Welcome back, <?= e($admin['name']) ?>. Here's what's happening on your website.</p>

<div class="row g-3 mb-4">
  <?php
  $cards = [
      ['Total Services', $stats['services'], 'gear-wide-connected', 'green', 'services.php'],
      ['Published Services', $stats['published'], 'check2-circle', 'navy', 'services.php?status=published'],
      ['Total Inquiries', $stats['inquiries'], 'envelope-paper', 'teal', 'inquiries.php'],
      ['Unread Inquiries', $stats['unread'], 'envelope-exclamation', 'orange', 'inquiries.php?read=0'],
  ];
  foreach ($cards as [$label, $value, $icon, $color, $link]): ?>
    <div class="col-sm-6 col-xl-3">
      <a href="<?= $link ?>" class="stat-card stat-<?= $color ?>">
        <span class="stat-icon"><i class="bi bi-<?= $icon ?>" aria-hidden="true"></i></span>
        <span><span class="stat-value"><?= number_format($value) ?></span><span class="stat-label"><?= e($label) ?></span></span>
      </a>
    </div>
  <?php endforeach; ?>
</div>

<div class="row g-4">
  <div class="col-xl-8">
    <div class="card shadow-sm border-0 h-100">
      <div class="card-header bg-white d-flex align-items-center">
        <h2 class="h6 mb-0">Recent Contact Submissions</h2>
        <a href="inquiries.php" class="btn btn-sm btn-outline-secondary ms-auto">View all</a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light"><tr><th>Name</th><th>Service / Subject</th><th>Status</th><th>Received</th></tr></thead>
          <tbody>
          <?php if (!$recent): ?>
            <tr><td colspan="4" class="text-center text-muted py-4">No inquiries yet.</td></tr>
          <?php endif; ?>
          <?php foreach ($recent as $r): ?>
            <tr class="<?= $r['is_read'] ? '' : 'fw-semibold' ?>">
              <td>
                <a href="inquiry.php?id=<?= (int) $r['id'] ?>" class="stretched-link-row"><?= e($r['full_name']) ?></a>
                <?php if (!$r['is_read']): ?><span class="badge text-bg-danger ms-1">New</span><?php endif; ?>
                <?php if ($r['inquiry_type'] === 'quote'): ?><span class="badge text-bg-warning ms-1">Quote</span><?php endif; ?>
                <div class="small text-muted fw-normal"><?= e($r['email']) ?></div>
              </td>
              <td><div class="small"><?= e($r['service_title'] ?: '—') ?></div><div class="small text-muted fw-normal text-truncate" style="max-width:260px"><?= e($r['subject']) ?></div></td>
              <td><?= status_badge($r['status']) ?></td>
              <td class="small text-muted text-nowrap"><?= e(format_dt($r['created_at'])) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-xl-4">
    <div class="card shadow-sm border-0 mb-4">
      <div class="card-header bg-white"><h2 class="h6 mb-0">Quick Links</h2></div>
      <div class="card-body d-grid gap-2">
        <a href="service-edit.php" class="btn btn-brand"><i class="bi bi-plus-lg"></i> Add New Service</a>
        <a href="post-edit.php" class="btn btn-outline-secondary"><i class="bi bi-journal-plus"></i> Write an Insight</a>
        <a href="pages.php" class="btn btn-outline-secondary"><i class="bi bi-layout-text-window"></i> Edit Page Content</a>
        <a href="settings.php" class="btn btn-outline-secondary"><i class="bi bi-sliders"></i> Website Settings</a>
        <a href="inquiries.php?export=csv" class="btn btn-outline-secondary"><i class="bi bi-filetype-csv"></i> Export Inquiries (CSV)</a>
      </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
      <div class="card-header bg-white"><h2 class="h6 mb-0">Inquiry Overview</h2></div>
      <ul class="list-group list-group-flush">
        <li class="list-group-item d-flex justify-content-between">Last 30 days <strong><?= $stats['month'] ?></strong></li>
        <li class="list-group-item d-flex justify-content-between">Quotation requests <strong><?= $stats['quotes'] ?></strong></li>
        <?php foreach (['new' => 'New', 'in_progress' => 'In Progress', 'responded' => 'Responded', 'closed' => 'Closed'] as $k => $label): ?>
          <li class="list-group-item d-flex justify-content-between"><a href="inquiries.php?status=<?= $k ?>"><?= $label ?></a> <strong><?= $byStatus[$k] ?? 0 ?></strong></li>
        <?php endforeach; ?>
        <li class="list-group-item d-flex justify-content-between">Published insights <strong><?= $stats['posts'] ?></strong></li>
      </ul>
    </div>

    <?php if ($pending): ?>
    <div class="card shadow-sm border-0 border-start border-warning border-4">
      <div class="card-header bg-white"><h2 class="h6 mb-0"><i class="bi bi-list-check text-warning"></i> Setup Checklist</h2></div>
      <ul class="list-group list-group-flush">
        <?php foreach ($pending as [$label, , $link]): ?>
          <li class="list-group-item d-flex align-items-center"><i class="bi bi-circle me-2 text-muted"></i><?= e($label) ?><a href="<?= $link ?>" class="ms-auto small">Fix</a></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/partials/footer.php';
