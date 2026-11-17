<?php
declare(strict_types=1);
require __DIR__ . '/_init.php';
$admin = require_admin();

$statuses = ['new' => 'New', 'in_progress' => 'In Progress', 'responded' => 'Responded', 'closed' => 'Closed'];
$id = (int) ($_GET['id'] ?? 0);
$inq = db_one('SELECT * FROM contact_inquiries WHERE id = ?', [$id]);
if (!$inq) {
    flash('danger', 'Inquiry not found.');
    redirect('inquiries.php');
}

if (is_post()) {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    if ($action === 'update') {
        $status = array_key_exists($_POST['status'] ?? '', $statuses) ? $_POST['status'] : $inq['status'];
        $notes = clean_str($_POST['admin_notes'] ?? '', 5000);
        db_exec('UPDATE contact_inquiries SET status = ?, admin_notes = ? WHERE id = ?', [$status, $notes ?: null, $id]);
        flash('success', 'Inquiry updated.');
    } elseif ($action === 'unread') {
        db_exec('UPDATE contact_inquiries SET is_read = 0 WHERE id = ?', [$id]);
        flash('success', 'Marked as unread.');
        redirect('inquiries.php');
    } elseif ($action === 'delete') {
        db_exec('DELETE FROM contact_inquiries WHERE id = ?', [$id]);
        flash('success', 'Inquiry deleted.');
        redirect('inquiries.php');
    }
    redirect('inquiry.php?id=' . $id);
}

// opening an inquiry marks it as read
if (!$inq['is_read']) {
    db_exec('UPDATE contact_inquiries SET is_read = 1 WHERE id = ?', [$id]);
    $inq['is_read'] = 1;
}

$prev = db_value('SELECT id FROM contact_inquiries WHERE created_at > ? OR (created_at = ? AND id > ?) ORDER BY created_at ASC, id ASC LIMIT 1', [$inq['created_at'], $inq['created_at'], $id]);
$next = db_value('SELECT id FROM contact_inquiries WHERE created_at < ? OR (created_at = ? AND id < ?) ORDER BY created_at DESC, id DESC LIMIT 1', [$inq['created_at'], $inq['created_at'], $id]);

