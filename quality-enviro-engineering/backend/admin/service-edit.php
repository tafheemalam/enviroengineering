<?php
declare(strict_types=1);
require __DIR__ . '/_init.php';
$admin = require_admin();

$id = (int) ($_GET['id'] ?? 0);
$service = $id ? db_one('SELECT * FROM services WHERE id = ?', [$id]) : null;
if ($id && !$service) {
    flash('danger', 'Service not found.');
    redirect('services.php');
}
$s = $service ?? [
    'title' => '', 'slug' => '', 'short_description' => '', 'full_description' => '', 'benefits' => '[]', 'key_features' => '[]',
    'image' => null, 'image_alt' => '', 'icon' => 'Leaf', 'page_type' => 'detail', 'status' => 'draft', 'is_featured' => 0,
    'sort_order' => (int) db_value('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM services'),
    'seo_title' => '', 'meta_description' => '', 'meta_keywords' => '', 'og_image' => null,
];
$errors = [];

if (is_post()) {
    verify_csrf();
    $s['title'] = clean_str($_POST['title'] ?? '', 180);
    $s['slug'] = slugify(clean_str($_POST['slug'] ?? '', 190) ?: $s['title']);
    $s['short_description'] = clean_str($_POST['short_description'] ?? '', 500);
    $s['full_description'] = sanitize_html($_POST['full_description'] ?? '');
    $s['benefits'] = json_encode(lines_to_array($_POST['benefits'] ?? ''), JSON_UNESCAPED_UNICODE);
    $s['key_features'] = json_encode(lines_to_array($_POST['key_features'] ?? ''), JSON_UNESCAPED_UNICODE);
    $s['image_alt'] = clean_str($_POST['image_alt'] ?? '', 255);
    $s['icon'] = in_array($_POST['icon'] ?? '', admin_icons(), true) ? $_POST['icon'] : null;
    $s['page_type'] = ($_POST['page_type'] ?? '') === 'contact' ? 'contact' : 'detail';
    $s['status'] = ($_POST['status'] ?? '') === 'published' ? 'published' : 'draft';
    $s['is_featured'] = !empty($_POST['is_featured']) ? 1 : 0;
    $s['sort_order'] = (int) ($_POST['sort_order'] ?? 0);
    $s['seo_title'] = clean_str($_POST['seo_title'] ?? '', 190);
    $s['meta_description'] = clean_str($_POST['meta_description'] ?? '', 320);
    $s['meta_keywords'] = clean_str($_POST['meta_keywords'] ?? '', 320);

    if (mb_strlen($s['title']) < 3) {
        $errors[] = 'Title must be at least 3 characters.';
    }
    if (mb_strlen($s['short_description']) < 10) {
        $errors[] = 'Please enter a short description (at least 10 characters).';
    }
    if ($s['page_type'] === 'detail' && $s['status'] === 'published' && trim(strip_tags($s['full_description'])) === '') {
        $errors[] = 'Published detail-page services need a full description.';
    }

    if (!$errors) {
        try {
            $s['image'] = process_image_field('image', $s['image'], 'services', $admin, $s['image_alt']);
            $s['og_image'] = process_image_field('og_image', $s['og_image'], 'services', $admin);
        } catch (RuntimeException $ex) {
            $errors[] = $ex->getMessage();
        }
    }

    if (!$errors) {
        $s['slug'] = unique_slug('services', $s['slug'], $id ?: null);
        $fields = ['title', 'slug', 'short_description', 'full_description', 'benefits', 'key_features', 'image', 'image_alt', 'icon',
            'page_type', 'status', 'is_featured', 'sort_order', 'seo_title', 'meta_description', 'meta_keywords', 'og_image'];
        $values = array_map(fn($f) => $s[$f] === '' ? null : $s[$f], $fields);
        // NOT NULL columns
        $values[array_search('short_description', $fields, true)] = $s['short_description'];
        if ($id) {
            db_exec('UPDATE services SET ' . implode(', ', array_map(fn($f) => "$f = ?", $fields)) . ' WHERE id = ?', [...$values, $id]);
            flash('success', 'Service updated.');
        } else {
            db_exec('INSERT INTO services (' . implode(', ', $fields) . ') VALUES (' . implode(', ', array_fill(0, count($fields), '?')) . ')', $values);
            $id = (int) db()->lastInsertId();
            flash('success', 'Service created.');
        }
        redirect('service-edit.php?id=' . $id);
    }
}

$pageTitle = $id ? 'Edit Service' : 'Add Service';
$active = 'services';
require __DIR__ . '/partials/header.php';
?>
<nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="services.php">Services</a></li><li class="breadcrumb-item active" aria-current="page"><?= e($id ? $s['title'] : 'New') ?></li></ol></nav>
<?php foreach ($errors as $err): ?><div class="alert alert-danger py-2" role="alert"><?= e($err) ?></div><?php endforeach; ?>

