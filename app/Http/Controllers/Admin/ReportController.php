<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Area;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Revenue dashboard page
     */
    public function revenue(Request $request)
    {
        $year = $request->input('year', now()->year);

        // Monthly revenue for the year
        $monthlyRevenue = Invoice::where('status', 'paid')
            ->whereYear('paid_at', $year)
            ->select(
                DB::raw('MONTH(paid_at) as month'),
                DB::raw('SUM(amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        // Fill all 12 months
        $monthlyData = [];
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyData[] = [
                'month'  => $i,
                'label'  => $monthNames[$i - 1],
                'total'  => $monthlyRevenue->get($i)?->total ?? 0,
                'count'  => $monthlyRevenue->get($i)?->count ?? 0,
            ];
        }

        // Revenue per area
        $revenueByArea = Invoice::where('invoices.status', 'paid')
            ->whereYear('paid_at', $year)
            ->join('customers', 'invoices.customer_id', '=', 'customers.id')
            ->join('areas', 'customers.area_id', '=', 'areas.id')
            ->select(
                'areas.name as area_name',
                DB::raw('SUM(invoices.amount) as total'),
                DB::raw('COUNT(invoices.id) as count')
            )
            ->groupBy('areas.name')
            ->orderByDesc('total')
            ->get();

        // Revenue per partner
        $revenueByPartner = Invoice::where('invoices.status', 'paid')
            ->whereYear('paid_at', $year)
            ->join('customers', 'invoices.customer_id', '=', 'customers.id')
            ->join('users', 'customers.partner_id', '=', 'users.id')
            ->select(
                'users.name as partner_name',
                DB::raw('SUM(invoices.amount) as total'),
                DB::raw('COUNT(invoices.id) as count')
            )
            ->groupBy('users.name')
            ->orderByDesc('total')
            ->get();

        // Summary totals
        $totalPaid = Invoice::where('status', 'paid')->whereYear('paid_at', $year)->sum('amount');
        $totalUnpaid = Invoice::where('status', 'unpaid')->sum('amount');
        $totalOverdue = Invoice::overdue()->sum('amount');
        $totalInvoices = Invoice::whereYear('created_at', $year)->count();

        // Available years
        $years = Invoice::selectRaw('YEAR(created_at) as y')
            ->distinct()
            ->orderByDesc('y')
            ->pluck('y');

        // Revenue per partner per month (for matrix table)
        $partnerMonthlyRaw = Invoice::where('invoices.status', 'paid')
            ->whereYear('paid_at', $year)
            ->join('customers', 'invoices.customer_id', '=', 'customers.id')
            ->join('users', 'customers.partner_id', '=', 'users.id')
            ->select(
                'users.id as partner_id',
                'users.name as partner_name',
                DB::raw('MONTH(invoices.paid_at) as month'),
                DB::raw('SUM(invoices.amount) as total'),
                DB::raw('COUNT(invoices.id) as count')
            )
            ->groupBy('users.id', 'users.name', 'month')
            ->orderBy('users.name')
            ->get();

        // Build pivot: partner → [month => total]
        $partnerMonthly = [];
        foreach ($partnerMonthlyRaw as $row) {
            $partnerMonthly[$row->partner_name][$row->month] = $row->total;
        }

        return view('admin.reports.revenue', compact(
            'year',
            'years',
            'monthlyData',
            'revenueByArea',
            'revenueByPartner',
            'partnerMonthly',
            'totalPaid',
            'totalUnpaid',
            'totalOverdue',
            'totalInvoices'
        ));
    }

    /**
     * Customer billing status report page
     */
    public function billing(Request $request)
    {
        $query = Customer::with(['area', 'partner', 'package'])
            ->withCount('invoices')
            ->withSum(['invoices as paid_total' => fn($q) => $q->where('status','paid')], 'amount')
            ->withSum(['invoices as unpaid_total' => fn($q) => $q->where('status','unpaid')], 'amount');

        if ($request->filled('area_id'))       $query->where('area_id', $request->area_id);
        if ($request->filled('partner_id'))    $query->where('partner_id', $request->partner_id);
        if ($request->filled('status'))        $query->where('status', $request->status);
        if ($request->filled('invoice_status')) {
            $query->whereHas('invoices', fn($q) => $q->where('status', $request->invoice_status));
        }

        $customers = $query->orderBy('name')->paginate(30)->withQueryString();

        $areas    = Area::orderBy('name')->get(['id','name']);
        $partners = User::where('role','partner')->orderBy('name')->get(['id','name']);

        $stats = [
            'total'       => Customer::count(),
            'active'      => Customer::where('status','active')->count(),
            'suspended'   => Customer::where('status','suspended')->count(),
            'unpaid_customers' => Customer::whereHas('invoices', fn($q) => $q->where('status','unpaid'))->count(),
        ];

        return view('admin.reports.billing', compact('customers','areas','partners','stats'));
    }

    /**
     * Export customer billing report as CSV
     */
    public function exportBilling(Request $request): StreamedResponse
    {
        $query = Customer::with(['area','partner','package'])
            ->withSum(['invoices as paid_total' => fn($q) => $q->where('status','paid')], 'amount')
            ->withSum(['invoices as unpaid_total' => fn($q) => $q->where('status','unpaid')], 'amount');

        if ($request->filled('area_id'))    $query->where('area_id', $request->area_id);
        if ($request->filled('partner_id')) $query->where('partner_id', $request->partner_id);
        if ($request->filled('status'))     $query->where('status', $request->status);

        $data = $query->orderBy('name')->get();

        $statusLabel = ['active'=>'Aktif','suspended'=>'Diisolir','provisioning'=>'Dalam Proses','failed'=>'Gagal','pending'=>'Pending'];
        $filename = 'laporan_pelanggan_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($data, $statusLabel) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['Nama','No. HP','Area','Mitra/Teknisi','Paket','Status Pelanggan','Total Bayar','Tunggakan','Tgl Daftar']);
            foreach ($data as $c) {
                fputcsv($out, [
                    $c->name,
                    $c->phone ?? '-',
                    $c->area->name ?? '-',
                    $c->partner->name ?? '-',
                    $c->package->name ?? '-',
                    $statusLabel[$c->status] ?? ucfirst($c->status),
                    $c->paid_total ?? 0,
                    $c->unpaid_total ?? 0,
                    $c->created_at->format('d M Y'),
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Export invoices as CSV
     */
    public function exportInvoices(Request $request): StreamedResponse
    {
        $query = Invoice::with(['customer.partner', 'customer.area'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('month') && $request->filled('year')) {
            $query->whereYear('due_date', $request->year)
                ->whereMonth('due_date', $request->month);
        } elseif ($request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        }
        if ($request->filled('area_id')) {
            $query->whereHas('customer', fn($q) => $q->where('area_id', $request->area_id));
        }
        if ($request->filled('partner_id')) {
            $query->whereHas('customer', fn($q) => $q->where('partner_id', $request->partner_id));
        }

        $invoices = $query->get();

        $filename = 'invoices_' . ($request->year ?? date('Y')) . '_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($invoices) {
            $out = fopen('php://output', 'w');

            // BOM for Excel UTF-8
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header
            fputcsv($out, [
                'Invoice #',
                'Customer',
                'Area',
                'Partner',
                'Amount',
                'Status',
                'Due Date',
                'Paid Date',
                'Payment Method',
                'Created At'
            ]);

            $totalPaid = 0;
            $totalUnpaid = 0;

            foreach ($invoices as $inv) {
                fputcsv($out, [
                    $inv->invoice_number,
                    $inv->customer?->name ?? '-',
                    $inv->customer?->area?->name ?? '-',
                    $inv->customer?->partner?->name ?? '-',
                    $inv->amount,
                    $inv->status,
                    $inv->due_date?->format('Y-m-d') ?? '-',
                    $inv->paid_at?->format('Y-m-d H:i') ?? '-',
                    $inv->payment_method ?? '-',
                    $inv->created_at?->format('Y-m-d H:i'),
                ]);

                if ($inv->status === 'paid') $totalPaid += $inv->amount;
                else $totalUnpaid += $inv->amount;
            }

            // Summary row
            fputcsv($out, []);
            fputcsv($out, ['SUMMARY', '', '', '', '', '', '', '', '', '']);
            fputcsv($out, ['Total Invoices', count($invoices)]);
            fputcsv($out, ['Total Paid', $totalPaid]);
            fputcsv($out, ['Total Unpaid', $totalUnpaid]);

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Export revenue summary as CSV
     */
    public function exportRevenue(Request $request): StreamedResponse
    {
        $year = $request->input('year', now()->year);

        $data = Invoice::where('invoices.status', 'paid')
            ->whereYear('invoices.paid_at', $year)
            ->join('customers', 'invoices.customer_id', '=', 'customers.id')
            ->leftJoin('areas', 'customers.area_id', '=', 'areas.id')
            ->leftJoin('users', 'customers.partner_id', '=', 'users.id')
            ->select(
                DB::raw('MONTH(invoices.paid_at) as month'),
                'areas.name as area_name',
                'users.name as partner_name',
                DB::raw('SUM(invoices.amount) as total'),
                DB::raw('COUNT(invoices.id) as count')
            )
            ->groupBy('month', 'areas.name', 'users.name')
            ->orderBy('month')
            ->get();

        $filename = "revenue_{$year}_" . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($data, $year) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($out, ["Revenue Report — {$year}"]);
            fputcsv($out, []);
            fputcsv($out, ['Month', 'Area', 'Partner', 'Invoice Count', 'Revenue']);

            $grandTotal = 0;
            $months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

            foreach ($data as $row) {
                fputcsv($out, [
                    $months[$row->month] ?? $row->month,
                    $row->area_name ?? '-',
                    $row->partner_name ?? '-',
                    $row->count,
                    $row->total,
                ]);
                $grandTotal += $row->total;
            }

            fputcsv($out, []);
            fputcsv($out, ['GRAND TOTAL', '', '', '', $grandTotal]);

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
