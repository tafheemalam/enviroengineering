<?php
declare(strict_types=1);
require __DIR__ . '/_init.php';
$admin = require_admin();

$errors = [];
if (is_post()) {
    verify_csrf();
    $action = $_POST['action'] ?? 'save';

    if ($action === 'test_email') {
        $to = setting('notify_email') ?: setting('company_email');
        $ok = $to && send_mail($to, 'Test email from your website', email_layout('Email is working', '<p>This is a test message sent from the admin panel. Your email settings are configured correctly.</p>'));
        flash($ok ? 'success' : 'danger', $ok ? "Test email sent to $to." : 'Sending failed. Check SMTP settings and the server error log.');
        redirect('settings.php#email');
    }

    $in = fn(string $k, int $max = 255) => clean_str($_POST[$k] ?? '', $max);
    $values = [
        // key => [value, group, public]
        'company_name'      => [$in('company_name', 150), 'general', 1],
        'company_short'     => [$in('company_short', 20), 'general', 1],
        'tagline'           => [$in('tagline', 255), 'general', 1],
        'site_url'          => [rtrim($in('site_url', 255), '/'), 'general', 1],
        'timezone'          => [in_array($_POST['timezone'] ?? '', timezone_identifiers_list(), true) ? $_POST['timezone'] : 'UTC', 'general', 0],
        'copyright_text'    => [$in('copyright_text', 255), 'general', 1],
        'company_email'     => [$in('company_email', 190), 'contact', 1],
        'notify_email'      => [$in('notify_email', 190), 'contact', 0],
        'phone'             => [$in('phone', 40), 'contact', 1],
        'phone_secondary'   => [$in('phone_secondary', 40), 'contact', 1],
        'service_area'      => [$in('service_area', 190), 'contact', 1],
        'whatsapp'          => [preg_replace('/[^\d+]/', '', $in('whatsapp', 30)), 'contact', 1],
        'address'           => [$in('address', 500), 'contact', 1],
        'business_hours'    => [$in('business_hours', 190), 'contact', 1],
        'map_embed_url'     => [safe_map_url($_POST['map_embed_url'] ?? ''), 'contact', 1],
        'show_insights'     => [!empty($_POST['show_insights']) ? '1' : '0', 'features', 1],
        'show_testimonials' => [!empty($_POST['show_testimonials']) ? '1' : '0', 'features', 1],
        'show_whatsapp'     => [!empty($_POST['show_whatsapp']) ? '1' : '0', 'features', 1],
        'send_confirmation' => [!empty($_POST['send_confirmation']) ? '1' : '0', 'email', 0],
        'smtp_host'         => [$in('smtp_host', 190), 'email', 0],
        'smtp_port'         => [(string) max(1, min(65535, (int) ($_POST['smtp_port'] ?? 587))), 'email', 0],
        'smtp_secure'       => [in_array($_POST['smtp_secure'] ?? '', ['tls', 'ssl', 'none'], true) ? $_POST['smtp_secure'] : 'tls', 'email', 0],
        'smtp_user'         => [$in('smtp_user', 190), 'email', 0],
        'smtp_from_email'   => [$in('smtp_from_email', 190), 'email', 0],
        'smtp_from_name'    => [$in('smtp_from_name', 150), 'email', 0],
        'ga_measurement_id' => [strtoupper($in('ga_measurement_id', 30)), 'analytics', 1],
        'gsc_verification'  => [$in('gsc_verification', 120), 'analytics', 0],
    ];

    if ($values['company_name'][0] === '') {
        $errors[] = 'Company name is required.';
    }
    foreach (['company_email', 'notify_email', 'smtp_from_email'] as $k) {
        if ($values[$k][0] !== '' && !filter_var($values[$k][0], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email for " . str_replace('_', ' ', $k) . '.';
        }
    }
    if ($values['site_url'][0] !== '' && !filter_var($values['site_url'][0], FILTER_VALIDATE_URL)) {
        $errors[] = 'Website URL must be a full URL, e.g. https://www.example.com';
    }
    if (trim((string) ($_POST['map_embed_url'] ?? '')) !== '' && $values['map_embed_url'][0] === '') {
        $errors[] = 'Google Maps: paste the "Embed a map" link (https://www.google.com/maps/embed?...) or the full iframe code.';
    }
    if ($values['ga_measurement_id'][0] !== '' && !preg_match('/^G-[A-Z0-9]{4,20}$/', $values['ga_measurement_id'][0])) {
        $errors[] = 'Google Analytics ID should look like G-XXXXXXXXXX.';
    }
    // allow pasting the full meta tag for Search Console
    if (preg_match('/content=["\']([^"\']+)["\']/', (string) ($_POST['gsc_verification'] ?? ''), $m)) {
        $values['gsc_verification'][0] = clean_str($m[1], 120);
    }

    if (!$errors) {
        try {
            $logo = process_image_field('logo', setting('logo'), 'brand', $admin, 'Company logo');
            $favicon = process_image_field('favicon', setting('favicon'), 'brand', $admin, 'Favicon');
            $values['logo'] = [$logo, 'general', 1];
            $values['favicon'] = [$favicon, 'general', 1];
        } catch (RuntimeException $ex) {
            $errors[] = $ex->getMessage();
        }
    }
    if (!$errors) {
        // only overwrite SMTP password when a new one is typed
        if (($_POST['smtp_pass'] ?? '') !== '') {
            $values['smtp_pass'] = [(string) $_POST['smtp_pass'], 'email', 0];
        }
        if (!empty($_POST['smtp_pass_clear'])) {
            $values['smtp_pass'] = ['', 'email', 0];
        }
        db()->beginTransaction();
        foreach ($values as $k => [$v, $g, $p]) {
            save_setting($k, $v, $g, $p);
        }
        db()->commit();
        flash('success', 'Settings saved.');
        redirect('settings.php');
    }
}

$s = settings(true);
$val = fn(string $k) => e($_POST[$k] ?? $s[$k] ?? '');
$checked = fn(string $k) => (($s[$k] ?? '0') === '1') ? ' checked' : '';

$pageTitle = 'Website Settings';
$active = 'settings';
require __DIR__ . '/partials/header.php';
?>
<?php foreach ($errors as $err): ?><div class="alert alert-danger py-2"><?= e($err) ?></div><?php endforeach; ?>

<form method="post" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <div class="row g-4">
    <div class="col-xl-6">
      <section class="card shadow-sm border-0 mb-4" id="general">
        <div class="card-header bg-white"><h2 class="h6 mb-0"><i class="bi bi-building"></i> Company</h2></div>
        <div class="card-body row g-3">
          <div class="col-md-8"><label class="form-label fw-semibold" for="company_name">Company name</label><input class="form-control" id="company_name" name="company_name" value="<?= $val('company_name') ?>" required></div>
          <div class="col-md-4"><label class="form-label fw-semibold" for="company_short">Short name</label><input class="form-control" id="company_short" name="company_short" value="<?= $val('company_short') ?>" maxlength="20"></div>
          <div class="col-12"><label class="form-label fw-semibold" for="tagline">Tagline</label><input class="form-control" id="tagline" name="tagline" value="<?= $val('tagline') ?>"></div>
          <div class="col-md-7"><label class="form-label fw-semibold" for="site_url">Website URL</label><input class="form-control" id="site_url" name="site_url" value="<?= $val('site_url') ?>" placeholder="https://www.your-domain.com"><div class="form-text">Used for canonical URLs, sitemap and social sharing.</div></div>
          <div class="col-md-5"><label class="form-label fw-semibold" for="timezone">Timezone</label>
            <select class="form-select" id="timezone" name="timezone"><?php foreach (timezone_identifiers_list() as $tz): ?><option<?= ($s['timezone'] ?? 'UTC') === $tz ? ' selected' : '' ?>><?= e($tz) ?></option><?php endforeach; ?></select></div>
          <div class="col-12"><label class="form-label fw-semibold" for="copyright_text">Copyright text</label><div class="input-group"><span class="input-group-text">&copy; <?= date('Y') ?></span><input class="form-control" id="copyright_text" name="copyright_text" value="<?= $val('copyright_text') ?>"></div><div class="form-text">The year updates automatically.</div></div>
          <div class="col-md-6"><?= image_field('logo', $s['logo'] ?? null, 'Company logo') ?></div>
          <div class="col-md-6"><?= image_field('favicon', $s['favicon'] ?? null, 'Favicon', 'Square image, at least 64×64 (PNG or ICO).') ?></div>
        </div>
      </section>

      <section class="card shadow-sm border-0 mb-4" id="features">
        <div class="card-header bg-white"><h2 class="h6 mb-0"><i class="bi bi-toggles"></i> Website features</h2></div>
        <div class="card-body">
          <div class="form-check form-switch mb-2"><input class="form-check-input" type="checkbox" role="switch" id="show_insights" name="show_insights" value="1"<?= $checked('show_insights') ?>><label class="form-check-label" for="show_insights">Show Insights / blog section</label></div>
          <div class="form-check form-switch mb-2"><input class="form-check-input" type="checkbox" role="switch" id="show_testimonials" name="show_testimonials" value="1"<?= $checked('show_testimonials') ?>><label class="form-check-label" for="show_testimonials">Show testimonials on the homepage</label></div>
          <div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="show_whatsapp" name="show_whatsapp" value="1"<?= $checked('show_whatsapp') ?>><label class="form-check-label" for="show_whatsapp">Show floating WhatsApp button (requires WhatsApp number)</label></div>
        </div>
      </section>

      <section class="card shadow-sm border-0 mb-4" id="analytics">
        <div class="card-header bg-white"><h2 class="h6 mb-0"><i class="bi bi-graph-up"></i> Google Analytics &amp; Search Console</h2></div>
        <div class="card-body row g-3">
          <div class="col-md-6"><label class="form-label fw-semibold" for="ga_measurement_id">GA4 Measurement ID</label><input class="form-control" id="ga_measurement_id" name="ga_measurement_id" value="<?= $val('ga_measurement_id') ?>" placeholder="G-XXXXXXXXXX"></div>
          <div class="col-md-6"><label class="form-label fw-semibold" for="gsc_verification">Search Console verification</label><input class="form-control" id="gsc_verification" name="gsc_verification" value="<?= $val('gsc_verification') ?>" placeholder="Paste the meta tag or its content"></div>
          <div class="col-12 form-text mt-0">After verifying, submit <code><?= e(site_url('/sitemap.xml')) ?></code> in Search Console.</div>
        </div>
      </section>
    </div>

    <div class="col-xl-6">
      <section class="card shadow-sm border-0 mb-4" id="contact">
        <div class="card-header bg-white"><h2 class="h6 mb-0"><i class="bi bi-telephone"></i> Contact details &amp; map</h2></div>
        <div class="card-body row g-3">
          <div class="col-md-6"><label class="form-label fw-semibold" for="company_email">Public email</label><input type="email" class="form-control" id="company_email" name="company_email" value="<?= $val('company_email') ?>"></div>
          <div class="col-md-6"><label class="form-label fw-semibold" for="notify_email">Inquiry notification email</label><input type="email" class="form-control" id="notify_email" name="notify_email" value="<?= $val('notify_email') ?>"><div class="form-text">Not shown publicly.</div></div>
          <div class="col-md-6"><label class="form-label fw-semibold" for="phone">Phone number</label><input class="form-control" id="phone" name="phone" value="<?= $val('phone') ?>"></div>
          <div class="col-md-6"><label class="form-label fw-semibold" for="phone_secondary">Second phone number <span class="text-muted fw-normal">(optional)</span></label><input class="form-control" id="phone_secondary" name="phone_secondary" value="<?= $val('phone_secondary') ?>"></div>
          <div class="col-md-6"><label class="form-label fw-semibold" for="whatsapp">WhatsApp number</label><input class="form-control" id="whatsapp" name="whatsapp" value="<?= $val('whatsapp') ?>" placeholder="International format, e.g. +15551234567"></div>
          <div class="col-12"><label class="form-label fw-semibold" for="address">Office address</label><textarea class="form-control" id="address" name="address" rows="2"><?= $val('address') ?></textarea></div>
          <div class="col-md-6"><label class="form-label fw-semibold" for="business_hours">Business hours <span class="text-muted fw-normal">(leave blank to hide)</span></label><input class="form-control" id="business_hours" name="business_hours" value="<?= $val('business_hours') ?>"></div>
          <div class="col-md-6"><label class="form-label fw-semibold" for="service_area">Service area</label><input class="form-control" id="service_area" name="service_area" value="<?= $val('service_area') ?>" placeholder="e.g. Karachi and projects across Pakistan"></div>
          <div class="col-12">
            <label class="form-label fw-semibold" for="map_embed_url">Google Maps embed</label>
            <textarea class="form-control font-monospace small" id="map_embed_url" name="map_embed_url" rows="3" placeholder="https://www.google.com/maps/embed?pb=..."><?= $val('map_embed_url') ?></textarea>
            <div class="form-text">In Google Maps: find your office → <em>Share</em> → <em>Embed a map</em> → copy the HTML and paste it here (the link is extracted automatically).</div>
            <?php if (!empty($s['map_embed_url'])): ?><div class="ratio ratio-16x9 mt-2 rounded overflow-hidden border"><iframe src="<?= e($s['map_embed_url']) ?>" title="Map preview" loading="lazy"></iframe></div><?php endif; ?>
          </div>
        </div>
      </section>

      <section class="card shadow-sm border-0 mb-4" id="email">
        <div class="card-header bg-white"><h2 class="h6 mb-0"><i class="bi bi-envelope-at"></i> Email (SMTP)</h2></div>
        <div class="card-body row g-3">
          <div class="col-12 form-text mt-0">Used for inquiry notifications, visitor confirmations and password resets. Without SMTP, PHP <code>mail()</code> is used, which many hosts block.</div>
          <div class="col-md-8"><label class="form-label fw-semibold" for="smtp_host">SMTP host</label><input class="form-control" id="smtp_host" name="smtp_host" value="<?= $val('smtp_host') ?>" placeholder="smtp.your-provider.com"></div>
          <div class="col-md-4"><label class="form-label fw-semibold" for="smtp_port">Port</label><input type="number" class="form-control" id="smtp_port" name="smtp_port" value="<?= $val('smtp_port') ?>"></div>
          <div class="col-md-4"><label class="form-label fw-semibold" for="smtp_secure">Encryption</label>
            <select class="form-select" id="smtp_secure" name="smtp_secure"><?php foreach (['tls' => 'STARTTLS (587)', 'ssl' => 'SSL/TLS (465)', 'none' => 'None'] as $k => $l): ?><option value="<?= $k ?>"<?= ($s['smtp_secure'] ?? 'tls') === $k ? ' selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></div>
          <div class="col-md-8"><label class="form-label fw-semibold" for="smtp_user">Username</label><input class="form-control" id="smtp_user" name="smtp_user" value="<?= $val('smtp_user') ?>" autocomplete="off"></div>
          <div class="col-md-8"><label class="form-label fw-semibold" for="smtp_pass">Password</label><input type="password" class="form-control" id="smtp_pass" name="smtp_pass" value="" autocomplete="new-password" placeholder="<?= !empty($s['smtp_pass']) ? '•••••••• (saved – leave blank to keep)' : '' ?>"></div>
          <div class="col-md-4 d-flex align-items-end"><div class="form-check"><input class="form-check-input" type="checkbox" id="smtp_pass_clear" name="smtp_pass_clear" value="1"><label class="form-check-label small" for="smtp_pass_clear">Clear saved password</label></div></div>
          <div class="col-md-6"><label class="form-label fw-semibold" for="smtp_from_email">From email</label><input type="email" class="form-control" id="smtp_from_email" name="smtp_from_email" value="<?= $val('smtp_from_email') ?>"></div>
          <div class="col-md-6"><label class="form-label fw-semibold" for="smtp_from_name">From name</label><input class="form-control" id="smtp_from_name" name="smtp_from_name" value="<?= $val('smtp_from_name') ?>"></div>
          <div class="col-12"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="send_confirmation" name="send_confirmation" value="1"<?= $checked('send_confirmation') ?>><label class="form-check-label" for="send_confirmation">Send a confirmation email to visitors after they submit a form</label></div></div>
        </div>
        <div class="card-footer bg-white">
          <button class="btn btn-sm btn-outline-secondary" name="action" value="test_email" formnovalidate><i class="bi bi-send"></i> Send test email (uses saved settings)</button>
        </div>
      </section>
    </div>
  </div>
  <div class="save-bar"><button class="btn btn-brand btn-lg"><i class="bi bi-check2"></i> Save settings</button></div>
</form>
<?= media_library_modal() ?>
<?php require __DIR__ . '/partials/footer.php';
