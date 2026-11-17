<?php
declare(strict_types=1);

/**
 * Minimal dependency-free mailer.
 * Uses SMTP (SSL or STARTTLS + AUTH LOGIN) when configured in Admin > Settings,
 * otherwise falls back to PHP mail().
 *
 * Returns true on success; logs failures to the PHP error log.
 */
function send_mail(string $to, string $subject, string $htmlBody, ?string $replyTo = null, ?string $replyName = null): bool
{
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    $fromEmail = setting('smtp_from_email', 'no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
    $fromName  = setting('smtp_from_name', setting('company_name', 'Website'));
    $subject   = mail_header_clean($subject);
    $fromName  = mail_header_clean($fromName);

    $boundary = 'qes_' . bin2hex(random_bytes(12));
    $text = trim(html_entity_decode(strip_tags(preg_replace('#<br\s*/?>|</p>|</tr>|</h\d>#i', "\n", $htmlBody)), ENT_QUOTES, 'UTF-8'));

    $headers = [
        'Date: ' . date('r'),
        'From: ' . mime_encode($fromName) . " <$fromEmail>",
        'To: <' . $to . '>',
        'Subject: ' . mime_encode($subject),
        'Message-ID: <' . bin2hex(random_bytes(16)) . '@' . (explode('@', $fromEmail)[1] ?? 'localhost') . '>',
        'MIME-Version: 1.0',
        'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
    ];
    if ($replyTo && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
        $headers[] = 'Reply-To: ' . ($replyName ? mime_encode(mail_header_clean($replyName)) . ' ' : '') . "<$replyTo>";
    }

    $body = "--$boundary\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
        . chunk_split(base64_encode($text))
        . "--$boundary\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
        . chunk_split(base64_encode($htmlBody))
        . "--$boundary--\r\n";

    $host = setting('smtp_host');
    if (!$host) {
        // mail() takes To and Subject separately
        $mailHeaders = array_filter($headers, fn($h) => !preg_match('/^(To|Subject):/i', $h));
        $ok = @mail($to, mime_encode($subject), $body, implode("\r\n", $mailHeaders));
        if (!$ok) {
            error_log('[QES mail] mail() failed – configure SMTP in Admin > Settings.');
        }
        return $ok;
    }

    try {
        return smtp_send($host, (int) setting('smtp_port', 587), (string) setting('smtp_secure', 'tls'),
            (string) setting('smtp_user', ''), (string) setting('smtp_pass', ''),
            $fromEmail, $to, implode("\r\n", $headers) . "\r\n\r\n" . $body);
    } catch (Throwable $e) {
        error_log('[QES mail] SMTP error: ' . $e->getMessage());
        return false;
    }
}

function mail_header_clean(string $v): string
{
    return trim(str_replace(["\r", "\n", "\0"], ' ', $v));
}

function mime_encode(string $v): string
{
    return preg_match('/[^\x20-\x7E]/', $v) ? '=?UTF-8?B?' . base64_encode($v) . '?=' : $v;
}

function smtp_send(string $host, int $port, string $secure, string $user, string $pass, string $from, string $to, string $data): bool
{
    $remote = ($secure === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port;
    $ctx = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true]]);
    $fp = @stream_socket_client($remote, $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $ctx);
    if (!$fp) {
        throw new RuntimeException("Connect failed: $errstr ($errno)");
    }
    stream_set_timeout($fp, 15);

    $read = function () use ($fp): string {
        $resp = '';
        while (($line = fgets($fp, 515)) !== false) {
            $resp .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        return $resp;
    };
    $cmd = function (string $c, array $expect) use ($fp, $read): string {
        if ($c !== '') {
            fwrite($fp, $c . "\r\n");
        }
        $r = $read();
        if (!in_array((int) substr($r, 0, 3), $expect, true)) {
            throw new RuntimeException('SMTP unexpected reply to "' . (str_starts_with($c, 'AUTH') || strlen($c) > 60 ? '[hidden]' : $c) . '": ' . trim($r));
        }
        return $r;
    };

    $ehloHost = preg_replace('/[^a-z0-9.\-]/i', '', $_SERVER['SERVER_NAME'] ?? 'localhost') ?: 'localhost';
    $cmd('', [220]);
    $cmd("EHLO $ehloHost", [250]);
    if ($secure === 'tls') {
        $cmd('STARTTLS', [220]);
        if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT)) {
            throw new RuntimeException('STARTTLS failed');
        }
        $cmd("EHLO $ehloHost", [250]);
    }
    if ($user !== '') {
        $cmd('AUTH LOGIN', [334]);
        $cmd(base64_encode($user), [334]);
        $cmd(base64_encode($pass), [235]);
    }
    $cmd("MAIL FROM:<$from>", [250]);
    $cmd("RCPT TO:<$to>", [250, 251]);
    $cmd('DATA', [354]);
    // dot-stuffing
    $data = preg_replace('/^\./m', '..', str_replace(["\r\n", "\r"], "\n", $data));
    $data = str_replace("\n", "\r\n", $data);
    $cmd($data . "\r\n.", [250]);
    $cmd('QUIT', [221]);
    fclose($fp);
    return true;
}

/** Wrap content in a simple branded email layout. */
function email_layout(string $title, string $innerHtml): string
{
    $company = e(setting('company_name', 'Quality Enviro & Engineering Services'));
    return '<!doctype html><html><body style="margin:0;background:#f3f6f8;font-family:Arial,Helvetica,sans-serif;color:#1f2d3d">'
        . '<table width="100%" cellpadding="0" cellspacing="0" style="padding:24px 0"><tr><td align="center">'
        . '<table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border-radius:8px;overflow:hidden">'
        . '<tr><td style="background:#0f2a44;color:#ffffff;padding:20px 28px;font-size:18px;font-weight:bold">' . $company . '</td></tr>'
        . '<tr><td style="padding:28px"><h2 style="margin:0 0 16px;color:#1b6b34;font-size:20px">' . e($title) . '</h2>' . $innerHtml . '</td></tr>'
        . '<tr><td style="background:#eef3f0;padding:14px 28px;font-size:12px;color:#5b6b78">&copy; ' . date('Y') . ' ' . $company . '</td></tr>'
        . '</table></td></tr></table></body></html>';
}
