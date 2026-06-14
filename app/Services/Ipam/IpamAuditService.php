<?php

namespace App\Services\Ipam;

use App\Models\Ipam\IpamAuditLog;

class IpamAuditService
{
    /**
     * Record an IPAM audit log entry.
     *
     * @param string      $action     Operation type (e.g. 'create', 'update', 'delete', 'scan', 'import', 'map')
     * @param string      $targetType Entity type (e.g. 'router', 'olt', 'subnet', 'settings')
     * @param int|null    $targetId   Entity identifier (null for non-entity actions)
     * @param string      $detail     Human-readable description of the action
     */
    public static function log(string $action, string $targetType, ?int $targetId, string $detail): void
    {
        IpamAuditLog::create([
            'actor'       => auth()->user()?->name ?? 'system',
            'action'      => $action,
            'target_type' => $targetType,
            'target_id'   => $targetId,
            'detail'      => $detail,
            'created_at'  => now(),
        ]);
    }
}
