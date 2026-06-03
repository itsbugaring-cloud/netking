<?php

return [
    'host' => env('MIKROTIK_HOST', '192.168.1.150'),
    'user' => env('MIKROTIK_USER', 'api-hotspot'),
    'pass' => env('MIKROTIK_PASS', 'ApiPass123!'),
    'port' => (int) env('MIKROTIK_PORT', 8728),
];