<form method="post" enctype="multipart/form-data" novalidate>
  <?= csrf_field() ?>
  <div class="row g-4">
    <div class="col-xl-8">
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white"><h2 class="h6 mb-0">Service content</h2></div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label fw-semibold" for="title">Title <span class="text-danger">*</span></label>
            <input class="form-control form-control-lg" id="title" name="title" value="<?= e($s['title']) ?>" required maxlength="180" data-slug-source="#slug">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold" for="slug">URL slug</label>
            <div class="input-group"><span class="input-group-text">/services/</span><input class="form-control" id="slug" name="slug" value="<?= e($s['slug']) ?>" maxlength="190" pattern="[a-z0-9-]+" data-auto="<?= $id ? '0' : '1' ?>"></div>
            <div class="form-text">Lowercase letters, numbers and hyphens. Leave blank to generate from the title.</div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold" for="short_description">Short description <span class="text-danger">*</span></label>
            <textarea class="form-control" id="short_description" name="short_description" rows="3" maxlength="500" data-counter required><?= e($s['short_description']) ?></textarea>
            <div class="form-text">Shown on service cards (1–2 sentences).</div>
          </div>
          <div class="mb-3 detail-only">
            <label class="form-label fw-semibold">Full description</label>
            <?= rich_editor('full_description', $s['full_description']) ?>
          </div>
          <div class="row g-3 detail-only">
            <div class="col-md-6">
              <label class="form-label fw-semibold" for="key_features">Key features</label>
              <textarea class="form-control" id="key_features" name="key_features" rows="6" placeholder="One per line"><?= e(implode("\n", json_list($s['key_features']))) ?></textarea>
              <div class="form-text">One feature per line.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold" for="benefits">Benefits</label>
              <textarea class="form-control" id="benefits" name="benefits" rows="6" placeholder="One per line"><?= e(implode("\n", json_list($s['benefits']))) ?></textarea>
              <div class="form-text">One benefit per line.</div>
            </div>
          </div>
        </div>
      </div>

      <div class="card shadow-sm border-0">
        <div class="card-header bg-white"><h2 class="h6 mb-0">SEO</h2></div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label fw-semibold" for="seo_title">SEO title</label>
            <input class="form-control" id="seo_title" name="seo_title" value="<?= e($s['seo_title']) ?>" maxlength="190" data-counter data-ideal="60">
            <div class="form-text">Defaults to "<?= e(($s['title'] ?: 'Title') . ' | ' . setting('company_name')) ?>". Ideal: 50–60 characters.</div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold" for="meta_description">Meta description</label>
            <textarea class="form-control" id="meta_description" name="meta_description" rows="2" maxlength="320" data-counter data-ideal="160"><?= e($s['meta_description']) ?></textarea>
            <div class="form-text">Ideal: 120–160 characters. Defaults to the short description.</div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold" for="meta_keywords">Meta keywords</label>
            <input class="form-control" id="meta_keywords" name="meta_keywords" value="<?= e($s['meta_keywords']) ?>" maxlength="320" placeholder="comma, separated, keywords">
          </div>
          <?= image_field('og_image', $s['og_image'], 'Social share image (Open Graph)', 'Optional. Defaults to the featured image. Recommended 1200×630.') ?>
        </div>
      </div>
    </div>

    <div class="col-xl-4">
      <div class="card shadow-sm border-0 mb-4 sticky-xl-top" style="top:80px">
        <div class="card-header bg-white"><h2 class="h6 mb-0">Publish</h2></div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label fw-semibold" for="status">Status</label>
            <select class="form-select" id="status" name="status">
              <option value="draft"<?= $s['status'] === 'draft' ? ' selected' : '' ?>>Draft</option>
              <option value="published"<?= $s['status'] === 'published' ? ' selected' : '' ?>>Published</option>
            </select>
          </div>
          <fieldset class="mb-3">
            <legend class="form-label fw-semibold fs-6">When a visitor clicks this service</legend>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="page_type" id="pt_detail" value="detail"<?= $s['page_type'] === 'detail' ? ' checked' : '' ?>>
              <label class="form-check-label" for="pt_detail"><strong>Open a detail page</strong><br><small class="text-muted">Full description, features, benefits &amp; inquiry button.</small></label>
            </div>
            <div class="form-check mt-2">
              <input class="form-check-input" type="radio" name="page_type" id="pt_contact" value="contact"<?= $s['page_type'] === 'contact' ? ' checked' : '' ?>>
              <label class="form-check-label" for="pt_contact"><strong>Go to Contact Us</strong><br><small class="text-muted">Opens the contact form with this service preselected.</small></label>
            </div>
          </fieldset>
          <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" role="switch" id="is_featured" name="is_featured" value="1"<?= $s['is_featured'] ? ' checked' : '' ?>>
            <label class="form-check-label" for="is_featured">Featured on homepage</label>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold" for="sort_order">Display order</label>
            <input type="number" class="form-control" id="sort_order" name="sort_order" value="<?= (int) $s['sort_order'] ?>">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold" for="icon">Card icon</label>
            <?= icon_select('icon', $s['icon'], 'id="icon"') ?>
          </div>
          <div class="d-grid gap-2">
            <button class="btn btn-brand"><i class="bi bi-check2"></i> Save service</button>
            <?php if ($id && $s['status'] === 'published'): ?>
              <a class="btn btn-outline-secondary" target="_blank" rel="noopener" href="<?= e($s['page_type'] === 'detail' ? '/services/' . $s['slug'] : '/contact?service=' . $s['slug']) ?>"><i class="bi bi-box-arrow-up-right"></i> View on website</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <div class="card shadow-sm border-0">
        <div class="card-header bg-white"><h2 class="h6 mb-0">Featured image</h2></div>
        <div class="card-body">
          <?= image_field('image', $s['image'], 'Service image') ?>
          <label class="form-label fw-semibold" for="image_alt">Image alt text</label>
          <input class="form-control" id="image_alt" name="image_alt" value="<?= e($s['image_alt']) ?>" maxlength="255" placeholder="Describe the image for accessibility & SEO">
        </div>
      </div>
    </div>
  </div>
</form>
<?= media_library_modal() ?>
<?php require __DIR__ . '/partials/footer.php';
