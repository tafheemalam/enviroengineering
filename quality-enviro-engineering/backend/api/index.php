<?php
/**
 * QES public REST API
 *
 *  GET  /api/bootstrap               settings, social links, nav services
 *  GET  /api/settings                public settings
 *  GET  /api/home                    home sections + featured services + latest posts + testimonials
 *  GET  /api/pages/{key}             page + sections (about, contact, services, quote, insights, privacy, terms)
 *  GET  /api/services                published services
 *  GET  /api/services/{slug}         single service (detail)
 *  GET  /api/posts?page=&limit=      published insights
 *  GET  /api/posts/{slug}            single insight
 *  GET  /api/testimonials            published testimonials
 *  GET  /api/seo/{key}               SEO meta for a page key
 *  POST /api/contact                 submit contact inquiry
 *  POST /api/quote                   submit quotation request
 */
declare(strict_types=1);

require dirname(__DIR__) . '/includes/bootstrap.php';
require_once QES_ROOT . '/includes/repository.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

// ---- CORS (only for configured origins, e.g. the Vite dev server) ----
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = config('app.cors', []);
if ($origin !== '' && in_array($origin, $allowedOrigins, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Access-Control-Max-Age: 600');
}
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method === 'OPTIONS') {
    http_response_code(204);
    exit;
}

