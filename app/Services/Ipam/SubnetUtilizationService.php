<?php

namespace App\Services\Ipam;

use App\Models\Ipam\IpamIpPool;
use App\Models\Ipam\IpamRouterAddress;
use App\Models\Ipam\IpamSubnet;
use Illuminate\Support\Collection;

class SubnetUtilizationService
{
    /**
     * Calculate utilization for a single subnet.
     *
     * @param IpamSubnet $subnet
     * @return array{total: int, used: int, available: int, percentage: float}
     */
    public function calculateUtilization(IpamSubnet $subnet): array
    {
        $prefix = (int) $subnet->prefix_length;
        $networkLong = ip2long($subnet->network_address);

        if ($networkLong === false) {
            return ['total' => 0, 'used' => 0, 'available' => 0, 'percentage' => 0.0];
        }

        // Calculate total usable IPs
        $totalIps = $this->calculateTotalUsableIps($prefix);

        // Get subnet start and end IPs
        [$subnetStart, $subnetEnd] = $this->getSubnetRange($networkLong, $prefix);

        // Collect all used IPs within this subnet
        $usedIps = $this->getUsedIps($subnetStart, $subnetEnd);

        $usedCount = count($usedIps);
        $availableCount = max(0, $totalIps - $usedCount);
        $percentage = $totalIps > 0 ? round(($usedCount / $totalIps) * 100, 2) : 0.0;

        return [
            'total' => $totalIps,
            'used' => $usedCount,
            'available' => $availableCount,
            'percentage' => $percentage,
        ];
    }

    /**
     * Find available (unallocated) IP space within a subnet.
     *
     * Returns a collection of contiguous available IP ranges.
     *
     * @param IpamSubnet $subnet
     * @return Collection<int, array{start: string, end: string, count: int}>
     */
    public function findAvailableSpace(IpamSubnet $subnet): Collection
    {
        $prefix = (int) $subnet->prefix_length;
        $networkLong = ip2long($subnet->network_address);

        if ($networkLong === false) {
            return collect();
        }

        [$subnetStart, $subnetEnd] = $this->getSubnetRange($networkLong, $prefix);

        // Determine the usable range (exclude network and broadcast for normal subnets)
        $usableStart = $subnetStart;
        $usableEnd = $subnetEnd;

        if ($prefix < 31) {
            $usableStart = $subnetStart + 1; // Skip network address
            $usableEnd = $subnetEnd - 1;     // Skip broadcast address
        }

        // Get all used IPs as a sorted array
        $usedIps = $this->getUsedIps($subnetStart, $subnetEnd);
        sort($usedIps);

        // Find contiguous available blocks
        $availableRanges = collect();
        $rangeStart = null;

        for ($ip = $usableStart; $ip <= $usableEnd; $ip++) {
            if (!in_array($ip, $usedIps, true)) {
                if ($rangeStart === null) {
                    $rangeStart = $ip;
                }
            } else {
                if ($rangeStart !== null) {
                    $availableRanges->push([
                        'start' => long2ip($rangeStart),
                        'end' => long2ip($ip - 1),
                        'count' => $ip - $rangeStart,
                    ]);
                    $rangeStart = null;
                }
            }
        }

        // Close the last range if open
        if ($rangeStart !== null) {
            $availableRanges->push([
                'start' => long2ip($rangeStart),
                'end' => long2ip($usableEnd),
                'count' => $usableEnd - $rangeStart + 1,
            ]);
        }

        return $availableRanges;
    }

    /**
     * Calculate utilization for all subnets.
     *
     * @return Collection<int, array{subnet: IpamSubnet, total: int, used: int, available: int, percentage: float}>
     */
    public function calculateAll(): Collection
    {
        return IpamSubnet::all()->map(function (IpamSubnet $subnet) {
            $utilization = $this->calculateUtilization($subnet);

            return array_merge(['subnet' => $subnet], $utilization);
        });
    }

    /**
     * Calculate total usable IPs for a given prefix length.
     * /32 = 1 IP, /31 = 2 IPs (point-to-point), others exclude network + broadcast.
     */
    private function calculateTotalUsableIps(int $prefix): int
    {
        $totalAddresses = (int) pow(2, 32 - $prefix);

        if ($prefix >= 31) {
            return $totalAddresses; // /31 and /32 don't exclude network/broadcast
        }

        return $totalAddresses - 2; // Exclude network and broadcast addresses
    }

    /**
     * Get the start and end IP (as longs) of a subnet.
     *
     * @return array{0: int, 1: int}
     */
    private function getSubnetRange(int $networkLong, int $prefix): array
    {
        $hostBits = 32 - $prefix;
        $subnetMask = $hostBits === 32 ? 0 : (~0 << $hostBits);

        // Ensure network address is properly masked
        $subnetStart = $networkLong & $subnetMask;
        $subnetEnd = $subnetStart | ((1 << $hostBits) - 1);

        return [$subnetStart, $subnetEnd];
    }

    /**
     * Get all used IP addresses (as longs) within a given IP range.
     *
     * Collects from:
     * - ipam_router_addresses (address field in format x.x.x.x/prefix)
     * - ipam_ip_pools (ranges field in format x.x.x.x-y.y.y.y)
     *
     * @return array<int>
     */
    private function getUsedIps(int $subnetStart, int $subnetEnd): array
    {
        $usedIps = [];

        // Collect IPs from router addresses
        $addresses = IpamRouterAddress::all();
        foreach ($addresses as $address) {
            $ip = $this->parseAddressIp($address->address);
            if ($ip !== null && $ip >= $subnetStart && $ip <= $subnetEnd) {
                $usedIps[] = $ip;
            }
        }

        // Collect IPs from IP pools
        $pools = IpamIpPool::all();
        foreach ($pools as $pool) {
            $ranges = $this->parsePoolRanges($pool->ranges);
            foreach ($ranges as [$rangeStart, $rangeEnd]) {
                // Only include IPs that fall within our subnet
                $overlapStart = max($rangeStart, $subnetStart);
                $overlapEnd = min($rangeEnd, $subnetEnd);

                for ($ip = $overlapStart; $ip <= $overlapEnd; $ip++) {
                    $usedIps[] = $ip;
                }
            }
        }

        // Return unique IPs
        return array_values(array_unique($usedIps));
    }

    /**
     * Parse CIDR address (e.g., "192.168.1.1/24") and return the IP as a long.
     */
    private function parseAddressIp(string $address): ?int
    {
        // Address format: x.x.x.x/prefix or just x.x.x.x
        $parts = explode('/', $address, 2);
        $ip = trim($parts[0]);

        $long = ip2long($ip);

        return $long !== false ? $long : null;
    }

    /**
     * Parse pool range string (e.g., "192.168.1.10-192.168.1.50") into start/end longs.
     * Supports multiple ranges separated by commas.
     *
     * @return array<int, array{0: int, 1: int}>
     */
    private function parsePoolRanges(string $ranges): array
    {
        $parsed = [];
        $rangeEntries = explode(',', $ranges);

        foreach ($rangeEntries as $entry) {
            $entry = trim($entry);
            if (empty($entry)) {
                continue;
            }

            $parts = explode('-', $entry, 2);
            if (count($parts) !== 2) {
                // Single IP, not a range
                $ip = ip2long(trim($parts[0]));
                if ($ip !== false) {
                    $parsed[] = [$ip, $ip];
                }
                continue;
            }

            $start = ip2long(trim($parts[0]));
            $end = ip2long(trim($parts[1]));

            if ($start !== false && $end !== false) {
                $parsed[] = [$start, $end];
            }
        }

        return $parsed;
    }
}
