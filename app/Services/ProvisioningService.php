<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Area;
use App\Models\Package;
use App\Jobs\CreateMikrotikSecretJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class ProvisioningService
{
    /**
     * Provision a new customer with automatic IP allocation
     *
     * @param array $data
     * @return Customer
     */
    public function provisionCustomer(array $data): Customer
    {
        return DB::transaction(function () use ($data) {
            // Get the area
            $area = Area::findOrFail($data['area_id']);

            if (Customer::forAreaPppoe($area->id, $data['pppoe_user'])->lockForUpdate()->exists()) {
                throw new \RuntimeException("PPPoE username {$data['pppoe_user']} sudah dipakai di area {$area->name}.");
            }

            // Calculate next available IP (with pessimistic lock)
            $nextIp = $this->getNextAvailableIp($area);

            // Resolve package price from Package model if package_id is provided
            $packagePrice = null;
            if (!empty($data['package_id'])) {
                $package = Package::find($data['package_id']);
                $packagePrice = $package?->price ?? config('billing.default_package_price', 100000);
            }

            // Create customer in database with provisioning status
            $customer = Customer::create([
                'partner_id' => $data['partner_id'],
                'area_id' => $data['area_id'],
                'package_id' => $data['package_id'] ?? null,
                'name' => $data['name'],
                'pppoe_user' => $data['pppoe_user'],
                'pppoe_pass' => $data['pppoe_pass'],
                'portal_password' => !empty($data['portal_password'])
                    ? (Hash::needsRehash($data['portal_password'])
                        ? Hash::make($data['portal_password'])
                        : $data['portal_password'])
                    : null,
                'remote_ip' => $nextIp,
                'local_address' => $data['local_address'] ?? null,
                'ont_sn' => $data['ont_sn'] ?? null,
                'package_price' => $packagePrice,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'status' => 'provisioning',
            ]);

            // Dispatch job to create MikroTik secret (async)
            CreateMikrotikSecretJob::dispatch($customer);

            Log::info("Customer {$customer->id} provisioned", [
                'ip' => $nextIp,
                'package_id' => $data['package_id'] ?? null,
                'area' => $area->name,
            ]);

            return $customer;
        });
    }

    /**
     * Calculate the next available IP from the area's pool
     * Uses pessimistic locking to prevent race conditions under concurrent requests.
     *
     * @param Area $area
     * @return string
     * @throws \Exception
     */
    private function getNextAvailableIp(Area $area): string
    {
        $startIp = $area->ip_pool_start;
        $endIp = $area->ip_pool_end;

        // Convert IP to long for calculation
        $startLong = ip2long($startIp);
        $endLong = ip2long($endIp);

        if ($startLong === false || $endLong === false) {
            throw new \Exception("Invalid IP pool range for area {$area->name}");
        }

        // CRITICAL: lockForUpdate() prevents concurrent requests from allocating the same IP.
        // This works because provisionCustomer() wraps everything in DB::transaction().
        $usedIps = Customer::where('area_id', $area->id)
            ->whereNotNull('remote_ip')
            ->lockForUpdate()
            ->pluck('remote_ip')
            ->toArray();

        $usedIpsLong = array_map('ip2long', $usedIps);

        // Find first available IP
        for ($ipLong = $startLong; $ipLong <= $endLong; $ipLong++) {
            if (!in_array($ipLong, $usedIpsLong)) {
                return long2ip($ipLong);
            }
        }

        throw new \Exception("No available IP addresses in pool for area {$area->name} ({$startIp} - {$endIp})");
    }

    /**
     * Mark customer as active (called after successful MikroTik provisioning)
     */
    public function markAsActive(Customer $customer): void
    {
        $customer->update([
            'status' => 'active',
            'error_message' => null,
        ]);
    }

    /**
     * Mark customer provisioning as failed
     */
    public function markAsFailed(Customer $customer, string $errorMessage): void
    {
        $customer->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }
}
