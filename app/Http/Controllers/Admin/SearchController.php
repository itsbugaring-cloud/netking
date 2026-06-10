<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $q = $request->get('q', '');
        if (strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $results = [];

        // Search customers
        $customers = Customer::where('name', 'like', "%{$q}%")
            ->orWhere('pppoe_user', 'like', "%{$q}%")
            ->orWhere('phone', 'like', "%{$q}%")
            ->limit(5)
            ->get(['id', 'name', 'pppoe_user', 'status']);

        foreach ($customers as $c) {
            $results[] = [
                'type' => 'customer',
                'icon' => 'bx-user',
                'title' => $c->name,
                'subtitle' => $c->pppoe_user ?? '',
                'url' => route('admin.customers.show', $c->id),
                'badge' => $c->status,
            ];
        }

        // Search payments by customer name
        $payments = Payment::whereHas('customer', function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%");
            })
            ->with('customer:id,name')
            ->where('status', 'pending')
            ->limit(5)
            ->get();

        foreach ($payments as $pmt) {
            $results[] = [
                'type' => 'payment',
                'icon' => 'bx-money',
                'title' => ($pmt->customer->name ?? 'Unknown') . ' - Rp ' . number_format($pmt->jumlah, 0, ',', '.'),
                'subtitle' => ucfirst($pmt->status) . ' · ' . sprintf('%02d/%04d', $pmt->periode_bulan, $pmt->periode_tahun),
                'url' => route('admin.payments.review'),
                'badge' => $pmt->status,
            ];
        }

        // Search partners (link to user management since partner module removed)
        $partners = User::where('role', 'partner')
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            })
            ->limit(5)
            ->get(['id', 'name', 'email']);

        foreach ($partners as $p) {
            $results[] = [
                'type' => 'partner',
                'icon' => 'bx-buildings',
                'title' => $p->name,
                'subtitle' => $p->email,
                'url' => route('admin.users.index', ['search' => $p->name]),
                'badge' => 'partner',
            ];
        }

        return response()->json(['results' => $results]);
    }
}
