<?php
declare(strict_types=1);
require __DIR__ . '/_init.php';
$admin = require_admin();

if (is_post()) {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);
    switch ($action) {
        case 'delete':
            $s = db_one('SELECT title FROM services WHERE id = ?', [$id]);
            if ($s) {
                db_exec('DELETE FROM services WHERE id = ?', [$id]);
                flash('success', 'Service "' . $s['title'] . '" deleted. Existing inquiries keep the service name.');
            }
            break;
        case 'toggle_status':
            db_exec("UPDATE services SET status = IF(status = 'published', 'draft', 'published') WHERE id = ?", [$id]);
            flash('success', 'Service status updated.');
            break;
        case 'toggle_featured':
            db_exec('UPDATE services SET is_featured = 1 - is_featured WHERE id = ?', [$id]);
            flash('success', 'Featured setting updated.');
            break;
        case 'order':
            $orders = $_POST['order'] ?? [];
            if (is_array($orders)) {
                $st = db()->prepare('UPDATE services SET sort_order = ? WHERE id = ?');
                foreach ($orders as $sid => $ord) {
                    $st->execute([(int) $ord, (int) $sid]);
                }
                flash('success', 'Display order saved.');
            }
            break;
    }
    redirect('services.php' . (!empty($_GET) ? '?' . http_build_query($_GET) : ''));
}

$status = in_array($_GET['status'] ?? '', ['published', 'draft'], true) ? $_GET['status'] : '';
$q = clean_str($_GET['q'] ?? '', 100);
$where = [];
$params = [];
if ($status) {
    $where[] = 'status = ?';
    $params[] = $status;
}
if ($q !== '') {
    $where[] = '(title LIKE ? OR short_description LIKE ?)';
    $params[] = "%$q%";
    $params[] = "%$q%";
}
$services = db_all('SELECT * FROM services' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY sort_order, title', $params);

$pageTitle = 'Services';
$active = 'services';
require __DIR__ . '/partials/header.php';
?>
<div class="d-flex flex-wrap gap-2 align-items-center mb-3">
  <form class="d-flex flex-wrap gap-2" method="get" role="search">
    <label class="visually-hidden" for="q">Search services</label>
    <input class="form-control" id="q" name="q" value="<?= e($q) ?>" placeholder="Search services…">
    <label class="visually-hidden" for="status">Status</label>
    <select class="form-select" id="status" name="status" onchange="this.form.submit()">
      <option value="">All statuses</option>
      <option value="published"<?= $status === 'published' ? ' selected' : '' ?>>Published</option>
      <option value="draft"<?= $status === 'draft' ? ' selected' : '' ?>>Draft</option>
    </select>
    <button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
  </form>
  <a href="service-edit.php" class="btn btn-brand ms-auto"><i class="bi bi-plus-lg"></i> Add Service</a>
</div>

<form method="post" id="orderForm">
  <?= csrf_field() ?><input type="hidden" name="action" value="order">
  <div class="card shadow-sm border-0">
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead class="table-light">
          <tr><th style="width:90px">Order</th><th>Service</th><th>Page type</th><th>Status</th><th class="text-center">Featured</th><th class="text-end">Actions</th></tr>
        </thead>
        <tbody>
        <?php if (!$services): ?>
          <tr><td colspan="6" class="text-center text-muted py-5">No services found. <a href="service-edit.php">Add your first service</a>.</td></tr>
        <?php endif; ?>
        <?php foreach ($services as $s): ?>
          <tr>
            <td><input type="number" class="form-control form-control-sm" name="order[<?= (int) $s['id'] ?>]" value="<?= (int) $s['sort_order'] ?>" aria-label="Display order for <?= e($s['title']) ?>"></td>
            <td>
              <div class="d-flex align-items-center gap-3">
                <?php if ($s['image']): ?><img src="<?= e(upload_url($s['image'])) ?>" alt="" class="thumb"><?php else: ?><span class="thumb thumb-empty"><i class="bi bi-image"></i></span><?php endif; ?>
                <div>
                  <a href="service-edit.php?id=<?= (int) $s['id'] ?>" class="fw-semibold"><?= e($s['title']) ?></a>
                  <div class="small text-muted">/services/<?= e($s['slug']) ?></div>
                </div>
              </div>
            </td>
            <td><?= $s['page_type'] === 'detail' ? '<span class="badge text-bg-light border"><i class="bi bi-file-text"></i> Detail page</span>' : '<span class="badge text-bg-light border"><i class="bi bi-envelope"></i> Contact redirect</span>' ?></td>
            <td><?= status_badge($s['status']) ?></td>
            <td class="text-center">
              <button type="submit" form="f-feat-<?= (int) $s['id'] ?>" class="btn btn-sm btn-link text-warning" aria-label="<?= $s['is_featured'] ? 'Unfeature' : 'Feature' ?> <?= e($s['title']) ?>">
                <i class="bi bi-star<?= $s['is_featured'] ? '-fill' : '' ?> fs-5"></i>
              </button>
            </td>
            <td class="text-end text-nowrap">
              <?php if ($s['status'] === 'published'): ?>
                <a href="<?= e($s['page_type'] === 'detail' ? '/services/' . $s['slug'] : '/contact?service=' . $s['slug']) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-light" title="View on site"><i class="bi bi-box-arrow-up-right"></i></a>
              <?php endif; ?>
              <button type="submit" form="f-status-<?= (int) $s['id'] ?>" class="btn btn-sm btn-light" title="<?= $s['status'] === 'published' ? 'Unpublish' : 'Publish' ?>"><i class="bi bi-<?= $s['status'] === 'published' ? 'eye-slash' : 'eye' ?>"></i></button>
              <a href="service-edit.php?id=<?= (int) $s['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
              <button type="submit" form="f-del-<?= (int) $s['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete" data-confirm="Delete the service &quot;<?= e($s['title']) ?>&quot;? This cannot be undone."><i class="bi bi-trash"></i></button>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php if ($services): ?>
      <div class="card-footer bg-white d-flex align-items-center">
        <small class="text-muted">Lower numbers appear first. <i class="bi bi-star-fill text-warning"></i> Featured services show on the homepage.</small>
        <button class="btn btn-sm btn-outline-secondary ms-auto"><i class="bi bi-sort-numeric-down"></i> Save order</button>
      </div>
    <?php endif; ?>
  </div>
</form>

<?php foreach ($services as $s): $sid = (int) $s['id']; ?>
  <form method="post" id="f-feat-<?= $sid ?>" class="d-none"><?= csrf_field() ?><input type="hidden" name="action" value="toggle_featured"><input type="hidden" name="id" value="<?= $sid ?>"></form>
  <form method="post" id="f-status-<?= $sid ?>" class="d-none"><?= csrf_field() ?><input type="hidden" name="action" value="toggle_status"><input type="hidden" name="id" value="<?= $sid ?>"></form>
  <form method="post" id="f-del-<?= $sid ?>" class="d-none"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $sid ?>"></form>
<?php endforeach; ?>
<?php require __DIR__ . '/partials/footer.php';
