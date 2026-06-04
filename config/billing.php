<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Billing Configuration
    |--------------------------------------------------------------------------
    */

    // Default package price if not set (in IDR)
    'default_package_price' => env('DEFAULT_PACKAGE_PRICE', 100000),

    // Default pricing fallback based on package download speed (Mbps).
    // These are used only when customer/package price is empty or zero.
    'default_speed_prices' => [
        10 => env('BILLING_PRICE_10MBPS', 150000),
        8 => env('BILLING_PRICE_8MBPS', 125000),
        6 => env('BILLING_PRICE_6MBPS', 100000),
    ],

    // Fixed invoice due day each month (e.g. 20 = pembayaran terakhir tanggal 20)
    'invoice_due_day' => env('INVOICE_DUE_DAY', 20),

    // Base day count for prorata formula.
    // Example: Rp150.000 / 30 hari = Rp5.000 per hari.
    'proration_base_days' => env('BILLING_PRORATION_BASE_DAYS', 30),

    // Grace period in days after due date before auto-suspension
    // 0 means customer becomes eligible for suspension on the next daily run
    // after the due date has passed (e.g. due 20th, suspend run on 21st).
    'suspend_grace_days' => env('BILLING_SUSPEND_GRACE_DAYS', 0),

];
