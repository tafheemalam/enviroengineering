<?php
declare(strict_types=1);
require __DIR__ . '/_init.php';
$admin = require_admin();

$id = (int) ($_GET['id'] ?? 0);
$post = $id ? db_one('SELECT * FROM posts WHERE id = ?', [$id]) : null;
if ($id && !$post) {
    flash('danger', 'Article not found.');
    redirect('posts.php');
}
$p = $post ?? ['title' => '', 'slug' => '', 'excerpt' => '', 'content' => '', 'image' => null, 'image_alt' => '', 'author_name' => $admin['name'],
    'status' => 'draft', 'published_at' => null, 'seo_title' => '', 'meta_description' => '', 'meta_keywords' => ''];
$errors = [];

if (is_post()) {
    verify_csrf();
    $p['title'] = clean_str($_POST['title'] ?? '', 200);
    $p['slug'] = slugify(clean_str($_POST['slug'] ?? '', 190) ?: $p['title']);
    $p['excerpt'] = clean_str($_POST['excerpt'] ?? '', 500);
    $p['content'] = sanitize_html($_POST['content'] ?? '');
    $p['image_alt'] = clean_str($_POST['image_alt'] ?? '', 255);
    $p['author_name'] = clean_str($_POST['author_name'] ?? '', 120);
    $p['status'] = ($_POST['status'] ?? '') === 'published' ? 'published' : 'draft';
    $p['seo_title'] = clean_str($_POST['seo_title'] ?? '', 190);
    $p['meta_description'] = clean_str($_POST['meta_description'] ?? '', 320);
    $p['meta_keywords'] = clean_str($_POST['meta_keywords'] ?? '', 320);
    // publish date entered in local time -> UTC
    $pd = clean_str($_POST['published_at'] ?? '', 20);
    if ($pd !== '' && ($dt = DateTime::createFromFormat('Y-m-d\TH:i', $pd, new DateTimeZone(date_default_timezone_get())))) {
        $p['published_at'] = $dt->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
    } elseif ($p['status'] === 'published' && !$p['published_at']) {
        $p['published_at'] = gmdate('Y-m-d H:i:s');
    }

    if (mb_strlen($p['title']) < 3) {
        $errors[] = 'Please enter a title.';
    }
    if ($p['status'] === 'published' && trim(strip_tags($p['content'])) === '') {
        $errors[] = 'Published articles need content.';
    }
    if (!$errors) {
        try {
            $p['image'] = process_image_field('image', $p['image'], 'posts', $admin, $p['image_alt']);
        } catch (RuntimeException $ex) {
            $errors[] = $ex->getMessage();
        }
    }
    if (!$errors) {
        $p['slug'] = unique_slug('posts', $p['slug'], $id ?: null);
        $fields = ['title', 'slug', 'excerpt', 'content', 'image', 'image_alt', 'author_name', 'status', 'published_at', 'seo_title', 'meta_description', 'meta_keywords'];
        $values = array_map(fn($f) => $p[$f] === '' ? null : $p[$f], $fields);
        if ($id) {
            db_exec('UPDATE posts SET ' . implode(', ', array_map(fn($f) => "$f = ?", $fields)) . ' WHERE id = ?', [...$values, $id]);
        } else {
            db_exec('INSERT INTO posts (' . implode(', ', $fields) . ') VALUES (' . implode(', ', array_fill(0, count($fields), '?')) . ')', $values);
            $id = (int) db()->lastInsertId();
        }
        flash('success', 'Article saved.');
        redirect('post-edit.php?id=' . $id);
    }
}

