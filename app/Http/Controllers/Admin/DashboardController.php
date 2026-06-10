<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\User;
use App\Models\Customer;
use App\Models\Payment;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->role === 'partner') {
            return $this->partnerDashboard($user);
        }

        if ($user->role === 'finance') {
            return redirect()->route('admin.payments.review');
        }

        return $this->adminDashboard();
    }

    private function adminDashboard()
    {
        $stats = [
            'total_areas'            => Area::count(),
            'total_partners'         => User::where('role', 'partner')->count(),
            'active_resellers'       => User::where('role', 'partner')->count(),
            'total_customers'        => Customer::count(),
            'active_customers'       => Customer::where('status', 'active')->count(),
            'suspended_customers'    => Customer::where('status', 'suspended')->count(),
            'pending_customers'      => Customer::where('status', 'provisioning')->count(),
            'provisioning_customers' => Customer::where('status', 'provisioning')->count(),
            'failed_customers'       => Customer::where('status', 'failed')->count(),
            'total_revenue'          => Payment::where('status', 'approved')->sum('jumlah'),
            'monthly_revenue'        => Payment::where('status', 'approved')
                ->whereMonth('approved_at', now()->month)
                ->whereYear('approved_at', now()->year)
                ->sum('jumlah'),
            'mrr'                    => Customer::where('customers.status', 'active')
                ->join('packages', 'customers.package_id', '=', 'packages.id')
                ->sum('packages.price'),
            'pending_payments'       => Payment::where('status', 'pending')->count(),
            'approved_this_month'    => Payment::where('status', 'approved')
                ->whereMonth('approved_at', now()->month)
                ->whereYear('approved_at', now()->year)
                ->count(),
            'unpaid_commissions'     => 0, // [REMOVED] Commission feature removed
            'paid_commissions'       => 0, // [REMOVED] Commission feature removed
            'open_tickets'           => \App\Models\Ticket::whereIn('status', ['open', 'pending'])->count(),
        ];

        $recentCustomers = Customer::with(['partner', 'area', 'package'])
            ->orderBy('created_at', 'desc')->limit(10)->get();

        $topPartners = User::where('role', 'partner')
            ->withCount(['customers' => fn($q) => $q->where('status', 'active')])
            ->orderBy('customers_count', 'desc')->limit(5)->get();

        $areaStats = Area::withCount('customers')->orderBy('customers_count', 'desc')->get();

        // Chart data — last 6 months
        $labels = [];
        $revenueData = [];
        $growthData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $labels[] = $date->format('M');
            $revenueData[] = (int) Payment::where('status', 'approved')
                ->whereMonth('approved_at', $date->month)
                ->whereYear('approved_at', $date->year)
                ->sum('jumlah');
            $growthData[] = (int) Customer::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();
        }
        // Make growth cumulative
        $totalBefore = Customer::where('created_at', '<', now()->subMonths(5)->startOfMonth())->count();
        $cumulative = [];
        $running = $totalBefore;
        foreach ($growthData as $g) {
            $running += $g;
            $cumulative[] = $running;
        }
        $chartData = ['labels' => $labels, 'revenue' => $revenueData, 'growth' => $cumulative];

        // Sparkline 7-day trend data for stat cards
        $sparkline = ['customers' => [], 'revenue' => [], 'payments' => [], 'active' => []];
        for ($d = 6; $d >= 0; $d--) {
            $day = now()->subDays($d);
            $sparkline['customers'][] = (int) Customer::whereDate('created_at', '<=', $day)->count();
            $sparkline['active'][] = (int) Customer::where('status', 'active')->whereDate('created_at', '<=', $day)->count();
            $sparkline['payments'][] = (int) Payment::where('status', 'pending')->whereDate('created_at', '<=', $day)->count();
            $sparkline['revenue'][] = (int) Payment::where('status', 'approved')->whereDate('approved_at', $day)->sum('jumlah');
        }

        return view('admin.dashboard', compact('stats', 'recentCustomers', 'topPartners', 'areaStats', 'chartData', 'sparkline'));
    }

    /**
     * Billing calendar JSON endpoint — now returns payment data
     */
    public function billingCalendar()
    {
        $payments = Payment::with('customer:id,name')
            ->where('status', 'approved')
            ->whereNotNull('approved_at')
            ->orderBy('approved_at', 'desc')
            ->limit(200)
            ->get();

        $events = $payments->map(function ($pmt) {
            return [
                'title' => ($pmt->customer->name ?? 'Unknown') . ' - Rp ' . number_format($pmt->jumlah, 0, ',', '.'),
                'start' => $pmt->approved_at->format('Y-m-d'),
                'color' => '#10b981',
            ];
        });

        return response()->json($events);
    }


    private function partnerDashboard(User $partner)
    {
        $partnerCustomers = Customer::where('partner_id', $partner->id);
        $customerIds = (clone $partnerCustomers)->pluck('id');
        $areaIds = (clone $partnerCustomers)->whereNotNull('area_id')->pluck('area_id')->push($partner->area_id)->filter()->unique()->values();

        $stats = [
            'total_areas'            => max(1, $areaIds->count()),
            'total_partners'         => 0,
            'active_resellers'       => 0,
            'total_customers'        => (clone $partnerCustomers)->count(),
            'active_customers'       => (clone $partnerCustomers)->where('status', 'active')->count(),
            'suspended_customers'    => (clone $partnerCustomers)->where('status', 'suspended')->count(),
            'pending_customers'      => (clone $partnerCustomers)->where('status', 'provisioning')->count(),
            'provisioning_customers' => (clone $partnerCustomers)->where('status', 'provisioning')->count(),
            'failed_customers'       => (clone $partnerCustomers)->where('status', 'failed')->count(),
            'total_revenue'          => Payment::whereIn('customer_id', $customerIds)
                ->where('status', 'approved')->sum('jumlah'),
            'monthly_revenue'        => Payment::whereIn('customer_id', $customerIds)
                ->where('status', 'approved')
                ->whereMonth('approved_at', now()->month)
                ->whereYear('approved_at', now()->year)
                ->sum('jumlah'),
            'mrr'                    => Customer::where('customers.partner_id', $partner->id)
                ->where('customers.status', 'active')
                ->join('packages', 'customers.package_id', '=', 'packages.id')
                ->sum('packages.price'),
            'pending_payments'       => Payment::whereIn('customer_id', $customerIds)
                ->where('status', 'pending')->count(),
            'approved_this_month'    => Payment::whereIn('customer_id', $customerIds)
                ->where('status', 'approved')
                ->whereMonth('approved_at', now()->month)
                ->whereYear('approved_at', now()->year)
                ->count(),
            'unpaid_commissions'     => 0, // [REMOVED] Commission feature removed
            'paid_commissions'       => 0, // [REMOVED] Commission feature removed
            'open_tickets'           => \App\Models\Ticket::whereIn('status', ['open', 'pending'])->count(),
        ];

        $recentCustomers = Customer::with(['partner', 'area', 'package'])
            ->where('partner_id', $partner->id)
            ->orderBy('created_at', 'desc')->limit(10)->get();

        $topPartners = collect();
        $areaStats = Area::whereIn('id', $areaIds)->withCount(['customers' => fn($q) => $q->where('partner_id', $partner->id)])->get();

        // Chart data — last 6 months (scoped to partner's area)
        $labels = [];
        $revenueData = [];
        $growthData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $labels[] = $date->format('M');
            $revenueData[] = (int) Payment::whereIn('customer_id', $customerIds)
                ->where('status', 'approved')
                ->whereMonth('approved_at', $date->month)
                ->whereYear('approved_at', $date->year)
                ->sum('jumlah');
            $growthData[] = (int) Customer::where('partner_id', $partner->id)
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();
        }
        $totalBefore = Customer::where('partner_id', $partner->id)
            ->where('created_at', '<', now()->subMonths(5)->startOfMonth())->count();
        $cumulative = [];
        $running = $totalBefore;
        foreach ($growthData as $g) {
            $running += $g;
            $cumulative[] = $running;
        }
        $chartData = ['labels' => $labels, 'revenue' => $revenueData, 'growth' => $cumulative];

        // Sparkline 7-day trend data
        $sparkline = ['customers' => [], 'revenue' => [], 'payments' => [], 'active' => []];
        for ($d = 6; $d >= 0; $d--) {
            $day = now()->subDays($d);
            $sparkline['customers'][] = (int) Customer::where('partner_id', $partner->id)->whereDate('created_at', '<=', $day)->count();
            $sparkline['active'][] = (int) Customer::where('partner_id', $partner->id)->where('status', 'active')->whereDate('created_at', '<=', $day)->count();
            $sparkline['payments'][] = (int) Payment::whereIn('customer_id', $customerIds)->where('status', 'pending')->whereDate('created_at', '<=', $day)->count();
            $sparkline['revenue'][] = (int) Payment::whereIn('customer_id', $customerIds)->where('status', 'approved')->whereDate('approved_at', $day)->sum('jumlah');
        }

        return view('admin.dashboard', compact('stats', 'recentCustomers', 'topPartners', 'areaStats', 'chartData', 'sparkline'));
    }
}
