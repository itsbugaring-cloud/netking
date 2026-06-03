<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommissionLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        $query = CommissionLog::with(['user', 'customer', 'invoice']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('partner_id')) {
            $query->where('user_id', $request->partner_id);
        }
        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        $commissions = $query->latest()->paginate(25)->withQueryString();

        $stats = [
            'total_pending' => CommissionLog::where('status', 'pending')->sum('amount'),
            'total_unpaid'  => CommissionLog::where('status', 'unpaid')->sum('amount'),
            'total_paid'    => CommissionLog::where('status', 'paid')->sum('amount'),
        ];

        // Per-partner summary for selected period
        $partnerSummaryQuery = CommissionLog::with('user')
            ->select('user_id', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as cnt'),
                     DB::raw('SUM(CASE WHEN status="unpaid" THEN amount ELSE 0 END) as unpaid_total'),
                     DB::raw('SUM(CASE WHEN status="paid" THEN amount ELSE 0 END) as paid_total'))
            ->groupBy('user_id');
        if ($request->filled('month')) $partnerSummaryQuery->where('month', $request->month);
        if ($request->filled('year'))  $partnerSummaryQuery->where('year', $request->year);
        $partnerSummary = $partnerSummaryQuery->get();

        $partners = User::where('role', 'partner')->orderBy('name')->get(['id', 'name']);

        return view('admin.commissions.index', compact('commissions', 'stats', 'partnerSummary', 'partners'));
    }

    public function export(Request $request): StreamedResponse
    {
        $query = CommissionLog::with(['user', 'customer', 'invoice']);
        if ($request->filled('status'))     $query->where('status', $request->status);
        if ($request->filled('partner_id')) $query->where('user_id', $request->partner_id);
        if ($request->filled('month'))      $query->where('month', $request->month);
        if ($request->filled('year'))       $query->where('year', $request->year);
        $data = $query->latest()->get();

        $monthNames = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        $filename = 'komisi_' . ($request->year ?? date('Y'))
            . ($request->month ? '_' . str_pad($request->month,2,'0',STR_PAD_LEFT) : '')
            . '_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($data, $monthNames) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['Mitra','Pelanggan','No. Invoice','Periode','Komisi','Status','Dibayar Pada']);
            foreach ($data as $row) {
                fputcsv($out, [
                    $row->user->name ?? '-',
                    $row->customer->name ?? '-',
                    $row->invoice->invoice_number ?? '-',
                    ($monthNames[$row->month] ?? '-') . ' ' . $row->year,
                    $row->amount,
                    match($row->status) { 'paid'=>'Lunas', 'unpaid'=>'Dikonfirmasi', default=>'Tertunda' },
                    $row->paid_at?->format('d M Y') ?? '-',
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function paySingle(Request $request, CommissionLog $commission)
    {
        if ($commission->status === 'paid') {
            return back()->with('error', 'Komisi sudah dibayar.');
        }

        $request->validate([
            'payment_method' => 'nullable|string|max:100',
            'payment_notes'  => 'nullable|string|max:500',
            'payment_proof'  => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:3072',
        ]);

        $proofPath = null;
        if ($request->hasFile('payment_proof')) {
            $proofPath = $request->file('payment_proof')
                ->store('commissions/proofs', 'public');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($commission, $request, $proofPath) {
            $commission->update([
                'status'         => 'paid',
                'paid_at'        => now(),
                'payment_method' => $request->payment_method,
                'payment_notes'  => $request->payment_notes,
                'payment_proof'  => $proofPath,
            ]);

            // Update partner wallet balance
            $commission->user->increment('wallet_balance', $commission->amount);
        });

        return back()->with('success', 'Komisi Rp ' . number_format($commission->amount, 0, ',', '.') . ' berhasil dicairkan ke ' . $commission->user->name . '.');
    }

    public function payCommissions(Request $request)
    {
        $validated = $request->validate([
            'commission_ids' => 'required|array',
            'commission_ids.*' => 'exists:commission_logs,id',
        ]);

        $paidCount = 0;

        \Illuminate\Support\Facades\DB::transaction(function () use ($validated, &$paidCount) {
            $commissions = CommissionLog::whereIn('id', $validated['commission_ids'])
                ->where('status', 'unpaid')
                ->lockForUpdate()
                ->get();

            foreach ($commissions as $commission) {
                $commission->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);

                $commission->user->increment('wallet_balance', $commission->amount);
                $paidCount++;
            }
        });

        return back()->with('success', $paidCount . ' commission(s) paid successfully');
    }
}
