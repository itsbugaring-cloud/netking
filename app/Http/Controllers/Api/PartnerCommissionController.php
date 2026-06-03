<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CommissionLog;
use App\Models\CommissionWithdrawalRequest;

class PartnerCommissionController extends Controller
{
    public function index(Request $request)
    {
        $partner = $request->user();

        // Total available (pending)
        $totalPending = CommissionLog::where('user_id', $partner->id)
            ->where('status', 'pending')
            ->sum('amount');

        // Total paid
        $totalPaid = CommissionLog::where('user_id', $partner->id)
            ->where('status', 'paid')
            ->sum('amount');

        // Monthly breakdown (last 6 months)
        $months = [];
        for ($i = 0; $i < 6; $i++) {
            $date = now()->subMonths($i);
            $month = $date->month;
            $year = $date->year;

            $total = CommissionLog::where('user_id', $partner->id)
                ->where('month', $month)
                ->where('year', $year)
                ->sum('amount');

            $count = CommissionLog::where('user_id', $partner->id)
                ->where('month', $month)
                ->where('year', $year)
                ->count();

            $status = CommissionLog::where('user_id', $partner->id)
                ->where('month', $month)
                ->where('year', $year)
                ->value('status') ?? 'pending';

            $months[] = [
                'month' => $month,
                'year' => $year,
                'label' => $date->translatedFormat('F Y'),
                'total' => $total,
                'customer_count' => $count,
                'status' => $status,
            ];
        }

        // Pending withdrawal request
        $pendingWithdrawal = CommissionWithdrawalRequest::where('user_id', $partner->id)
            ->where('status', 'pending')
            ->first();

        return response()->json([
            'data' => [
                'total_pending' => $totalPending,
                'total_paid' => $totalPaid,
                'total_available' => $totalPending + $totalPaid,
                'months' => $months,
                'pending_withdrawal' => $pendingWithdrawal ? [
                    'id' => $pendingWithdrawal->id,
                    'amount' => $pendingWithdrawal->amount,
                    'status' => $pendingWithdrawal->status,
                    'created_at' => $pendingWithdrawal->created_at,
                ] : null,
            ],
        ]);
    }

    public function detail(Request $request, int $month, int $year)
    {
        $partner = $request->user();

        $logs = CommissionLog::where('user_id', $partner->id)
            ->where('month', $month)
            ->where('year', $year)
            ->with('customer:id,name,package_id', 'customer.package:id,name')
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'customer_name' => $log->customer?->name ?? '-',
                    'package' => $log->customer?->package?->name ?? '-',
                    'amount' => $log->amount,
                    'status' => $log->status,
                ];
            });

        return response()->json(['data' => $logs]);
    }

    public function requestWithdrawal(Request $request)
    {
        $request->validate([
            'payment_method'       => 'required|string|max:50',
            'payment_account'      => 'required|string|max:100',
            'payment_account_name' => 'required|string|max:150',
            'notes'                => 'nullable|string|max:500',
        ]);

        $partner = $request->user();

        // Prevent duplicate pending request
        $existing = CommissionWithdrawalRequest::where('user_id', $partner->id)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Anda masih memiliki permintaan pencairan yang sedang diproses.',
                'data' => $existing,
            ], 409);
        }

        $totalPending = CommissionLog::where('user_id', $partner->id)
            ->where('status', 'pending')
            ->sum('amount');

        if ($totalPending <= 0) {
            return response()->json([
                'message' => 'Tidak ada komisi yang bisa dicairkan.',
            ], 422);
        }

        $withdrawal = CommissionWithdrawalRequest::create([
            'user_id'              => $partner->id,
            'amount'               => $totalPending,
            'status'               => 'pending',
            'payment_method'       => $request->payment_method,
            'payment_account'      => $request->payment_account,
            'payment_account_name' => $request->payment_account_name,
            'notes'                => $request->notes,
        ]);

        return response()->json([
            'message' => 'Permintaan pencairan berhasil dikirim. Admin akan memproses dalam 1-3 hari kerja.',
            'data'    => $withdrawal,
        ], 201);
    }
}
