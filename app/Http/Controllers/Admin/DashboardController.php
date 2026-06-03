<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\User;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\CommissionLog;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->role === 'partner') {
            return $this->partnerDashboard($user);
        }

        if ($user->role === 'finance') {
            return redirect()->route('admin.invoices.index');
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
            'total_revenue'          => Invoice::where('status', 'paid')->sum('amount'),
            'monthly_revenue'        => Invoice::where('status', 'paid')
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year)
                ->sum('amount'),
            'mrr'                    => Customer::where('customers.status', 'active')
                ->join('packages', 'customers.package_id', '=', 'packages.id')
                ->sum('packages.price'),
            'unpaid_invoices_count'  => Invoice::where('status', 'unpaid')->count(),
            'unpaid_invoices_amount' => Invoice::where('status', 'unpaid')->sum('amount'),
            'overdue_invoices_count' => Invoice::where('status', 'unpaid')
                ->where('due_date', '<', now())->count(),
            'unpaid_commissions'     => CommissionLog::where('status', 'unpaid')->sum('amount'),
            'paid_commissions'       => CommissionLog::where('status', 'paid')->sum('amount'),
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
            $revenueData[] = (int) Invoice::where('status', 'paid')
                ->whereMonth('paid_at', $date->month)
                ->whereYear('paid_at', $date->year)
                ->sum('amount');
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
        $sparkline = ['customers' => [], 'revenue' => [], 'invoices' => [], 'active' => []];
        for ($d = 6; $d >= 0; $d--) {
            $day = now()->subDays($d);
            $sparkline['customers'][] = (int) Customer::whereDate('created_at', '<=', $day)->count();
            $sparkline['active'][] = (int) Customer::where('status', 'active')->whereDate('created_at', '<=', $day)->count();
            $sparkline['invoices'][] = (int) Invoice::where('status', 'unpaid')->whereDate('created_at', '<=', $day)->count();
            $sparkline['revenue'][] = (int) Invoice::where('status', 'paid')->whereDate('paid_at', $day)->sum('amount');
        }

        return view('admin.dashboard', compact('stats', 'recentCustomers', 'topPartners', 'areaStats', 'chartData', 'sparkline'));
    }

    /**
     * Billing calendar JSON endpoint for FullCalendar
     */
    public function billingCalendar()
    {
        $invoices = Invoice::with('customer:id,name')
            ->whereNotNull('due_date')
            ->get();

        $events = $invoices->map(function ($inv) {
            if ($inv->status === 'paid') {
                $color = '#10b981'; // green
            } elseif ($inv->status === 'unpaid' && $inv->due_date->isPast()) {
                $color = '#ef4444'; // red (overdue)
            } else {
                $color = '#f59e0b'; // yellow (pending)
            }
            return [
                'title' => ($inv->customer->name ?? 'Unknown') . ' - ' . $inv->invoice_number,
                'start' => $inv->due_date->format('Y-m-d'),
                'color' => $color,
                'url'   => route('admin.invoices.show', $inv->id),
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
            'total_revenue'          => Invoice::whereIn('customer_id', $customerIds)
                ->where('status', 'paid')->sum('amount'),
            'monthly_revenue'        => Invoice::whereIn('customer_id', $customerIds)
                ->where('status', 'paid')
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year)
                ->sum('amount'),
            'mrr'                    => Customer::where('customers.partner_id', $partner->id)
                ->where('customers.status', 'active')
                ->join('packages', 'customers.package_id', '=', 'packages.id')
                ->sum('packages.price'),
            'unpaid_invoices_count'  => Invoice::whereIn('customer_id', $customerIds)
                ->where('status', 'unpaid')->count(),
            'unpaid_invoices_amount' => Invoice::whereIn('customer_id', $customerIds)
                ->where('status', 'unpaid')->sum('amount'),
            'overdue_invoices_count' => Invoice::whereIn('customer_id', $customerIds)
                ->where('status', 'unpaid')
                ->where('due_date', '<', now())->count(),
            'unpaid_commissions'     => CommissionLog::where('user_id', $partner->id)->where('status', 'unpaid')->sum('amount'),
            'paid_commissions'       => CommissionLog::where('user_id', $partner->id)->where('status', 'paid')->sum('amount'),
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
            $revenueData[] = (int) Invoice::whereIn('customer_id', $customerIds)
                ->where('status', 'paid')
                ->whereMonth('paid_at', $date->month)
                ->whereYear('paid_at', $date->year)
                ->sum('amount');
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
        $sparkline = ['customers' => [], 'revenue' => [], 'invoices' => [], 'active' => []];
        for ($d = 6; $d >= 0; $d--) {
            $day = now()->subDays($d);
            $sparkline['customers'][] = (int) Customer::where('partner_id', $partner->id)->whereDate('created_at', '<=', $day)->count();
            $sparkline['active'][] = (int) Customer::where('partner_id', $partner->id)->where('status', 'active')->whereDate('created_at', '<=', $day)->count();
            $sparkline['invoices'][] = (int) Invoice::whereIn('customer_id', $customerIds)->where('status', 'unpaid')->whereDate('created_at', '<=', $day)->count();
            $sparkline['revenue'][] = (int) Invoice::whereIn('customer_id', $customerIds)->where('status', 'paid')->whereDate('paid_at', $day)->sum('amount');
        }

        return view('admin.dashboard', compact('stats', 'recentCustomers', 'topPartners', 'areaStats', 'chartData', 'sparkline'));
    }
}
