<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Display customer dashboard
     */
    public function index()
    {
        $customer = auth('customer')->user();
        $customer->load('package');

        // Get latest approved payment
        $latestPayment = $customer->payments()
            ->where('status', 'approved')
            ->latest('approved_at')
            ->first();

        // Count pending payments
        $pendingCount = $customer->payments()
            ->where('status', 'pending')
            ->count();

        // Check MikroTik connection status (optional - may fail if MikroTik not configured)
        $isOnline = false;
        $uptime = '0s';

        try {
            if (class_exists('\App\Services\MikroTikService')) {
                $mikrotik = app(\App\Services\MikroTikService::class);

                if ($mikrotik->isConnected()) {
                    $sessionData = $mikrotik->getActiveSessions($customer->pppoe_user);

                    if ($sessionData['success'] && !empty($sessionData['data'])) {
                        $isOnline = true;
                        $uptime = $sessionData['data'][0]['uptime'] ?? '0s';
                    }
                }
            }
        } catch (\Exception $e) {
            // Silently fail if MikroTik not available
            \Log::warning('MikroTik check failed in customer dashboard', [
                'error' => $e->getMessage()
            ]);
        }

        $stats = [
            'package' => $customer->package->name ?? 'No Package',
            'package_speed' => $customer->package ? $customer->package->speed_label : '-',
            'status' => $customer->status,
            'ip_address' => $customer->remote_ip,
            'is_online' => $isOnline,
            'uptime' => $uptime,
            'latest_payment' => $latestPayment,
            'pending_count' => $pendingCount,
        ];

        return view('customer.dashboard', compact('customer', 'stats'));
    }
}
