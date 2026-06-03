<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
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

        // Search invoices
        $invoices = Invoice::where('invoice_number', 'like', "%{$q}%")
            ->limit(5)
            ->get(['id', 'invoice_number', 'amount', 'status']);

        foreach ($invoices as $inv) {
            $results[] = [
                'type' => 'invoice',
                'icon' => 'bx-receipt',
                'title' => $inv->invoice_number,
                'subtitle' => 'Rp ' . number_format($inv->amount, 0, ',', '.'),
                'url' => route('admin.invoices.show', $inv->id),
                'badge' => $inv->status,
            ];
        }

        // Search partners
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
                'url' => route('admin.partners.show', $p->id),
                'badge' => 'partner',
            ];
        }

        return response()->json(['results' => $results]);
    }
}
