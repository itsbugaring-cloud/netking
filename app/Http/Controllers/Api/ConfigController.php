<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;

class ConfigController extends Controller
{
    /**
     * Return public app configuration values.
     * No authentication required — safe values only.
     */
    public function index()
    {
        return response()->json([
            'data' => [
                'support_wa_number' => Setting::get('support_wa_number', '6281234567890'),
                'company_name'      => Setting::get('company_name', 'Netking'),
                'company_email'     => Setting::get('company_email', 'admin@netking.id'),
            ],
        ]);
    }
}