function respond($data, int $status = 200, int $cacheSeconds = 0): never
{
    http_response_code($status);
    if ($cacheSeconds > 0 && $status === 200) {
        header('Cache-Control: public, max-age=' . $cacheSeconds);
    } else {
        header('Cache-Control: no-store');
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function fail(string $message, int $status = 400, array $errors = []): never
{
    respond(['success' => false, 'message' => $message] + ($errors ? ['errors' => $errors] : []), $status);
}

// ---- Route resolution ------------------------------------------------
$route = $_GET['route'] ?? null;
if ($route === null) {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $route = preg_replace('#^.*?/api/?#', '', $path);
}
$route = trim((string) $route, '/');
$segments = $route === '' ? [] : explode('/', $route);
$resource = $segments[0] ?? '';
$param = isset($segments[1]) ? rawurldecode($segments[1]) : null;
if ($param !== null && !preg_match('/^[a-z0-9_-]{1,190}$/i', $param)) {
    fail('Not found', 404);
}

const CACHE = 60; // seconds of browser cache for public GETs

try {
    if ($method === 'GET') {
        switch ($resource) {
            case 'bootstrap':
                respond(['success' => true, 'data' => site_bootstrap()], 200, CACHE);

            case 'settings':
                respond(['success' => true, 'data' => public_settings()], 200, CACHE);

            case 'home':
                $home = get_page('home');
                $settings = public_settings();
                $featured = get_services(true, 6);
                respond(['success' => true, 'data' => [
                    'sections'     => $home['sections'] ?? (object) [],
                    'services'     => $featured ?: get_services(false, 6),
                    'posts'        => $settings['show_insights'] ? get_posts(3)['items'] : [],
                    'testimonials' => $settings['show_testimonials'] ? get_testimonials() : [],
                    'seo'          => get_seo('home'),
                ]], 200, CACHE);

            case 'pages':
                if (!$param) {
                    fail('Page key required', 400);
                }
                $page = get_page($param);
                if (!$page) {
                    fail('Page not found', 404);
                }
                $page['seo'] = get_seo($param);
                respond(['success' => true, 'data' => $page], 200, CACHE);

            case 'services':
                if ($param) {
                    $service = get_service($param);
                    if (!$service) {
                        fail('Service not found', 404);
                    }
                    $service['related'] = array_values(array_filter(get_services(false), fn($s) => $s['slug'] !== $param));
                    $service['related'] = array_slice($service['related'], 0, 3);
                    respond(['success' => true, 'data' => $service], 200, CACHE);
                }
                respond(['success' => true, 'data' => get_services(false)], 200, CACHE);

            case 'posts':
                if (setting('show_insights', '1') !== '1') {
                    fail('Not found', 404);
                }
                if ($param) {
                    $post = get_post($param);
                    if (!$post) {
                        fail('Article not found', 404);
                    }
                    respond(['success' => true, 'data' => $post], 200, CACHE);
                }
                $page = max(1, (int) ($_GET['page'] ?? 1));
                $limit = max(1, min(50, (int) ($_GET['limit'] ?? 9)));
                respond(['success' => true, 'data' => get_posts($limit, $page)], 200, CACHE);

            case 'testimonials':
                respond(['success' => true, 'data' => setting('show_testimonials', '0') === '1' ? get_testimonials() : []], 200, CACHE);

            case 'seo':
                if (!$param) {
                    fail('Page key required', 400);
                }
                respond(['success' => true, 'data' => get_seo($param)], 200, CACHE);

            case '':
                respond(['success' => true, 'name' => 'QES API', 'version' => '1.0']);
        }
        fail('Endpoint not found', 404);
    }

    if ($method === 'POST' && in_array($resource, ['contact', 'quote'], true)) {
        handle_inquiry($resource);
    }

    fail('Method not allowed', 405);
} catch (PDOException $e) {
    error_log('[QES API] DB error: ' . $e->getMessage());
    fail(config('app.debug') ? $e->getMessage() : 'A server error occurred. Please try again later.', 500);
} catch (Throwable $e) {
    error_log('[QES API] ' . $e->getMessage());
    fail(config('app.debug') ? $e->getMessage() : 'A server error occurred. Please try again later.', 500);
}

// ---------------------------------------------------------------------
function handle_inquiry(string $type): never
{
    // Same-origin / allowed-origin check (basic CSRF defence for a public JSON endpoint)
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if ($origin !== '') {
        $host = parse_url($origin, PHP_URL_HOST) . (parse_url($origin, PHP_URL_PORT) ? ':' . parse_url($origin, PHP_URL_PORT) : '');
        if ($host !== ($_SERVER['HTTP_HOST'] ?? '') && !in_array($origin, config('app.cors', []), true)) {
            fail('Request origin not allowed.', 403);
        }
    }

    $ctype = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($ctype, 'application/json') !== false) {
        $raw = file_get_contents('php://input', false, null, 0, 64 * 1024);
        $in = json_decode((string) $raw, true);
        if (!is_array($in)) {
            fail('Invalid request body.');
        }
    } else {
        $in = $_POST;
    }

    // Honeypot – real users never fill this hidden field. Pretend success.
    if (!empty($in['website'])) {
        respond(['success' => true, 'message' => 'Thank you! Your message has been sent.']);
    }
    // Timing check – forms submitted in < N seconds are almost always bots
    $started = (int) ($in['_started'] ?? 0);
    if ($started > 0 && (int) floor(microtime(true) * 1000) - $started < config('security.min_form_seconds') * 1000) {
        fail('Please take a moment to review your message and try again.', 429);
    }

    $data = [
        'full_name'    => clean_str($in['full_name'] ?? '', 120),
        'company_name' => clean_str($in['company_name'] ?? '', 160),
        'email'        => clean_str($in['email'] ?? '', 190),
        'phone'        => clean_str($in['phone'] ?? '', 40),
        'service_id'   => (int) ($in['service_id'] ?? 0),
        'subject'      => clean_str($in['subject'] ?? '', 200),
        'message'      => clean_str($in['message'] ?? '', 5000),
        'project_location' => clean_str($in['project_location'] ?? '', 200),
        'timeline'     => clean_str($in['timeline'] ?? '', 100),
        'budget_range' => clean_str($in['budget_range'] ?? '', 100),
    ];

    $errors = [];
    if (mb_strlen($data['full_name']) < 2) {
        $errors['full_name'] = 'Please enter your full name.';
    }
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }
    if (!preg_match('/^\+?[0-9\s().-]{7,20}$/', $data['phone'])) {
        $errors['phone'] = 'Please enter a valid phone number.';
    }
    if ($type === 'contact' && mb_strlen($data['subject']) < 3) {
        $errors['subject'] = 'Please enter a subject.';
    }
    if (mb_strlen($data['message']) < 10) {
        $errors['message'] = 'Please enter a message of at least 10 characters.';
    }
    $service = null;
    if ($data['service_id'] > 0) {
        $service = db_one("SELECT id, title FROM services WHERE id = ? AND status = 'published'", [$data['service_id']]);
        if (!$service) {
            $errors['service_id'] = 'Please choose a valid service.';
        }
    } elseif ($type === 'quote') {
        $errors['service_id'] = 'Please choose the service you need.';
    }
    if ($errors) {
        fail('Please correct the highlighted fields.', 422, $errors);
    }

    if (!rate_limit_hit('inquiry', (int) config('security.contact_max'), (int) config('security.contact_window'))) {
        fail('Too many submissions from your network. Please try again later or contact us by phone.', 429);
    }

    if ($type === 'quote' && $data['subject'] === '') {
        $data['subject'] = 'Quotation request' . ($service ? ': ' . $service['title'] : '');
    }

    db_exec(
        'INSERT INTO contact_inquiries (inquiry_type, full_name, company_name, email, phone, service_id, service_title, subject, message,
                                        project_location, timeline, budget_range, ip_address, user_agent)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
        [
            $type, $data['full_name'], $data['company_name'] ?: null, $data['email'], $data['phone'],
            $service['id'] ?? null, $service['title'] ?? null, $data['subject'], $data['message'],
            $type === 'quote' ? ($data['project_location'] ?: null) : null,
            $type === 'quote' ? ($data['timeline'] ?: null) : null,
            $type === 'quote' ? ($data['budget_range'] ?: null) : null,
            client_ip(), clean_str($_SERVER['HTTP_USER_AGENT'] ?? '', 255),
        ]
    );
    $id = (int) db()->lastInsertId();

    // ---- Notifications (failures are logged, never shown to the visitor) ----
    $label = $type === 'quote' ? 'Quotation Request' : 'Contact Inquiry';
    $rows = [
        'Name' => $data['full_name'], 'Company' => $data['company_name'], 'Email' => $data['email'],
        'Phone' => $data['phone'], 'Service' => $service['title'] ?? '', 'Subject' => $data['subject'],
    ];
    if ($type === 'quote') {
        $rows += ['Project location' => $data['project_location'], 'Timeline' => $data['timeline'], 'Budget' => $data['budget_range']];
    }
    $table = '<table cellpadding="6" style="border-collapse:collapse;font-size:14px">';
    foreach ($rows as $k => $v) {
        if ($v !== '') {
            $table .= '<tr><td style="color:#5b6b78;width:140px"><strong>' . e($k) . '</strong></td><td>' . e($v) . '</td></tr>';
        }
    }
    $table .= '</table><p style="margin-top:16px"><strong>Message</strong></p><p style="white-space:pre-line">' . e($data['message']) . '</p>';

    $notify = setting('notify_email') ?: setting('company_email');
    if ($notify) {
        send_mail($notify, "New $label #$id – " . $data['subject'],
            email_layout("New $label", $table . '<p style="margin-top:20px;font-size:12px;color:#5b6b78">View it in the admin dashboard under Inquiries.</p>'),
            $data['email'], $data['full_name']);
    }
    if (setting('send_confirmation', '1') === '1') {
        $company = setting('company_name', 'our team');
        send_mail($data['email'], "We received your message – $company",
            email_layout('Thank you for contacting us',
                '<p>Dear ' . e($data['full_name']) . ',</p><p>Thank you for reaching out to ' . e($company) . '. We have received your ' . strtolower($label)
                . ' and a member of our team will respond as soon as possible.</p>'
                . '<p style="color:#5b6b78;font-size:13px">Reference: #' . $id . '</p>'
                . '<p>Kind regards,<br>' . e($company) . '</p>'));
    }

    respond([
        'success' => true,
        'message' => $type === 'quote'
            ? 'Thank you! Your quotation request has been received. We will be in touch shortly.'
            : 'Thank you! Your message has been sent. We will get back to you soon.',
        'reference' => $id,
    ], 201);
}
