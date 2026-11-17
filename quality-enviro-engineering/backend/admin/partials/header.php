<?php
/** @var string $pageTitle  @var string $active  @var array $admin */
$pageTitle = $pageTitle ?? 'Dashboard';
$active = $active ?? '';
$unread = (int) db_value('SELECT COUNT(*) FROM contact_inquiries WHERE is_read = 0');
$company = setting('company_name', 'QES');
$logo = upload_url(setting('logo'));
$nav = [
    ['index.php', 'dashboard', 'speedometer2', 'Dashboard'],
    ['inquiries.php', 'inquiries', 'envelope-paper', 'Inquiries', $unread],
    ['services.php', 'services', 'gear-wide-connected', 'Services'],
    ['pages.php', 'pages', 'layout-text-window', 'Page Content'],
    ['posts.php', 'posts', 'journal-richtext', 'Insights'],
    ['testimonials.php', 'testimonials', 'chat-quote', 'Testimonials'],
    ['media.php', 'media', 'images', 'Media'],
    ['seo.php', 'seo', 'search', 'SEO'],
    ['social.php', 'social', 'share', 'Social Links'],
    ['settings.php', 'settings', 'sliders', 'Settings'],
];
if (($admin['role'] ?? '') === 'super_admin') {
    $nav[] = ['admins.php', 'admins', 'people', 'Admin Users'];
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<title><?= e($pageTitle) ?> · <?= e($company) ?> Admin</title>
<?php if ($logo): ?><link rel="icon" href="<?= e($logo) ?>"><?php endif; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="assets/admin.css?v=1" rel="stylesheet">
</head>
<body>
<a href="#admin-main" class="visually-hidden-focusable btn btn-dark position-absolute m-2" style="z-index:2000">Skip to content</a>
<div class="admin-shell">
  <aside class="admin-sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="adminSidebar" aria-label="Admin navigation">
    <div class="sidebar-brand">
      <?php if ($logo): ?><img src="<?= e($logo) ?>" alt="" width="40" height="36"><?php endif; ?>
      <div><strong><?= e(setting('company_short', 'QES')) ?></strong><small>Admin Panel</small></div>
      <button type="button" class="btn-close btn-close-white ms-auto d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#adminSidebar" aria-label="Close menu"></button>
    </div>
    <nav>
      <ul class="nav flex-column">
        <?php foreach ($nav as $item): ?>
          <li class="nav-item">
            <a class="nav-link<?= $active === $item[1] ? ' active' : '' ?>" href="<?= $item[0] ?>"<?= $active === $item[1] ? ' aria-current="page"' : '' ?>>
              <i class="bi bi-<?= $item[2] ?>" aria-hidden="true"></i> <?= e($item[3]) ?>
              <?php if (!empty($item[4])): ?><span class="badge rounded-pill text-bg-danger ms-auto"><?= (int) $item[4] ?></span><?php endif; ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </nav>
    <div class="sidebar-footer">
      <a href="/" target="_blank" rel="noopener" class="nav-link"><i class="bi bi-box-arrow-up-right" aria-hidden="true"></i> View website</a>
    </div>
  </aside>

  <div class="admin-main">
    <header class="admin-topbar">
      <button class="btn btn-light d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-controls="adminSidebar" aria-label="Open menu"><i class="bi bi-list"></i></button>
      <h1 class="h5 mb-0 text-truncate"><?= e($pageTitle) ?></h1>
      <div class="ms-auto d-flex align-items-center gap-2">
        <a href="inquiries.php?read=0" class="btn btn-light position-relative" aria-label="Unread inquiries: <?= $unread ?>">
          <i class="bi bi-bell"></i>
          <?php if ($unread): ?><span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-danger"><?= $unread ?></span><?php endif; ?>
        </a>
        <div class="dropdown">
          <button class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle"></i> <span class="d-none d-sm-inline"><?= e($admin['name'] ?? '') ?></span>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><span class="dropdown-item-text small text-muted"><?= e($admin['email'] ?? '') ?><br><?= e(ucwords(str_replace('_', ' ', $admin['role'] ?? ''))) ?></span></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-key"></i> Profile &amp; password</a></li>
            <li>
              <form method="post" action="logout.php"><?= csrf_field() ?><button class="dropdown-item text-danger" type="submit"><i class="bi bi-box-arrow-right"></i> Sign out</button></form>
            </li>
          </ul>
        </div>
      </div>
    </header>

    <main id="admin-main" class="admin-content" tabindex="-1">
      <?php foreach (take_flashes() as [$type, $msg]): ?>
        <div class="alert alert-<?= e($type) ?> alert-dismissible fade show" role="alert">
          <?= e($msg) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endforeach; ?>
