<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PartnerController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'partner')
            ->with('area')
            ->withCount(['customers' => function ($q) {
                $q->where('status', 'active');
            }]);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $partners = $query->paginate(15)->withQueryString();

        return view('admin.partners.index', compact('partners'));
    }

    public function create()
    {
        $areas = Area::all();
        return view('admin.partners.create', compact('areas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'area_id' => 'required|exists:areas,id',
            'wallet_balance' => 'nullable|numeric|min:0',
            'telegram_username' => ['nullable', 'regex:/^@?[A-Za-z0-9_]{5,32}$/', 'unique:users,telegram_username'],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = 'partner';
        $validated['wallet_balance'] = $validated['wallet_balance'] ?? 0;
        $validated['telegram_username'] = $this->normalizeTelegramUsername($validated['telegram_username'] ?? null);

        User::create($validated);

        return redirect()->route('admin.partners.index')
            ->with('success', 'Partner created successfully');
    }

    public function show(User $partner)
    {
        $partner->load(['area', 'customers' => function ($query) {
            $query->latest()->take(10);
        }]);

        $stats = [
            'total_customers' => $partner->customers()->count(),
            'active_customers' => $partner->customers()->where('status', 'active')->count(),
            'provisioning' => $partner->customers()->where('status', 'provisioning')->count(),
            'total_commission' => $partner->commissionLogs()->sum('amount'),
            'unpaid_commission' => $partner->commissionLogs()->where('status', 'unpaid')->sum('amount'),
        ];

        return view('admin.partners.show', compact('partner', 'stats'));
    }

    public function edit(User $partner)
    {
        $areas = Area::all();
        return view('admin.partners.edit', compact('partner', 'areas'));
    }

    public function update(Request $request, User $partner)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $partner->id,
            'password' => 'nullable|string|min:8|confirmed',
            'area_id' => 'required|exists:areas,id',
            'wallet_balance' => 'required|numeric|min:0',
            'telegram_username' => ['nullable', 'regex:/^@?[A-Za-z0-9_]{5,32}$/', 'unique:users,telegram_username,' . $partner->id],
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }
        $validated['telegram_username'] = $this->normalizeTelegramUsername($validated['telegram_username'] ?? null);

        $partner->update($validated);

        return redirect()->route('admin.partners.index')
            ->with('success', 'Partner updated successfully');
    }

    private function normalizeTelegramUsername(?string $username): ?string
    {
        if ($username === null) {
            return null;
        }
        $clean = trim($username);
        if ($clean === '') {
            return null;
        }
        return strtolower(ltrim($clean, '@'));
    }

    public function destroy(User $partner)
    {
        if ($partner->customers()->exists()) {
            return back()->with('error', 'Cannot delete partner with existing customers');
        }

        $partner->delete();

        return redirect()->route('admin.partners.index')
            ->with('success', 'Partner deleted successfully');
    }
}
