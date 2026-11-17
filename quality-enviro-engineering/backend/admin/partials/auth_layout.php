<?php
/** Minimal layout for login / password reset. Call auth_head($title) and auth_foot(). */
function auth_head(string $title): void
{
    $logo = upload_url(setting('logo'));
    ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?> · <?= e(setting('company_name', 'QES')) ?></title>
<?php if ($logo): ?><link rel="icon" href="<?= e($logo) ?>"><?php endif; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="assets/admin.css?v=1" rel="stylesheet">
</head>
<body class="auth-body">
<main class="auth-card">
  <div class="text-center mb-4">
    <?php if ($logo): ?><img src="<?= e($logo) ?>" alt="<?= e(setting('company_name')) ?>" class="auth-logo"><?php endif; ?>
    <h1 class="h4 mt-3 mb-1"><?= e($title) ?></h1>
    <p class="text-muted small mb-0"><?= e(setting('company_name', '')) ?> · Admin</p>
  </div>
  <?php foreach (take_flashes() as [$type, $msg]): ?>
    <div class="alert alert-<?= e($type) ?>" role="alert"><?= e($msg) ?></div>
  <?php endforeach;
}

function auth_foot(): void
{
    ?>
</main>
</body>
</html>
<?php
}