$pageTitle = 'Inquiry #' . $id;
$active = 'inquiries';
require __DIR__ . '/partials/header.php';
$replySubject = 'Re: ' . $inq['subject'];
?>
<div class="d-flex flex-wrap align-items-center gap-2 mb-3">
  <a href="inquiries.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> All inquiries</a>
  <div class="btn-group btn-group-sm ms-auto">
    <a class="btn btn-outline-secondary<?= $prev ? '' : ' disabled' ?>" href="<?= $prev ? 'inquiry.php?id=' . (int) $prev : '#' ?>" aria-label="Newer inquiry"><i class="bi bi-chevron-left"></i> Newer</a>
    <a class="btn btn-outline-secondary<?= $next ? '' : ' disabled' ?>" href="<?= $next ? 'inquiry.php?id=' . (int) $next : '#' ?>" aria-label="Older inquiry">Older <i class="bi bi-chevron-right"></i></a>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-8">
    <div class="card shadow-sm border-0">
      <div class="card-header bg-white d-flex flex-wrap gap-2 align-items-center">
        <h2 class="h5 mb-0"><?= e($inq['subject']) ?></h2>
        <?= $inq['inquiry_type'] === 'quote' ? '<span class="badge text-bg-warning">Quotation request</span>' : '<span class="badge text-bg-light border">Contact inquiry</span>' ?>
        <?= status_badge($inq['status']) ?>
      </div>
      <div class="card-body">
        <dl class="row mb-4 inquiry-meta">
          <dt class="col-sm-3">Name</dt><dd class="col-sm-9"><?= e($inq['full_name']) ?></dd>
          <?php if ($inq['company_name']): ?><dt class="col-sm-3">Company</dt><dd class="col-sm-9"><?= e($inq['company_name']) ?></dd><?php endif; ?>
          <dt class="col-sm-3">Email</dt><dd class="col-sm-9"><a href="mailto:<?= e($inq['email']) ?>"><?= e($inq['email']) ?></a></dd>
          <dt class="col-sm-3">Phone</dt><dd class="col-sm-9"><a href="tel:<?= e(preg_replace('/[^\d+]/', '', $inq['phone'])) ?>"><?= e($inq['phone']) ?></a></dd>
          <dt class="col-sm-3">Service</dt><dd class="col-sm-9"><?= e($inq['service_title'] ?: '—') ?></dd>
          <?php if ($inq['project_location']): ?><dt class="col-sm-3">Project location</dt><dd class="col-sm-9"><?= e($inq['project_location']) ?></dd><?php endif; ?>
          <?php if ($inq['timeline']): ?><dt class="col-sm-3">Timeline</dt><dd class="col-sm-9"><?= e($inq['timeline']) ?></dd><?php endif; ?>
          <?php if ($inq['budget_range']): ?><dt class="col-sm-3">Budget</dt><dd class="col-sm-9"><?= e($inq['budget_range']) ?></dd><?php endif; ?>
          <dt class="col-sm-3">Received</dt><dd class="col-sm-9"><?= e(format_dt($inq['created_at'], 'l, d F Y \a\t H:i')) ?></dd>
        </dl>
        <h3 class="h6 text-uppercase text-muted small">Message</h3>
        <div class="message-body"><?= nl2br(e($inq['message'])) ?></div>
      </div>
      <div class="card-footer bg-white d-flex flex-wrap gap-2">
        <a class="btn btn-brand" href="mailto:<?= e($inq['email']) ?>?subject=<?= rawurlencode($replySubject) ?>"><i class="bi bi-reply"></i> Reply by email</a>
        <?php if ($inq['phone']): ?><a class="btn btn-outline-secondary" href="tel:<?= e(preg_replace('/[^\d+]/', '', $inq['phone'])) ?>"><i class="bi bi-telephone"></i> Call</a><?php endif; ?>
        <form method="post" class="ms-auto d-flex gap-2">
          <?= csrf_field() ?>
          <button name="action" value="unread" class="btn btn-outline-secondary"><i class="bi bi-envelope"></i> Mark unread</button>
          <button name="action" value="delete" class="btn btn-outline-danger" data-confirm="Delete this inquiry permanently?"><i class="bi bi-trash"></i> Delete</button>
        </form>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <form method="post" class="card shadow-sm border-0 mb-4">
      <?= csrf_field() ?><input type="hidden" name="action" value="update">
      <div class="card-header bg-white"><h2 class="h6 mb-0">Follow-up</h2></div>
      <div class="card-body">
        <label class="form-label fw-semibold" for="status">Status</label>
        <select class="form-select mb-3" id="status" name="status">
          <?php foreach ($statuses as $k => $v): ?><option value="<?= $k ?>"<?= $inq['status'] === $k ? ' selected' : '' ?>><?= $v ?></option><?php endforeach; ?>
        </select>
        <label class="form-label fw-semibold" for="admin_notes">Internal notes</label>
        <textarea class="form-control" id="admin_notes" name="admin_notes" rows="6" placeholder="Visible to admins only"><?= e($inq['admin_notes']) ?></textarea>
      </div>
      <div class="card-footer bg-white"><button class="btn btn-brand w-100"><i class="bi bi-check2"></i> Save</button></div>
    </form>
    <div class="card shadow-sm border-0">
      <div class="card-header bg-white"><h2 class="h6 mb-0">Technical details</h2></div>
      <ul class="list-group list-group-flush small">
        <li class="list-group-item"><strong>IP:</strong> <?= e($inq['ip_address'] ?: '—') ?></li>
        <li class="list-group-item text-break"><strong>Browser:</strong> <?= e($inq['user_agent'] ?: '—') ?></li>
        <li class="list-group-item"><strong>Last updated:</strong> <?= e(format_dt($inq['updated_at'])) ?></li>
      </ul>
    </div>
  </div>
</div>
<?php require __DIR__ . '/partials/footer.php';
