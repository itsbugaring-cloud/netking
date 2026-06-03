<?php

declare(strict_types=1);

/**
 * Usage:
 * php set-webhook.php "https://your-domain/telegram-sandbox/webhook.php?k=SECRET"
 */

$cfg = require __DIR__ . '/config.local.php';

if ($argc < 2) {
    fwrite(STDERR, "Usage: php set-webhook.php \"https://.../webhook.php?k=SECRET\"\n");
    exit(1);
}

$url = $argv[1];
$token = (string) ($cfg['bot_token'] ?? '');

if ($token === '' || str_contains($token, 'REPLACE_WITH_')) {
    fwrite(STDERR, "Invalid bot_token in config.local.php\n");
    exit(1);
}

$apiUrl = "https://api.telegram.org/bot{$token}/setWebhook";

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POSTFIELDS => [
        'url' => $url,
        'drop_pending_updates' => 'true',
    ],
    CURLOPT_TIMEOUT => 20,
]);

$raw = curl_exec($ch);
$err = curl_error($ch);
$code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($err) {
    fwrite(STDERR, "cURL error: {$err}\n");
    exit(1);
}

echo "HTTP {$code}\n";
echo $raw . PHP_EOL;

