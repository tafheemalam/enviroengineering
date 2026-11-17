<?php
declare(strict_types=1);
require __DIR__ . '/_init.php';
$admin = require_admin();

const STATUSES = ['new' => 'New', 'in_progress' => 'In Progress', 'responded' => 'Responded', 'closed' => 'Closed'];

// ---- Bulk actions ----------------------------------------------------
if (is_post()) {
    verify_csrf();
    $ids = array_values(array_filter(array_map('intval', (array) ($_POST['ids'] ?? []))));
    $action = $_POST['bulk'] ?? '';
    if ($ids) {
        $in = implode(',', array_fill(0, count($ids), '?'));
        if ($action === 'read' || $action === 'unread') {
            db_exec("UPDATE contact_inquiries SET is_read = ? WHERE id IN ($in)", [$action === 'read' ? 1 : 0, ...$ids]);
            flash('success', count($ids) . ' inquiries marked as ' . $action . '.');
        } elseif (array_key_exists($action, STATUSES)) {
            db_exec("UPDATE contact_inquiries SET status = ? WHERE id IN ($in)", [$action, ...$ids]);
            flash('success', count($ids) . ' inquiries set to ' . STATUSES[$action] . '.');
        } elseif ($action === 'delete') {
            db_exec("DELETE FROM contact_inquiries WHERE id IN ($in)", $ids);
            flash('success', count($ids) . ' inquiries deleted.');
        }
    } else {
        flash('warning', 'Select at least one inquiry.');
    }
    redirect('inquiries.php' . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : ''));
}

// ---- Filters ---------------------------------------------------------
$f = [
    'q'       => clean_str($_GET['q'] ?? '', 100),
    'status'  => array_key_exists($_GET['status'] ?? '', STATUSES) ? $_GET['status'] : '',
    'read'    => in_array($_GET['read'] ?? '', ['0', '1'], true) ? $_GET['read'] : '',
    'type'    => in_array($_GET['type'] ?? '', ['contact', 'quote'], true) ? $_GET['type'] : '',
    'service' => (int) ($_GET['service'] ?? 0),
    'from'    => preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['from'] ?? '') ? $_GET['from'] : '',
    'to'      => preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['to'] ?? '') ? $_GET['to'] : '',
];
$where = [];
$params = [];
if ($f['q'] !== '') {
    $where[] = '(full_name LIKE ? OR email LIKE ? OR company_name LIKE ? OR subject LIKE ? OR message LIKE ? OR phone LIKE ?)';
    array_push($params, ...array_fill(0, 6, '%' . $f['q'] . '%'));
}
if ($f['status']) { $where[] = 'status = ?'; $params[] = $f['status']; }
if ($f['read'] !== '') { $where[] = 'is_read = ?'; $params[] = (int) $f['read']; }
if ($f['type']) { $where[] = 'inquiry_type = ?'; $params[] = $f['type']; }
if ($f['service']) { $where[] = 'service_id = ?'; $params[] = $f['service']; }
// dates are entered in local time; stored as UTC
$tz = new DateTimeZone(date_default_timezone_get());
if ($f['from']) {
    $where[] = 'created_at >= ?';
    $params[] = (new DateTime($f['from'] . ' 00:00:00', $tz))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
}
if ($f['to']) {
    $where[] = 'created_at <= ?';
    $params[] = (new DateTime($f['to'] . ' 23:59:59', $tz))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
}
$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';

// ---- CSV export ------------------------------------------------------
if (($_GET['export'] ?? '') === 'csv') {
    $rows = db_all('SELECT * FROM contact_inquiries' . $whereSql . ' ORDER BY created_at DESC', $params);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="inquiries-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
    fputcsv($out, ['ID', 'Type', 'Received', 'Status', 'Read', 'Full name', 'Company', 'Email', 'Phone', 'Service', 'Subject', 'Message', 'Project location', 'Timeline', 'Budget', 'Admin notes']);
    // neutralise spreadsheet formula injection
    $safe = fn($v) => is_string($v) && $v !== '' && in_array($v[0], ['=', '+', '-', '@', "\t", "\r"], true) ? "'" . $v : $v;
    foreach ($rows as $r) {
        fputcsv($out, array_map($safe, [
            $r['id'], $r['inquiry_type'], format_dt($r['created_at'], 'Y-m-d H:i'), STATUSES[$r['status']] ?? $r['status'], $r['is_read'] ? 'Yes' : 'No',
            $r['full_name'], $r['company_name'], $r['email'], $r['phone'], $r['service_title'], $r['subject'], $r['message'],
            $r['project_location'], $r['timeline'], $r['budget_range'], $r['admin_notes'],
        ]));
    }
    fclose($out);
    exit;
}

