<?php
declare(strict_types=1);
require __DIR__ . '/_init.php';
$admin = require_admin();

$id = (int) ($_GET['id'] ?? 0);
$sec = db_one('SELECT s.*, p.title AS page_title, p.page_key FROM page_sections s JOIN pages p ON p.id = s.page_id WHERE s.id = ?', [$id]);
if (!$sec) {
    flash('danger', 'Section not found.');
    redirect('pages.php');
}

/*
 * Which fields each section uses. Anything not listed is hidden in the form
 * so editors only see what actually appears on the website.
 */
$key = $sec['page_key'] . '.' . $sec['section_key'];
$layout = [
    'home.hero'         => ['eyebrow', 'title', 'subtitle', 'content', 'image', 'button', 'button2'],
    'home.about'        => ['eyebrow', 'title', 'content', 'image', 'button', 'items'],
    'home.services'     => ['eyebrow', 'title', 'subtitle'],
    'home.spotlight'    => ['eyebrow', 'title', 'content', 'image', 'button', 'items'],
    'home.calibration'  => ['eyebrow', 'title', 'subtitle', 'button', 'items'],
    'home.why'          => ['eyebrow', 'title', 'subtitle', 'items'],
    'home.credentials'  => ['eyebrow', 'title', 'subtitle', 'items'],
    'home.process'      => ['eyebrow', 'title', 'subtitle', 'items'],
    'home.cta'          => ['title', 'subtitle', 'button', 'button2'],
    'home.insights'     => ['eyebrow', 'title', 'subtitle'],
    'home.testimonials' => ['eyebrow', 'title', 'subtitle'],
    'home.contact'      => ['eyebrow', 'title', 'subtitle'],
    'home.footer'       => ['content'],
    'about.background'  => ['eyebrow', 'title', 'content', 'image'],
    'about.mission_vision' => ['eyebrow', 'title', 'items'],
    'about.values'      => ['eyebrow', 'title', 'subtitle', 'items'],
    'about.expertise'   => ['eyebrow', 'title', 'subtitle', 'items'],
    'about.quality'     => ['eyebrow', 'title', 'content'],
    'about.approach'    => ['eyebrow', 'title', 'content'],
    'about.trust'       => ['eyebrow', 'title', 'subtitle', 'items'],
];
$fields = $layout[$key] ?? ['eyebrow', 'title', 'subtitle'];
$has = fn(string $f) => in_array($f, $fields, true);
$errors = [];

if (is_post()) {
    verify_csrf();
    $data = [
        'eyebrow'      => clean_str($_POST['eyebrow'] ?? '', 190),
        'title'        => clean_str($_POST['title'] ?? '', 255),
        'subtitle'     => clean_str($_POST['subtitle'] ?? '', 500),
        'content'      => clean_str($_POST['content'] ?? '', 10000),
        'image_alt'    => clean_str($_POST['image_alt'] ?? '', 255),
        'button_text'  => clean_str($_POST['button_text'] ?? '', 100),
        'button_link'  => safe_link($_POST['button_link'] ?? ''),
        'button2_text' => clean_str($_POST['button2_text'] ?? '', 100),
        'button2_link' => safe_link($_POST['button2_link'] ?? ''),
        'is_visible'   => !empty($_POST['is_visible']) ? 1 : 0,
    ];
    foreach (['button_link', 'button2_link'] as $lf) {
        if (trim((string) ($_POST[$lf] ?? '')) !== '' && $data[$lf] === '') {
            $errors[] = 'Button links must start with "/" (internal page) or "https://".';
        }
    }
    $items = $has('items') ? json_encode(posted_items(), JSON_UNESCAPED_UNICODE) : $sec['items'];
    $image = $sec['image'];
    if ($has('image') && !$errors) {
        try {
            $image = process_image_field('image', $sec['image'], 'pages', $admin, $data['image_alt']);
        } catch (RuntimeException $ex) {
            $errors[] = $ex->getMessage();
        }
    }
    if (!$errors) {
        // only update the fields this section uses
        $map = ['eyebrow' => ['eyebrow'], 'title' => ['title'], 'subtitle' => ['subtitle'], 'content' => ['content'],
            'image' => ['image_alt'], 'button' => ['button_text', 'button_link'], 'button2' => ['button2_text', 'button2_link']];
        $set = ['is_visible = ?'];
        $params = [$data['is_visible']];
        foreach ($map as $f => $cols) {
            if ($has($f)) {
                foreach ($cols as $c) {
                    $set[] = "$c = ?";
                    $params[] = $data[$c] === '' ? null : $data[$c];
                }
            }
        }
        if ($has('image')) {
            $set[] = 'image = ?';
            $params[] = $image;
        }
        if ($has('items')) {
            $set[] = 'items = ?';
            $params[] = $items;
        }
        $params[] = $id;
        db_exec('UPDATE page_sections SET ' . implode(', ', $set) . ' WHERE id = ?', $params);
        db_exec('UPDATE pages SET updated_at = CURRENT_TIMESTAMP WHERE id = ?', [$sec['page_id']]);
        flash('success', '"' . $sec['label'] . '" saved.');
        redirect('section-edit.php?id=' . $id);
    }
    $sec = array_merge($sec, $data, ['items' => $items, 'image' => $image]);
}

