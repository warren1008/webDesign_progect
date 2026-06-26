<?php

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, max-age=0');

$cacheDirectory = __DIR__ . '/storage/cache';
$cacheFile = $cacheDirectory . '/usd-twd.json';
$cacheLifetime = 21600;
$fallbackRate = 31.606;
$fallbackDate = '2026-06-15';

if (!is_dir($cacheDirectory)) {
    @mkdir($cacheDirectory, 0775, true);
}

$readCache = static function () use ($cacheFile): ?array {
    if (!is_file($cacheFile)) {
        return null;
    }

    $cached = json_decode((string)@file_get_contents($cacheFile), true);
    return is_array($cached) && isset($cached['rate']) ? $cached : null;
};

$cached = $readCache();
if ($cached && (time() - (int)($cached['fetched_timestamp'] ?? 0)) < $cacheLifetime) {
    $cached['cached'] = true;
    echo json_encode($cached, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$apiUrl = 'https://api.frankfurter.dev/v2/rate/USD/TWD';
$response = false;

if (function_exists('curl_init')) {
    $curl = curl_init($apiUrl);
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_USERAGENT => 'StafflessNoodleStore/1.0',
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $response = curl_exec($curl);
    $statusCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    if ($statusCode !== 200) {
        $response = false;
    }
} elseif (filter_var(ini_get('allow_url_fopen'), FILTER_VALIDATE_BOOLEAN)) {
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'header' => "User-Agent: StafflessNoodleStore/1.0\r\n",
        ],
    ]);
    $response = @file_get_contents($apiUrl, false, $context);
}

$remote = $response ? json_decode($response, true) : null;
if (is_array($remote) && isset($remote['rate']) && (float)$remote['rate'] > 0) {
    $payload = [
        'base' => 'USD',
        'quote' => 'TWD',
        'rate' => round((float)$remote['rate'], 4),
        'date' => (string)($remote['date'] ?? date('Y-m-d')),
        'fetched_at' => date(DATE_ATOM),
        'fetched_timestamp' => time(),
        'source' => 'Frankfurter',
        'cached' => false,
    ];
    @file_put_contents(
        $cacheFile,
        json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        LOCK_EX
    );
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($cached) {
    $cached['cached'] = true;
    $cached['stale'] = true;
    echo json_encode($cached, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

http_response_code(200);
echo json_encode([
    'base' => 'USD',
    'quote' => 'TWD',
    'rate' => $fallbackRate,
    'date' => $fallbackDate,
    'fetched_at' => null,
    'fetched_timestamp' => 0,
    'source' => 'Fallback',
    'cached' => true,
    'stale' => true,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