$localPublished = $p['published_at'] ? format_dt($p['published_at'], 'Y-m-d\TH:i') : '';
$pageTitle = $id ? 'Edit Article' : 'New Article';
$active = 'posts';
require __DIR__ . '/partials/header.php';
?>
<nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="posts.php">Insights</a></li><li class="breadcrumb-item active" aria-current="page"><?= e($id ? $p['title'] : 'New') ?></li></ol></nav>
<?php foreach ($errors as $err): ?><div class="alert alert-danger py-2"><?= e($err) ?></div><?php endforeach; ?>
<form method="post" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <div class="row g-4">
    <div class="col-xl-8">
      <div class="card shadow-sm border-0 mb-4"><div class="card-body">
        <div class="mb-3"><label class="form-label fw-semibold" for="title">Title</label><input class="form-control form-control-lg" id="title" name="title" value="<?= e($p['title']) ?>" required maxlength="200" data-slug-source="#slug"></div>
        <div class="mb-3"><label class="form-label fw-semibold" for="slug">URL slug</label><div class="input-group"><span class="input-group-text">/insights/</span><input class="form-control" id="slug" name="slug" value="<?= e($p['slug']) ?>" maxlength="190" data-auto="<?= $id ? '0' : '1' ?>"></div></div>
        <div class="mb-3"><label class="form-label fw-semibold" for="excerpt">Excerpt</label><textarea class="form-control" id="excerpt" name="excerpt" rows="2" maxlength="500" data-counter><?= e($p['excerpt']) ?></textarea><div class="form-text">Short summary shown on cards.</div></div>
        <label class="form-label fw-semibold">Content</label>
        <?= rich_editor('content', $p['content'], 18) ?>
      </div></div>
      <div class="card shadow-sm border-0"><div class="card-header bg-white"><h2 class="h6 mb-0">SEO</h2></div><div class="card-body">
        <div class="mb-3"><label class="form-label fw-semibold" for="seo_title">SEO title</label><input class="form-control" id="seo_title" name="seo_title" value="<?= e($p['seo_title']) ?>" maxlength="190" data-counter data-ideal="60"></div>
        <div class="mb-3"><label class="form-label fw-semibold" for="meta_description">Meta description</label><textarea class="form-control" id="meta_description" name="meta_description" rows="2" maxlength="320" data-counter data-ideal="160"><?= e($p['meta_description']) ?></textarea></div>
        <div><label class="form-label fw-semibold" for="meta_keywords">Meta keywords</label><input class="form-control" id="meta_keywords" name="meta_keywords" value="<?= e($p['meta_keywords']) ?>" maxlength="320"></div>
      </div></div>
    </div>
    <div class="col-xl-4">
      <div class="card shadow-sm border-0 mb-4"><div class="card-header bg-white"><h2 class="h6 mb-0">Publish</h2></div><div class="card-body">
        <div class="mb-3"><label class="form-label fw-semibold" for="status">Status</label><select class="form-select" id="status" name="status"><option value="draft"<?= $p['status'] === 'draft' ? ' selected' : '' ?>>Draft</option><option value="published"<?= $p['status'] === 'published' ? ' selected' : '' ?>>Published</option></select></div>
        <div class="mb-3"><label class="form-label fw-semibold" for="published_at">Publish date</label><input type="datetime-local" class="form-control" id="published_at" name="published_at" value="<?= e($localPublished) ?>"><div class="form-text">Future dates schedule the article.</div></div>
        <div class="mb-3"><label class="form-label fw-semibold" for="author_name">Author</label><input class="form-control" id="author_name" name="author_name" value="<?= e($p['author_name']) ?>" maxlength="120"></div>
        <button class="btn btn-brand w-100"><i class="bi bi-check2"></i> Save article</button>
      </div></div>
      <div class="card shadow-sm border-0"><div class="card-header bg-white"><h2 class="h6 mb-0">Featured image</h2></div><div class="card-body">
        <?= image_field('image', $p['image'], 'Image') ?>
        <label class="form-label fw-semibold" for="image_alt">Alt text</label><input class="form-control" id="image_alt" name="image_alt" value="<?= e($p['image_alt']) ?>" maxlength="255">
      </div></div>
    </div>
  </div>
</form>
<?= media_library_modal() ?>
<?php require __DIR__ . '/partials/footer.php';