$pageTitle = 'Edit: ' . $sec['label'];
$active = 'pages';
require __DIR__ . '/partials/header.php';
?>
<nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="pages.php">Page Content</a></li><li class="breadcrumb-item"><?= e($sec['page_title']) ?></li><li class="breadcrumb-item active" aria-current="page"><?= e($sec['label']) ?></li></ol></nav>
<?php foreach ($errors as $err): ?><div class="alert alert-danger py-2" role="alert"><?= e($err) ?></div><?php endforeach; ?>

<form method="post" enctype="multipart/form-data" class="card shadow-sm border-0" id="sectionForm">
  <?= csrf_field() ?>
  <div class="card-body">
    <div class="row g-3">
      <?php if ($has('eyebrow')): ?>
        <div class="col-md-6"><label class="form-label fw-semibold" for="eyebrow">Small heading (eyebrow)</label><input class="form-control" id="eyebrow" name="eyebrow" value="<?= e($sec['eyebrow']) ?>" maxlength="190"></div>
      <?php endif; ?>
      <?php if ($has('title')): ?>
        <div class="col-12"><label class="form-label fw-semibold" for="title">Title <?= $key === 'home.hero' ? '<span class="badge text-bg-info">Homepage H1</span>' : '' ?></label><input class="form-control form-control-lg" id="title" name="title" value="<?= e($sec['title']) ?>" maxlength="255"></div>
      <?php endif; ?>
      <?php if ($has('subtitle')): ?>
        <div class="col-12"><label class="form-label fw-semibold" for="subtitle">Subtitle</label><textarea class="form-control" id="subtitle" name="subtitle" rows="2" maxlength="500"><?= e($sec['subtitle']) ?></textarea></div>
      <?php endif; ?>
      <?php if ($has('content')): ?>
        <div class="col-12"><label class="form-label fw-semibold" for="content">Text</label><textarea class="form-control" id="content" name="content" rows="7"><?= e($sec['content']) ?></textarea><div class="form-text">Leave a blank line between paragraphs.</div></div>
      <?php endif; ?>
      <?php if ($has('button')): ?>
        <div class="col-md-6"><label class="form-label fw-semibold" for="button_text">Primary button text</label><input class="form-control" id="button_text" name="button_text" value="<?= e($sec['button_text']) ?>" maxlength="100"></div>
        <div class="col-md-6"><label class="form-label fw-semibold" for="button_link">Primary button link</label><input class="form-control" id="button_link" name="button_link" value="<?= e($sec['button_link']) ?>" maxlength="255" placeholder="/services"></div>
      <?php endif; ?>
      <?php if ($has('button2')): ?>
        <div class="col-md-6"><label class="form-label fw-semibold" for="button2_text">Secondary button text</label><input class="form-control" id="button2_text" name="button2_text" value="<?= e($sec['button2_text']) ?>" maxlength="100"></div>
        <div class="col-md-6"><label class="form-label fw-semibold" for="button2_link">Secondary button link</label><input class="form-control" id="button2_link" name="button2_link" value="<?= e($sec['button2_link']) ?>" maxlength="255" placeholder="/contact"></div>
      <?php endif; ?>
      <?php if ($has('image')): ?>
        <div class="col-12">
          <?= image_field('image', $sec['image'], $key === 'home.hero' ? 'Background image (optional – a dark overlay is applied)' : 'Image') ?>
          <label class="form-label fw-semibold" for="image_alt">Image alt text</label>
          <input class="form-control" id="image_alt" name="image_alt" value="<?= e($sec['image_alt']) ?>" maxlength="255">
        </div>
      <?php endif; ?>
    </div>

    <?php if ($has('items')): ?>
      <hr class="my-4">
      <h2 class="h6">List items</h2>
      <p class="small text-muted">Cards shown in this section. Drag order with the arrow buttons.</p>
      <input type="hidden" name="items_json" id="items_json" value="<?= e(json_encode(json_list($sec['items']), JSON_UNESCAPED_UNICODE)) ?>">
      <div id="itemsEditor" class="items-editor" data-icons="<?= e(json_encode(admin_icons())) ?>"></div>
      <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="addItem"><i class="bi bi-plus-lg"></i> Add item</button>
    <?php endif; ?>

    <div class="form-check form-switch mt-4">
      <input class="form-check-input" type="checkbox" role="switch" id="is_visible" name="is_visible" value="1"<?= $sec['is_visible'] ? ' checked' : '' ?>>
      <label class="form-check-label" for="is_visible">Show this section on the website</label>
    </div>
  </div>
  <div class="card-footer bg-white d-flex gap-2">
    <button class="btn btn-brand"><i class="bi bi-check2"></i> Save section</button>
    <a href="pages.php" class="btn btn-outline-secondary">Back</a>
  </div>
</form>
<?php if ($has('image')) echo media_library_modal(); ?>
<?php require __DIR__ . '/partials/footer.php';
