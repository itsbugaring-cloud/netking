<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$duplicate = \App\Models\Customer::where('pppoe_user', 'NGL-013')->first();
if ($duplicate) {
    $duplicate->pppoe_user = 'NGL-013-DUPLICATE-' . rand(100, 999);
    $duplicate->save();
    echo "Duplicate NGL-013 has been safely renamed to " . $duplicate->pppoe_user . "\n";
} else {
    echo "No duplicate found.\n";
}
