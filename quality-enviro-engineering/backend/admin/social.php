<?php
declare(strict_types=1);
require __DIR__ . '/_init.php';
$admin = require_admin();

$links = db_all('SELECT * FROM social_links ORDER BY sort_order, id');
$errors = [];

if (is_post()) {
    verify_csrf();
    $posted = $_POST['links'] ?? [];
    $updates = [];
    foreach ($links as $l) {
        $row = $posted[$l['id']] ?? [];
        $url = clean_str($row['url'] ?? '', 255);
        if ($l['platform'] === 'whatsapp' && $url !== '' && preg_match('/^\+?[\d\s-]{7,20}$/', $url)) {
            $url = 'https://wa.me/' . preg_replace('/\D/', '', $url);   // accept a phone number
        }
        if ($url !== '' && !preg_match('#^https://#i', $url)) {
            $errors[] = $l['label'] . ': URL must start with https://';
            continue;
        }
        $updates[] = [$url ?: null, !empty($row['active']) ? 1 : 0, (int) ($row['order'] ?? 0), $l['id']];
    }
    if (!$errors) {
        $st = db()->prepare('UPDATE social_links SET url = ?, is_active = ?, sort_order = ? WHERE id = ?');
        foreach ($updates as $u) {
            $st->execute($u);
        }
        flash('success', 'Social links saved.');
        redirect('social.php');
    }
}

$icons = ['facebook' => 'facebook', 'linkedin' => 'linkedin', 'instagram' => 'instagram', 'youtube' => 'youtube', 'x' => 'twitter-x', 'whatsapp' => 'whatsapp'];
$pageTitle = 'Social Media Links';
$active = 'social';
require __DIR__ . '/partials/header.php';
?>
<?php foreach ($errors as $err): ?><div class="alert alert-danger py-2"><?= e($err) ?></div><?php endforeach; ?>
<p class="text-muted">Links appear in the header, footer and contact page. Empty or disabled links are hidden. All open safely in a new tab.</p>
<form method="post" class="card shadow-sm border-0">
  <?= csrf_field() ?>
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead class="table-light"><tr><th>Platform</th><th>Profile URL</th><th class="text-center">Active</th><th style="width:100px">Order</th></tr></thead>
      <tbody>
      <?php foreach ($links as $l): $lid = (int) $l['id']; $p = $_POST['links'][$lid] ?? null; ?>
        <tr>
          <td class="text-nowrap"><i class="bi bi-<?= $icons[$l['platform']] ?? 'globe' ?> fs-5 me-2" aria-hidden="true"></i><strong><?= e($l['label']) ?></strong></td>
          <td><label class="visually-hidden" for="url<?= $lid ?>"><?= e($l['label']) ?> URL</label>
            <input class="form-control" id="url<?= $lid ?>" name="links[<?= $lid ?>][url]" value="<?= e($p['url'] ?? $l['url']) ?>" placeholder="<?= $l['platform'] === 'whatsapp' ? 'https://wa.me/15551234567 or phone number' : 'https://' ?>"></td>
          <td class="text-center"><input class="form-check-input" type="checkbox" name="links[<?= $lid ?>][active]" value="1"<?= $l['is_active'] ? ' checked' : '' ?> aria-label="<?= e($l['label']) ?> active"></td>
          <td><input type="number" class="form-control" name="links[<?= $lid ?>][order]" value="<?= (int) $l['sort_order'] ?>" aria-label="<?= e($l['label']) ?> order"></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="card-footer bg-white"><button class="btn btn-brand"><i class="bi bi-check2"></i> Save links</button></div>
</form>
<?php require __DIR__ . '/partials/footer.php';