$total = (int) db_value('SELECT COUNT(*) FROM contact_inquiries' . $whereSql, $params);
$perPage = 20;
$query = array_filter($f, fn($v) => $v !== '' && $v !== 0);
[$offset, $pager] = paginate($total, $perPage, (int) ($_GET['page'] ?? 1), $query);
$rows = db_all('SELECT id, inquiry_type, full_name, company_name, email, phone, service_title, subject, status, is_read, created_at
                FROM contact_inquiries' . $whereSql . " ORDER BY created_at DESC LIMIT $perPage OFFSET $offset", $params);
$services = db_all('SELECT id, title FROM services ORDER BY title');

$pageTitle = 'Contact Inquiries';
$active = 'inquiries';
require __DIR__ . '/partials/header.php';
?>
<form method="get" class="card shadow-sm border-0 mb-3">
  <div class="card-body row g-2 align-items-end">
    <div class="col-lg-3 col-md-6"><label class="form-label small mb-1" for="q">Search</label><input class="form-control" id="q" name="q" value="<?= e($f['q']) ?>" placeholder="Name, email, subject…"></div>
    <div class="col-lg-2 col-md-3 col-6"><label class="form-label small mb-1" for="status">Status</label>
      <select class="form-select" id="status" name="status"><option value="">Any</option><?php foreach (STATUSES as $k => $v): ?><option value="<?= $k ?>"<?= $f['status'] === $k ? ' selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select></div>
    <div class="col-lg-1 col-md-3 col-6"><label class="form-label small mb-1" for="read">Read</label>
      <select class="form-select" id="read" name="read"><option value="">Any</option><option value="0"<?= $f['read'] === '0' ? ' selected' : '' ?>>Unread</option><option value="1"<?= $f['read'] === '1' ? ' selected' : '' ?>>Read</option></select></div>
    <div class="col-lg-2 col-md-4"><label class="form-label small mb-1" for="service">Service</label>
      <select class="form-select" id="service" name="service"><option value="">Any</option><?php foreach ($services as $s): ?><option value="<?= (int) $s['id'] ?>"<?= $f['service'] === (int) $s['id'] ? ' selected' : '' ?>><?= e($s['title']) ?></option><?php endforeach; ?></select></div>
    <div class="col-lg-1 col-md-2 col-6"><label class="form-label small mb-1" for="type">Type</label>
      <select class="form-select" id="type" name="type"><option value="">Any</option><option value="contact"<?= $f['type'] === 'contact' ? ' selected' : '' ?>>Contact</option><option value="quote"<?= $f['type'] === 'quote' ? ' selected' : '' ?>>Quote</option></select></div>
    <div class="col-lg-1 col-md-3 col-6"><label class="form-label small mb-1" for="from">From</label><input type="date" class="form-control" id="from" name="from" value="<?= e($f['from']) ?>"></div>
    <div class="col-lg-1 col-md-3 col-6"><label class="form-label small mb-1" for="to">To</label><input type="date" class="form-control" id="to" name="to" value="<?= e($f['to']) ?>"></div>
    <div class="col-lg-1 col-md-12 d-flex gap-1">
      <button class="btn btn-brand flex-fill" aria-label="Apply filters"><i class="bi bi-funnel"></i></button>
      <a href="inquiries.php" class="btn btn-outline-secondary" aria-label="Clear filters"><i class="bi bi-x-lg"></i></a>
    </div>
  </div>
</form>

<form method="post" id="bulkForm">
  <?= csrf_field() ?>
  <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
    <label class="visually-hidden" for="bulk">Bulk action</label>
    <select name="bulk" id="bulk" class="form-select form-select-sm" style="width:auto">
      <option value="">Bulk action…</option>
      <option value="read">Mark as read</option>
      <option value="unread">Mark as unread</option>
      <?php foreach (STATUSES as $k => $v): ?><option value="<?= $k ?>">Set status: <?= $v ?></option><?php endforeach; ?>
      <option value="delete">Delete</option>
    </select>
    <button class="btn btn-sm btn-outline-secondary" data-confirm-bulk>Apply</button>
    <span class="text-muted small ms-2"><?= number_format($total) ?> result<?= $total === 1 ? '' : 's' ?></span>
    <a class="btn btn-sm btn-outline-success ms-auto" href="?<?= e(http_build_query($query + ['export' => 'csv'])) ?>"><i class="bi bi-filetype-csv"></i> Export CSV</a>
  </div>

  <div class="card shadow-sm border-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th style="width:36px"><input class="form-check-input" type="checkbox" id="checkAll" aria-label="Select all"></th>
            <th>From</th><th>Service / Subject</th><th>Type</th><th>Status</th><th>Received</th><th></th>
          </tr>
        </thead>
        <tbody>
        <?php if (!$rows): ?><tr><td colspan="7" class="text-center text-muted py-5">No inquiries match your filters.</td></tr><?php endif; ?>
        <?php foreach ($rows as $r): ?>
          <tr class="<?= $r['is_read'] ? '' : 'row-unread' ?>">
            <td><input class="form-check-input row-check" type="checkbox" name="ids[]" value="<?= (int) $r['id'] ?>" aria-label="Select inquiry from <?= e($r['full_name']) ?>"></td>
            <td>
              <a href="inquiry.php?id=<?= (int) $r['id'] ?>" class="<?= $r['is_read'] ? '' : 'fw-bold' ?>"><?= e($r['full_name']) ?></a>
              <?php if (!$r['is_read']): ?><span class="badge text-bg-danger ms-1">New</span><?php endif; ?>
              <div class="small text-muted"><?= e($r['email']) ?><?= $r['company_name'] ? ' · ' . e($r['company_name']) : '' ?></div>
            </td>
            <td><div class="small fw-semibold"><?= e($r['service_title'] ?: '—') ?></div><div class="small text-muted text-truncate" style="max-width:300px"><?= e($r['subject']) ?></div></td>
            <td><?= $r['inquiry_type'] === 'quote' ? '<span class="badge text-bg-warning">Quote</span>' : '<span class="badge text-bg-light border">Contact</span>' ?></td>
            <td><?= status_badge($r['status']) ?></td>
            <td class="small text-muted text-nowrap"><?= e(format_dt($r['created_at'])) ?></td>
            <td class="text-end"><a href="inquiry.php?id=<?= (int) $r['id'] ?>" class="btn btn-sm btn-outline-primary" aria-label="View inquiry"><i class="bi bi-eye"></i></a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php if ($pager): ?><div class="card-footer bg-white"><?= $pager ?></div><?php endif; ?>
  </div>
</form>
<?php require __DIR__ . '/partials/footer.php';
