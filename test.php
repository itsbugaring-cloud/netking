<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Area;
use App\Services\MikroTikService;

$area = Area::where('name', 'like', '%Bayongbong%')->first();
if (!$area) {
    echo "Area not found\n";
    exit;
}

echo "Testing Area: " . $area->name . "\n";
echo "IP: " . $area->router_ip . "\n";

$mikrotik = MikroTikService::forArea($area);
$secretsResult = $mikrotik->getAllSecrets();

if (!$secretsResult['success']) {
    echo "Failed: " . $secretsResult['error'] . "\n";
    exit;
}

$secrets = $secretsResult['data'];
echo "Total Secrets: " . count($secrets) . "\n\n";

foreach ($secrets as $secret) {
    $name = $secret['name'] ?? '';
    if ($name === 'BYB-012' || $name === 'BYB-013' || $name === 'BYB-014' || $name === 'BYB-015') {
        echo "ID: " . ($secret['.id'] ?? 'N/A') . " | Name: " . $name . " | Comment: " . ($secret['comment'] ?? 'NONE') . "\n";
    }
}
