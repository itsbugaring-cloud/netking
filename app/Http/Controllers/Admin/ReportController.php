<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
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

        // Monthly revenue for the year (from approved payments)
        $monthlyRevenue = Payment::where('status', 'approved')
            ->whereYear('approved_at', $year)
            ->select(
                DB::raw('MONTH(approved_at) as month'),
                DB::raw('SUM(jumlah) as total'),
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
        $revenueByArea = Payment::where('payments.status', 'approved')
            ->whereYear('approved_at', $year)
            ->join('customers', 'payments.customer_id', '=', 'customers.id')
            ->join('areas', 'customers.area_id', '=', 'areas.id')
            ->select(
                'areas.name as area_name',
                DB::raw('SUM(payments.jumlah) as total'),
                DB::raw('COUNT(payments.id) as count')
            )
            ->groupBy('areas.name')
            ->orderByDesc('total')
            ->get();

        // Revenue per partner
        $revenueByPartner = Payment::where('payments.status', 'approved')
            ->whereYear('approved_at', $year)
            ->join('customers', 'payments.customer_id', '=', 'customers.id')
            ->join('users', 'customers.partner_id', '=', 'users.id')
            ->select(
                'users.name as partner_name',
                DB::raw('SUM(payments.jumlah) as total'),
                DB::raw('COUNT(payments.id) as count')
            )
            ->groupBy('users.name')
            ->orderByDesc('total')
            ->get();

        // Summary totals
        $totalPaid = Payment::where('status', 'approved')->whereYear('approved_at', $year)->sum('jumlah');
        $totalPending = Payment::where('status', 'pending')->sum('jumlah');
        $totalPayments = Payment::whereYear('created_at', $year)->count();

        // Available years
        $years = Payment::selectRaw('YEAR(created_at) as y')
            ->distinct()
            ->orderByDesc('y')
            ->pluck('y');

        if ($years->isEmpty()) {
            $years = collect([now()->year]);
        }

        // Revenue per partner per month (for matrix table)
        $partnerMonthlyRaw = Payment::where('payments.status', 'approved')
            ->whereYear('approved_at', $year)
            ->join('customers', 'payments.customer_id', '=', 'customers.id')
            ->join('users', 'customers.partner_id', '=', 'users.id')
            ->select(
                'users.id as partner_id',
                'users.name as partner_name',
                DB::raw('MONTH(payments.approved_at) as month'),
                DB::raw('SUM(payments.jumlah) as total'),
                DB::raw('COUNT(payments.id) as count')
            )
            ->groupBy('users.id', 'users.name', 'month')
            ->orderBy('users.name')
            ->get();

        // Build pivot: partner → [month => total]
        $partnerMonthly = [];
        foreach ($partnerMonthlyRaw as $row) {
            $partnerMonthly[$row->partner_name][$row->month] = $row->total;
        }

        // Keep backward-compatible variable names for the view
        $totalUnpaid = $totalPending;
        $totalOverdue = 0;
        $totalInvoices = $totalPayments;

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
            ->withCount('payments')
            ->withSum(['payments as paid_total' => fn($q) => $q->where('status','approved')], 'jumlah')
            ->withSum(['payments as pending_total' => fn($q) => $q->where('status','pending')], 'jumlah');

        if ($request->filled('area_id'))       $query->where('area_id', $request->area_id);
        if ($request->filled('partner_id'))    $query->where('partner_id', $request->partner_id);
        if ($request->filled('status'))        $query->where('status', $request->status);
        if ($request->filled('payment_status')) {
            $query->whereHas('payments', fn($q) => $q->where('status', $request->payment_status));
        }

        $customers = $query->orderBy('name')->paginate(30)->withQueryString();

        $areas    = Area::orderBy('name')->get(['id','name']);
        $partners = User::where('role','partner')->orderBy('name')->get(['id','name']);

        $stats = [
            'total'       => Customer::count(),
            'active'      => Customer::where('status','active')->count(),
            'suspended'   => Customer::where('status','suspended')->count(),
            'pending_payments' => Payment::where('status','pending')->count(),
        ];

        return view('admin.reports.billing', compact('customers','areas','partners','stats'));
    }

    /**
     * Export customer billing report as CSV
     */
    public function exportBilling(Request $request): StreamedResponse
    {
        $query = Customer::with(['area','partner','package'])
            ->withSum(['payments as paid_total' => fn($q) => $q->where('status','approved')], 'jumlah')
            ->withSum(['payments as pending_total' => fn($q) => $q->where('status','pending')], 'jumlah');

        if ($request->filled('area_id'))    $query->where('area_id', $request->area_id);
        if ($request->filled('partner_id')) $query->where('partner_id', $request->partner_id);
        if ($request->filled('status'))     $query->where('status', $request->status);

        $data = $query->orderBy('name')->get();

        $statusLabel = ['active'=>'Aktif','suspended'=>'Diisolir','provisioning'=>'Dalam Proses','failed'=>'Gagal','pending'=>'Pending'];
        $filename = 'laporan_pelanggan_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($data, $statusLabel) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['Nama','No. HP','Area','PIC','Paket','Status Pelanggan','Total Bayar','Pending','Tgl Daftar']);
            foreach ($data as $c) {
                fputcsv($out, [
                    $c->name,
                    $c->phone ?? '-',
                    $c->area->name ?? '-',
                    $c->partner->name ?? '-',
                    $c->package->name ?? '-',
                    $statusLabel[$c->status] ?? ucfirst($c->status),
                    $c->paid_total ?? 0,
                    $c->pending_total ?? 0,
                    $c->created_at->format('d M Y'),
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Monthly payment report page
     */
    public function paymentReport(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $areaId = $request->input('area_id');

        $query = Payment::where('status', 'approved')
            ->where('periode_bulan', $month)
            ->where('periode_tahun', $year);

        if ($areaId) {
            $query->whereHas('customer', fn($q) => $q->where('area_id', $areaId));
        }

        // Summary
        $totalAmount = (clone $query)->sum('jumlah');
        $totalCount = (clone $query)->count();

        // Per-rekening breakdown
        $rekeningBreakdown = (clone $query)
            ->select('rekening_tujuan', DB::raw('SUM(jumlah) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('rekening_tujuan')
            ->orderByDesc('total')
            ->get();

        // Detail table
        $payments = (clone $query)
            ->with(['customer.area', 'approvedBy'])
            ->orderBy('approved_at', 'desc')
            ->paginate(50)
            ->withQueryString();

        $areas = Area::orderBy('name')->get(['id', 'name']);

        // Available years
        $years = Payment::selectRaw('DISTINCT periode_tahun as y')
            ->orderByDesc('y')
            ->pluck('y');
        if ($years->isEmpty()) {
            $years = collect([now()->year]);
        }

        return view('admin.reports.payments', compact(
            'month', 'year', 'areaId',
            'totalAmount', 'totalCount',
            'rekeningBreakdown', 'payments',
            'areas', 'years'
        ));
    }

    /**
     * Export payments as CSV
     */
    public function exportPayments(Request $request): StreamedResponse
    {
        $query = Payment::with(['customer.partner', 'customer.area', 'approvedBy'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        }
        if ($request->filled('area_id')) {
            $query->whereHas('customer', fn($q) => $q->where('area_id', $request->area_id));
        }
        if ($request->filled('partner_id')) {
            $query->whereHas('customer', fn($q) => $q->where('partner_id', $request->partner_id));
        }

        $payments = $query->get();

        $filename = 'payments_' . ($request->year ?? date('Y')) . '_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($payments) {
            $out = fopen('php://output', 'w');

            // BOM for Excel UTF-8
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header
            fputcsv($out, [
                'ID',
                'Customer',
                'Area',
                'PIC',
                'Jumlah',
                'Metode',
                'Rekening',
                'Periode',
                'Status',
                'Approved At',
                'Approved By',
                'Created At'
            ]);

            $totalApproved = 0;
            $totalPending = 0;

            foreach ($payments as $pmt) {
                $periode = sprintf('%02d/%04d', $pmt->periode_bulan, $pmt->periode_tahun);
                fputcsv($out, [
                    $pmt->id,
                    $pmt->customer?->name ?? '-',
                    $pmt->customer?->area?->name ?? '-',
                    $pmt->customer?->partner?->name ?? '-',
                    $pmt->jumlah,
                    $pmt->metode,
                    $pmt->rekening_tujuan ?? '-',
                    $periode,
                    $pmt->status,
                    $pmt->approved_at?->format('Y-m-d H:i') ?? '-',
                    $pmt->approvedBy?->name ?? '-',
                    $pmt->created_at?->format('Y-m-d H:i'),
                ]);

                if ($pmt->status === 'approved') $totalApproved += $pmt->jumlah;
                else if ($pmt->status === 'pending') $totalPending += $pmt->jumlah;
            }

            // Summary row
            fputcsv($out, []);
            fputcsv($out, ['SUMMARY', '', '', '', '', '', '', '', '', '', '', '']);
            fputcsv($out, ['Total Payments', count($payments)]);
            fputcsv($out, ['Total Approved', $totalApproved]);
            fputcsv($out, ['Total Pending', $totalPending]);

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

        $data = Payment::where('payments.status', 'approved')
            ->whereYear('payments.approved_at', $year)
            ->join('customers', 'payments.customer_id', '=', 'customers.id')
            ->leftJoin('areas', 'customers.area_id', '=', 'areas.id')
            ->leftJoin('users', 'customers.partner_id', '=', 'users.id')
            ->select(
                DB::raw('MONTH(payments.approved_at) as month'),
                'areas.name as area_name',
                'users.name as partner_name',
                DB::raw('SUM(payments.jumlah) as total'),
                DB::raw('COUNT(payments.id) as count')
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
            fputcsv($out, ['Month', 'Area', 'PIC', 'Payment Count', 'Revenue']);

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
