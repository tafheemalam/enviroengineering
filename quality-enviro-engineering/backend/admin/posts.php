<?php
declare(strict_types=1);
require __DIR__ . '/_init.php';
$admin = require_admin();

if (is_post()) {
    verify_csrf();
    $id = (int) ($_POST['id'] ?? 0);
    if (($_POST['action'] ?? '') === 'delete') {
        db_exec('DELETE FROM posts WHERE id = ?', [$id]);
        flash('success', 'Article deleted.');
    } elseif (($_POST['action'] ?? '') === 'toggle') {
        db_exec("UPDATE posts SET status = IF(status = 'published', 'draft', 'published'),
                 published_at = IF(status = 'published' AND published_at IS NULL, UTC_TIMESTAMP(), published_at) WHERE id = ?", [$id]);
        flash('success', 'Article status updated.');
    }
    redirect('posts.php');
}

$total = (int) db_value('SELECT COUNT(*) FROM posts');
[$offset, $pager] = paginate($total, 20, (int) ($_GET['page'] ?? 1));
$posts = db_all("SELECT id, title, slug, status, published_at, image, updated_at FROM posts ORDER BY COALESCE(published_at, created_at) DESC LIMIT 20 OFFSET $offset");

$pageTitle = 'Insights';
$active = 'posts';
require __DIR__ . '/partials/header.php';
?>
<div class="d-flex align-items-center mb-3">
  <p class="text-muted mb-0">Articles improve organic search traffic. <?= setting('show_insights', '1') === '1' ? '' : '<strong class="text-warning">The Insights section is currently hidden – enable it in Settings.</strong>' ?></p>
  <a href="post-edit.php" class="btn btn-brand ms-auto"><i class="bi bi-plus-lg"></i> New Article</a>
</div>
<div class="card shadow-sm border-0">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead class="table-light"><tr><th>Article</th><th>Status</th><th>Published</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
      <?php if (!$posts): ?><tr><td colspan="4" class="text-center text-muted py-5">No articles yet. <a href="post-edit.php">Write the first one</a>.</td></tr><?php endif; ?>
      <?php foreach ($posts as $p): ?>
        <tr>
          <td><div class="d-flex align-items-center gap-3">
            <?php if ($p['image']): ?><img src="<?= e(upload_url($p['image'])) ?>" alt="" class="thumb"><?php else: ?><span class="thumb thumb-empty"><i class="bi bi-image"></i></span><?php endif; ?>
            <div><a class="fw-semibold" href="post-edit.php?id=<?= (int) $p['id'] ?>"><?= e($p['title']) ?></a><div class="small text-muted">/insights/<?= e($p['slug']) ?></div></div>
          </div></td>
          <td><?= status_badge($p['status']) ?></td>
          <td class="small text-muted"><?= e(format_dt($p['published_at'], 'd M Y')) ?: '—' ?></td>
          <td class="text-end text-nowrap">
            <?php if ($p['status'] === 'published'): ?><a class="btn btn-sm btn-light" href="/insights/<?= e($p['slug']) ?>" target="_blank" rel="noopener" title="View"><i class="bi bi-box-arrow-up-right"></i></a><?php endif; ?>
            <form method="post" class="d-inline"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int) $p['id'] ?>"><button class="btn btn-sm btn-light" title="<?= $p['status'] === 'published' ? 'Unpublish' : 'Publish' ?>"><i class="bi bi-<?= $p['status'] === 'published' ? 'eye-slash' : 'eye' ?>"></i></button></form>
            <a class="btn btn-sm btn-outline-primary" href="post-edit.php?id=<?= (int) $p['id'] ?>" title="Edit"><i class="bi bi-pencil"></i></a>
            <form method="post" class="d-inline"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $p['id'] ?>"><button class="btn btn-sm btn-outline-danger" data-confirm="Delete this article?" title="Delete"><i class="bi bi-trash"></i></button></form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php if ($pager): ?><div class="card-footer bg-white"><?= $pager ?></div><?php endif; ?>
</div>
<?php require __DIR__ . '/partials/footer.php';
