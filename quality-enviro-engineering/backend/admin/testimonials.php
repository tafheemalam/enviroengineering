<?php
declare(strict_types=1);
require __DIR__ . '/_init.php';
$admin = require_admin();

$editId = (int) ($_GET['edit'] ?? 0);
$edit = $editId ? db_one('SELECT * FROM testimonials WHERE id = ?', [$editId]) : null;
$errors = [];

if (is_post()) {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);
    if ($action === 'delete') {
        db_exec('DELETE FROM testimonials WHERE id = ?', [$id]);
        flash('success', 'Testimonial deleted.');
        redirect('testimonials.php');
    }
    if ($action === 'toggle') {
        db_exec("UPDATE testimonials SET status = IF(status = 'published', 'draft', 'published') WHERE id = ?", [$id]);
        flash('success', 'Status updated.');
        redirect('testimonials.php');
    }
    if ($action === 'save') {
        $t = [
            'client_name' => clean_str($_POST['client_name'] ?? '', 120),
            'client_role' => clean_str($_POST['client_role'] ?? '', 160),
            'company'     => clean_str($_POST['company'] ?? '', 160),
            'content'     => clean_str($_POST['content'] ?? '', 2000),
            'rating'      => max(1, min(5, (int) ($_POST['rating'] ?? 5))),
            'status'      => ($_POST['status'] ?? '') === 'published' ? 'published' : 'draft',
            'sort_order'  => (int) ($_POST['sort_order'] ?? 0),
        ];
        if (mb_strlen($t['client_name']) < 2) {
            $errors[] = 'Client name is required.';
        }
        if (mb_strlen($t['content']) < 10) {
            $errors[] = 'Testimonial text is required.';
        }
        if (!$errors) {
            $vals = array_map(fn($v) => $v === '' ? null : $v, array_values($t));
            if ($id) {
                db_exec('UPDATE testimonials SET client_name=?, client_role=?, company=?, content=?, rating=?, status=?, sort_order=? WHERE id=?', [...$vals, $id]);
            } else {
                db_exec('INSERT INTO testimonials (client_name, client_role, company, content, rating, status, sort_order) VALUES (?,?,?,?,?,?,?)', $vals);
            }
            flash('success', 'Testimonial saved.');
            redirect('testimonials.php');
        }
        $edit = $t + ['id' => $id];
    }
}

$items = db_all('SELECT * FROM testimonials ORDER BY sort_order, id');
$f = $edit ?? ['id' => 0, 'client_name' => '', 'client_role' => '', 'company' => '', 'content' => '', 'rating' => 5, 'status' => 'draft', 'sort_order' => count($items) + 1];

$pageTitle = 'Testimonials';
$active = 'testimonials';
require __DIR__ . '/partials/header.php';
?>
<?php if (setting('show_testimonials', '0') !== '1'): ?>
  <div class="alert alert-info"><i class="bi bi-info-circle"></i> Testimonials are hidden on the website. Enable them in <a href="settings.php#features">Settings → Website features</a> once you have genuine client feedback.</div>
<?php endif; ?>
<?php foreach ($errors as $err): ?><div class="alert alert-danger py-2"><?= e($err) ?></div><?php endforeach; ?>
<div class="row g-4">
  <div class="col-lg-7">
    <div class="card shadow-sm border-0">
      <div class="table-responsive"><table class="table align-middle mb-0">
        <thead class="table-light"><tr><th>Client</th><th>Rating</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
        <tbody>
        <?php if (!$items): ?><tr><td colspan="4" class="text-center text-muted py-4">No testimonials yet.</td></tr><?php endif; ?>
        <?php foreach ($items as $t): ?>
          <tr>
            <td><strong><?= e($t['client_name']) ?></strong><div class="small text-muted"><?= e(trim($t['client_role'] . ', ' . $t['company'], ', ')) ?></div><div class="small text-truncate" style="max-width:340px"><?= e($t['content']) ?></div></td>
            <td class="text-warning text-nowrap"><?= str_repeat('★', (int) $t['rating']) ?></td>
            <td><?= status_badge($t['status']) ?></td>
            <td class="text-end text-nowrap">
              <form method="post" class="d-inline"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int) $t['id'] ?>"><button class="btn btn-sm btn-light" title="Publish/unpublish"><i class="bi bi-<?= $t['status'] === 'published' ? 'eye-slash' : 'eye' ?>"></i></button></form>
              <a class="btn btn-sm btn-outline-primary" href="?edit=<?= (int) $t['id'] ?>" title="Edit"><i class="bi bi-pencil"></i></a>
              <form method="post" class="d-inline"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $t['id'] ?>"><button class="btn btn-sm btn-outline-danger" data-confirm="Delete this testimonial?" title="Delete"><i class="bi bi-trash"></i></button></form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table></div>
    </div>
  </div>
  <div class="col-lg-5">
    <form method="post" class="card shadow-sm border-0">
      <?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
      <div class="card-header bg-white d-flex"><h2 class="h6 mb-0"><?= $f['id'] ? 'Edit testimonial' : 'Add testimonial' ?></h2><?php if ($f['id']): ?><a href="testimonials.php" class="ms-auto small">+ New</a><?php endif; ?></div>
      <div class="card-body row g-3">
        <div class="col-12"><label class="form-label fw-semibold" for="client_name">Client name</label><input class="form-control" id="client_name" name="client_name" value="<?= e($f['client_name']) ?>" required maxlength="120"></div>
        <div class="col-md-6"><label class="form-label fw-semibold" for="client_role">Role</label><input class="form-control" id="client_role" name="client_role" value="<?= e($f['client_role']) ?>" maxlength="160"></div>
        <div class="col-md-6"><label class="form-label fw-semibold" for="company">Company</label><input class="form-control" id="company" name="company" value="<?= e($f['company']) ?>" maxlength="160"></div>
        <div class="col-12"><label class="form-label fw-semibold" for="content">Testimonial</label><textarea class="form-control" id="content" name="content" rows="4" required maxlength="2000"><?= e($f['content']) ?></textarea></div>
        <div class="col-4"><label class="form-label fw-semibold" for="rating">Rating</label><select class="form-select" id="rating" name="rating"><?php for ($i = 5; $i >= 1; $i--): ?><option value="<?= $i ?>"<?= (int) $f['rating'] === $i ? ' selected' : '' ?>><?= $i ?> ★</option><?php endfor; ?></select></div>
        <div class="col-4"><label class="form-label fw-semibold" for="tstatus">Status</label><select class="form-select" id="tstatus" name="status"><option value="draft"<?= $f['status'] === 'draft' ? ' selected' : '' ?>>Draft</option><option value="published"<?= $f['status'] === 'published' ? ' selected' : '' ?>>Published</option></select></div>
        <div class="col-4"><label class="form-label fw-semibold" for="sort_order">Order</label><input type="number" class="form-control" id="sort_order" name="sort_order" value="<?= (int) $f['sort_order'] ?>"></div>
        <div class="col-12 form-text mt-1">Only publish genuine feedback you have permission to use.</div>
      </div>
      <div class="card-footer bg-white"><button class="btn btn-brand"><i class="bi bi-check2"></i> Save</button></div>
    </form>
  </div>
</div>
<?php require __DIR__ . '/partials/footer.php';
