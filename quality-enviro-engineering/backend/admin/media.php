<?php
declare(strict_types=1);
require __DIR__ . '/_init.php';
$admin = require_admin();

if (is_post()) {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    if ($action === 'upload') {
        $files = $_FILES['files'] ?? null;
        $ok = 0;
        $fail = [];
        if ($files && is_array($files['name'])) {
            foreach ($files['name'] as $i => $name) {
                if (($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                    continue;
                }
                $one = ['name' => $name, 'type' => $files['type'][$i], 'tmp_name' => $files['tmp_name'][$i], 'error' => $files['error'][$i], 'size' => $files['size'][$i]];
                try {
                    handle_image_upload($one, 'media', (int) $admin['id'], clean_str($_POST['alt'] ?? '', 255));
                    $ok++;
                } catch (RuntimeException $ex) {
                    $fail[] = $name . ': ' . $ex->getMessage();
                }
            }
        }
        if ($ok) {
            flash('success', "$ok file(s) uploaded.");
        }
        foreach ($fail as $msg) {
            flash('danger', $msg);
        }
        if (!$ok && !$fail) {
            flash('warning', 'Please choose at least one image.');
        }
    } elseif ($action === 'alt') {
        db_exec('UPDATE media SET alt_text = ? WHERE id = ?', [clean_str($_POST['alt_text'] ?? '', 255) ?: null, (int) ($_POST['id'] ?? 0)]);
        flash('success', 'Alt text saved.');
    } elseif ($action === 'delete') {
        $m = db_one('SELECT * FROM media WHERE id = ?', [(int) ($_POST['id'] ?? 0)]);
        if ($m) {
            $uses = media_usage($m['file_path']);
            if ($uses && empty($_POST['force'])) {
                flash('warning', 'This image is in use (' . implode('; ', $uses) . '). Replace it there first, or delete anyway.');
                $_SESSION['force_delete'] = (int) $m['id'];
            } else {
                delete_upload_file($m['file_path']);
                db_exec('DELETE FROM media WHERE id = ?', [$m['id']]);
                flash('success', 'Image deleted.');
            }
        }
    }
    redirect('media.php' . (isset($_GET['filter']) ? '?filter=' . urlencode((string) $_GET['filter']) : ''));
}

$filter = ($_GET['filter'] ?? '') === 'unused' ? 'unused' : '';
$all = db_all('SELECT m.*, a.name AS uploader FROM media m LEFT JOIN admins a ON a.id = m.uploaded_by ORDER BY m.created_at DESC');
$items = [];
foreach ($all as $m) {
    $m['uses'] = media_usage($m['file_path']);
    if ($filter === 'unused' && $m['uses']) {
        continue;
    }
    $items[] = $m;
}
$forceId = $_SESSION['force_delete'] ?? 0;
unset($_SESSION['force_delete']);

$pageTitle = 'Media Library';
$active = 'media';
require __DIR__ . '/partials/header.php';
?>
<form method="post" enctype="multipart/form-data" class="card shadow-sm border-0 mb-4">
  <?= csrf_field() ?><input type="hidden" name="action" value="upload">
  <div class="card-body">
    <div class="dropzone" id="dropzone">
      <i class="bi bi-cloud-arrow-up fs-1 text-success" aria-hidden="true"></i>
      <p class="mb-2"><strong>Drag &amp; drop images here</strong> or choose files</p>
      <label class="visually-hidden" for="files">Images</label>
      <input type="file" name="files[]" id="files" class="form-control mx-auto" style="max-width:420px" multiple accept=".jpg,.jpeg,.png,.webp,.gif">
      <p class="small text-muted mt-2 mb-0">JPG, PNG, WebP, GIF · max <?= round(config('uploads.max_bytes') / 1048576, 1) ?> MB each. Files are renamed and checked for safety.</p>
    </div>
    <div class="row g-2 mt-2 align-items-end">
      <div class="col-md-8"><label class="form-label small" for="alt">Alt text (optional, applied to all files)</label><input class="form-control" id="alt" name="alt" maxlength="255"></div>
      <div class="col-md-4"><button class="btn btn-brand w-100"><i class="bi bi-upload"></i> Upload</button></div>
    </div>
  </div>
</form>

<div class="d-flex align-items-center gap-2 mb-3">
  <div class="btn-group btn-group-sm" role="group" aria-label="Filter">
    <a href="media.php" class="btn btn-outline-secondary<?= $filter ? '' : ' active' ?>">All (<?= count($all) ?>)</a>
    <a href="media.php?filter=unused" class="btn btn-outline-secondary<?= $filter ? ' active' : '' ?>">Unused</a>
  </div>
</div>

<?php if (!$items): ?>
  <p class="text-muted text-center py-5"><?= $filter ? 'No unused images.' : 'No images uploaded yet.' ?></p>
<?php else: ?>
<div class="media-admin-grid">
  <?php foreach ($items as $m): $url = upload_url($m['file_path']); ?>
    <div class="card shadow-sm border-0 media-card">
      <a href="<?= e($url) ?>" target="_blank" rel="noopener" class="media-thumb" aria-label="Preview <?= e($m['original_name']) ?>"><img src="<?= e($url) ?>" alt="<?= e($m['alt_text'] ?? '') ?>" loading="lazy"></a>
      <div class="card-body p-2 small">
        <div class="text-truncate fw-semibold" title="<?= e($m['original_name']) ?>"><?= e($m['original_name']) ?></div>
        <div class="text-muted"><?= $m['width'] ? (int) $m['width'] . '×' . (int) $m['height'] . ' · ' : '' ?><?= number_format($m['file_size'] / 1024, 0) ?> KB</div>
        <div class="mt-1"><?= $m['uses'] ? '<span class="badge text-bg-success" title="' . e(implode("\n", $m['uses'])) . '">In use (' . count($m['uses']) . ')</span>' : '<span class="badge text-bg-light border">Unused</span>' ?></div>
        <form method="post" class="input-group input-group-sm mt-2">
          <?= csrf_field() ?><input type="hidden" name="action" value="alt"><input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
          <input class="form-control" name="alt_text" value="<?= e($m['alt_text'] ?? '') ?>" placeholder="Alt text" aria-label="Alt text">
          <button class="btn btn-outline-secondary" aria-label="Save alt text"><i class="bi bi-check2"></i></button>
        </form>
        <div class="d-flex gap-1 mt-2">
          <button type="button" class="btn btn-sm btn-light flex-fill js-copy" data-copy="<?= e($url) ?>"><i class="bi bi-clipboard"></i> Copy URL</button>
          <form method="post" class="m-0">
            <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
            <?php if ($forceId === (int) $m['id']): ?><input type="hidden" name="force" value="1"><?php endif; ?>
            <button class="btn btn-sm <?= $forceId === (int) $m['id'] ? 'btn-danger' : 'btn-outline-danger' ?>" data-confirm="<?= $forceId === (int) $m['id'] ? 'This image is in use. Delete it anyway?' : 'Delete this image?' ?>" aria-label="Delete image"><i class="bi bi-trash"></i><?= $forceId === (int) $m['id'] ? ' Delete anyway' : '' ?></button>
          </form>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
<?php require __DIR__ . '/partials/footer.php';
