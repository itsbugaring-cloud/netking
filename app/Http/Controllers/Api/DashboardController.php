<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Get partner dashboard data
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Get active customers count
        $activeCustomers = Customer::where('partner_id', $user->id)
            ->where('status', 'active')
            ->count();

        // Get total customers count
        $totalCustomers = Customer::where('partner_id', $user->id)->count();

        // Get pending customers count
        $provisioningCustomers = Customer::where('partner_id', $user->id)
            ->where('status', 'provisioning')
            ->count();

        // [REMOVED] Commission feature removed
        $unpaidCommission = 0;
        $paidCommission = 0;

        // Recent customers
        $recentCustomers = Customer::where('partner_id', $user->id)
            ->with('area:id,name')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'dashboard' => [
                'wallet_balance' => $user->wallet_balance,
                'active_customers' => $activeCustomers,
                'total_customers' => $totalCustomers,
                'provisioning_customers' => $provisioningCustomers,
                'unpaid_commission' => $unpaidCommission,
                'paid_commission' => $paidCommission,
                'recent_customers' => $recentCustomers,
            ],
        ]);
    }
}
