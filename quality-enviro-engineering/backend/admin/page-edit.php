<?php
declare(strict_types=1);
require __DIR__ . '/_init.php';
$admin = require_admin();

$id = (int) ($_GET['id'] ?? 0);
$page = db_one("SELECT * FROM pages WHERE id = ? AND page_key IN ('privacy','terms')", [$id]);
if (!$page) {
    flash('danger', 'Page not found.');
    redirect('pages.php');
}
$errors = [];
if (is_post()) {
    verify_csrf();
    $title = clean_str($_POST['title'] ?? '', 190);
    $content = sanitize_html($_POST['content'] ?? '');
    $status = ($_POST['status'] ?? '') === 'draft' ? 'draft' : 'published';
    if (mb_strlen($title) < 3) {
        $errors[] = 'Please enter a page title.';
    }
    if (!$errors) {
        db_exec('UPDATE pages SET title = ?, content = ?, status = ? WHERE id = ?', [$title, $content, $status, $id]);
        flash('success', 'Page saved.');
        redirect('page-edit.php?id=' . $id);
    }
    $page = array_merge($page, ['title' => $title, 'content' => $content, 'status' => $status]);
}

$pageTitle = 'Edit: ' . $page['title'];
$active = 'pages';
require __DIR__ . '/partials/header.php';
?>
<nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="pages.php">Page Content</a></li><li class="breadcrumb-item active" aria-current="page"><?= e($page['title']) ?></li></ol></nav>
<?php foreach ($errors as $err): ?><div class="alert alert-danger py-2"><?= e($err) ?></div><?php endforeach; ?>
<form method="post" class="card shadow-sm border-0">
  <?= csrf_field() ?>
  <div class="card-body">
    <div class="row g-3 mb-3">
      <div class="col-md-8"><label class="form-label fw-semibold" for="title">Page title</label><input class="form-control form-control-lg" id="title" name="title" value="<?= e($page['title']) ?>" required maxlength="190"></div>
      <div class="col-md-4"><label class="form-label fw-semibold" for="status">Status</label>
        <select class="form-select form-select-lg" id="status" name="status">
          <option value="published"<?= $page['status'] === 'published' ? ' selected' : '' ?>>Published</option>
          <option value="draft"<?= $page['status'] === 'draft' ? ' selected' : '' ?>>Draft (hidden)</option>
        </select>
      </div>
    </div>
    <label class="form-label fw-semibold">Content</label>
    <?= rich_editor('content', $page['content'], 20) ?>
    <div class="form-text">Have legal content reviewed by a qualified professional before publishing.</div>
  </div>
  <div class="card-footer bg-white"><button class="btn btn-brand"><i class="bi bi-check2"></i> Save page</button></div>
</form>
<?php require __DIR__ . '/partials/footer.php';
