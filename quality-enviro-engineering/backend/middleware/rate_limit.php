<?php
declare(strict_types=1);

/**
 * Fixed-window rate limiter backed by MySQL.
 * Returns true if the action is allowed (and records the hit).
 */
function rate_limit_hit(string $action, int $max, int $windowSeconds, ?string $identity = null): bool
{
    $key = hash('sha256', $action . '|' . ($identity ?? client_ip()));
    $now = gmdate('Y-m-d H:i:s');
    $row = db_one('SELECT hits, window_start FROM rate_limits WHERE rl_key = ?', [$key]);

    if (!$row || strtotime($row['window_start'] . ' UTC') + $windowSeconds < time()) {
        db_exec(
            'INSERT INTO rate_limits (rl_key, hits, window_start) VALUES (?, 1, ?)
             ON DUPLICATE KEY UPDATE hits = 1, window_start = VALUES(window_start)',
            [$key, $now]
        );
        // opportunistic cleanup (1% of requests)
        if (random_int(1, 100) === 1) {
            db_exec('DELETE FROM rate_limits WHERE window_start < (UTC_TIMESTAMP() - INTERVAL 1 DAY)');
        }
        return true;
    }
    if ((int) $row['hits'] >= $max) {
        return false;
    }
    db_exec('UPDATE rate_limits SET hits = hits + 1 WHERE rl_key = ?', [$key]);
    return true;
}

/** Is the action currently blocked (without recording a hit)? */
function rate_limited(string $action, int $max, int $windowSeconds, ?string $identity = null): bool
{
    $key = hash('sha256', $action . '|' . ($identity ?? client_ip()));
    $row = db_one('SELECT hits, window_start FROM rate_limits WHERE rl_key = ?', [$key]);
    return $row && strtotime($row['window_start'] . ' UTC') + $windowSeconds >= time() && (int) $row['hits'] >= $max;
}

function rate_limit_clear(string $action, ?string $identity = null): void
{
    db_exec('DELETE FROM rate_limits WHERE rl_key = ?', [hash('sha256', $action . '|' . ($identity ?? client_ip()))]);
}
