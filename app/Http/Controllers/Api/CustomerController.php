<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\ProvisioningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    protected $provisioningService;

    public function __construct(ProvisioningService $provisioningService)
    {
        $this->provisioningService = $provisioningService;
    }

    /**
     * Get all customers for authenticated partner
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $customers = Customer::where('partner_id', $user->id)
            ->with('area:id,name')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'customers' => $customers,
        ]);
    }

    /**
     * Create new customer (Zero-Touch Provisioning)
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // Validate request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'pppoe_user' => [
                'required',
                'string',
                'max:255',
                Rule::unique('customers', 'pppoe_user')->where(fn($q) => $q->where('area_id', $user->area_id)),
            ],
            'package_id' => 'nullable|integer|exists:packages,id',
            'ont_sn' => 'nullable|string|max:255',
        ]);

        try {
            // Auto-generate cryptographically secure password if not provided
            $pppoePassword = $request->pppoe_pass ?? Str::random(12);

            // Prepare customer data — include package_id for proper billing
            $customerData = [
                'partner_id' => $user->id,
                'area_id' => $user->area_id, // Partner's area
                'name' => $validated['name'],
                'pppoe_user' => $validated['pppoe_user'],
                'pppoe_pass' => $pppoePassword,
                'package_id' => $validated['package_id'] ?? null,
                'ont_sn' => $validated['ont_sn'] ?? null,
            ];

            // Provision customer (creates DB record + dispatches MikroTik job)
            $customer = $this->provisioningService->provisionCustomer($customerData);

            Log::info("Customer created by partner {$user->id}: {$customer->id}");

            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully. Provisioning in progress...',
                'customer' => $customer->load('area:id,name'),
            ], 201);
        } catch (\Exception $e) {
            Log::error("Customer creation failed: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat customer. Silakan coba lagi atau hubungi administrator.',
            ], 500);
        }
    }

    /**
     * Get single customer details
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();

        $customer = Customer::where('id', $id)
            ->where('partner_id', $user->id)
            ->with('area:id,name')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'customer' => $customer,
        ]);
    }
}
