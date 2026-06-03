<?php

namespace App\Imports;

use App\Models\Customer;
use App\Models\Package;
use App\Models\Area;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomersImport implements ToCollection, WithHeadingRow, SkipsOnFailure
{
    use SkipsFailures;

    protected $mode;
    protected $stats = [
        'success' => 0,
        'skipped' => 0,
        'failed' => 0,
    ];

    public function __construct($mode = 'create')
    {
        $this->mode = $mode; // 'create' or 'update'
    }

    /**
     * Process the collection of rows
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 because index starts at 0 and we have a header row

            // Each row in its own transaction — one failure won't roll back the entire import
            DB::beginTransaction();
            try {
                $this->processRow($row, $rowNumber);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $this->stats['failed']++;
                Log::error("Import failed at row {$rowNumber}: " . $e->getMessage(), [
                    'row' => $row->toArray()
                ]);

                // Collect the failure for reporting
                $this->onFailure(
                    new \Maatwebsite\Excel\Validators\Failure(
                        $rowNumber,
                        'general',
                        [$e->getMessage()],
                        $row->toArray()
                    )
                );
            }
        }
    }

    /**
     * Process a single row
     */
    protected function processRow($row, $rowNumber)
    {
        // Validate row data
        $validated = $this->validateRow($row, $rowNumber);

        // PPPoE usernames can repeat across areas, so imports must match by area + username.
        $existingCustomer = Customer::forAreaPppoe($validated['area_id'], $validated['pppoe_user'])->first();

        if ($existingCustomer && $this->mode === 'create') {
            $this->stats['skipped']++;
            return; // Skip existing customer in 'create' mode
        }

        // Get Area for IP allocation
        $area = Area::find($validated['area_id']);
        if (!$area) {
            throw new \Exception("Area not found for ID: {$validated['area_id']}");
        }

        // Handle IP allocation
        $remoteIp = $this->allocateIp($validated, $area, $existingCustomer);
        $validated['remote_ip'] = $remoteIp;

        // Map CSV field names to database field names
        $customerData = [
            'name' => $validated['name'],
            'pppoe_user' => $validated['pppoe_user'],
            'pppoe_pass' => $validated['pppoe_password'],
            'area_id' => $validated['area_id'],
            'partner_id' => $validated['partner_id'] ?? null,
            'package_id' => $validated['package_id'] ?? null,
            'remote_ip' => $validated['remote_ip'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'status' => 'provisioning',
        ];

        // Create or update customer
        if ($existingCustomer && $this->mode === 'update') {
            $existingCustomer->update($customerData);
            $this->stats['success']++;
        } else {
            Customer::create($customerData);
            $this->stats['success']++;
        }
    }

    /**
     * Validate row data
     */
    protected function validateRow($row, $rowNumber)
    {
        $validator = Validator::make($row->toArray(), [
            'name' => 'required|string|max:255',
            'pppoe_user' => 'required|string|max:255',
            'pppoe_password' => 'required|string|max:255',
            'area_id' => 'required|exists:areas,id',
            'partner_id' => 'nullable|exists:users,id',
            'package_id' => 'nullable|exists:packages,id',
            'remote_ip' => 'nullable|ip', // Optional IP
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $firstError = collect($errors)->flatten()->first();
            $firstField = collect($errors)->keys()->first();

            throw new \Exception("{$firstField}: {$firstError}");
        }

        return $validator->validated();
    }

    /**
     * Smart IP allocation logic
     * - If remote_ip is provided in CSV, validate it belongs to area pool and is available
     * - If remote_ip is empty, auto-allocate next available IP from area pool
     */
    protected function allocateIp($validated, Area $area, $existingCustomer = null)
    {
        $providedIp = $validated['remote_ip'] ?? null;

        // If IP is provided in CSV, validate it
        if ($providedIp) {
            // Check if IP belongs to area pool range
            $ipLong = ip2long($providedIp);
            $startLong = ip2long($area->ip_pool_start);
            $endLong = ip2long($area->ip_pool_end);

            if ($ipLong < $startLong || $ipLong > $endLong) {
                throw new \Exception("IP {$providedIp} does not belong to area pool {$area->ip_pool_start} - {$area->ip_pool_end}");
            }

            // Check if IP is already taken (excluding current customer if updating)
            $ipTaken = Customer::where('remote_ip', $providedIp)
                ->when($existingCustomer, function ($q) use ($existingCustomer) {
                    $q->where('id', '!=', $existingCustomer->id);
                })
                ->exists();

            if ($ipTaken) {
                throw new \Exception("IP {$providedIp} is already assigned to another customer");
            }

            return $providedIp;
        }

        // Auto-allocate IP from pool
        return $this->getNextAvailableIp($area, $existingCustomer);
    }

    /**
     * Get next available IP from area pool (uses ip_pool_start / ip_pool_end)
     */
    protected function getNextAvailableIp(Area $area, $existingCustomer = null)
    {
        $startLong = ip2long($area->ip_pool_start);
        $endLong = ip2long($area->ip_pool_end);

        if ($startLong === false || $endLong === false) {
            throw new \Exception("Invalid IP pool range for area {$area->name}");
        }

        // Get all assigned IPs in this area (excluding current customer if updating)
        $assignedIps = Customer::where('area_id', $area->id)
            ->when($existingCustomer, function ($q) use ($existingCustomer) {
                $q->where('id', '!=', $existingCustomer->id);
            })
            ->whereNotNull('remote_ip')
            ->pluck('remote_ip')
            ->toArray();

        $usedIpsLong = array_map('ip2long', $assignedIps);

        // Find first available IP
        for ($ipLong = $startLong; $ipLong <= $endLong; $ipLong++) {
            if (!in_array($ipLong, $usedIpsLong)) {
                return long2ip($ipLong);
            }
        }

        throw new \Exception("No available IPs in area {$area->name}");
    }

    /**
     * Get import statistics
     */
    public function getStats()
    {
        return $this->stats;
    }
}

