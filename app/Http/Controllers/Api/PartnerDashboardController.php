<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CommissionLog;
use App\Models\Olt;

class PartnerDashboardController extends Controller
{
    public function index(Request $request)
    {
        $partner = $request->user();
        $areaId = $partner->area_id;

        // Customer stats
        $totalCustomers = Customer::where('area_id', $areaId)->count();
        $activeCustomers = Customer::where('area_id', $areaId)->where('status', 'active')->count();
        $offlineCustomers = $totalCustomers - $activeCustomers;
        $unpaidInvoices = Customer::where('area_id', $areaId)
            ->whereHas('invoices', fn($q) => $q->where('status', 'unpaid'))
            ->count();

        // Commission this month
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $monthlyCommission = CommissionLog::where('user_id', $partner->id)
            ->where('month', $currentMonth)
            ->where('year', $currentYear)
            ->sum('amount');

        // OLT info for partner's area
        $olt = Olt::where('area_id', $areaId)->first();

        return response()->json([
            'data' => [
                'partner' => [
                    'name' => $partner->name,
                    'area' => $partner->area?->name,
                ],
                'stats' => [
                    'total_customers' => $totalCustomers,
                    'active_customers' => $activeCustomers,
                    'offline_customers' => $offlineCustomers,
                    'unpaid_invoices' => $unpaidInvoices,
                ],
                'commission_this_month' => $monthlyCommission,
                'olt' => $olt ? [
                    'id'           => $olt->id,
                    'name'         => $olt->name,
                    'ip_address'   => $olt->ip_address,
                    'brand'        => $olt->brand,
                    'status'       => $olt->status,
                    'sync_status'  => $olt->sync_status ?? null,
                    'sync_message' => $olt->sync_message ?? null,
                    'synced_at'    => $olt->synced_at ? \Carbon\Carbon::parse($olt->synced_at)->diffForHumans() : null,
                ] : null,
            ],
        ]);
    }
}
