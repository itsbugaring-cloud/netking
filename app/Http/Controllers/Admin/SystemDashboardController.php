<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Services\MikroTikService;

class SystemDashboardController extends Controller
{
    public function index()
    {
        $areas = Area::whereNotNull('router_ip')
            ->where('router_ip', '!=', '')
            ->orderBy('name')
            ->get();

        return view('admin.system-dashboard.index', compact('areas'));
    }

    public function data()
    {
        $areas = Area::whereNotNull('router_ip')
            ->where('router_ip', '!=', '')
            ->orderBy('name')
            ->get();

        $routers = [];

        foreach ($areas as $area) {
            $mikrotik = MikroTikService::forArea($area);
            $router = [
                'area_id' => $area->id,
                'area_name' => $area->name,
                'router_ip' => $area->router_ip,
                'status' => 'offline',
                'health' => 'offline',
                'resource' => null,
                'identity' => null,
                'license' => null,
                'hardware' => null,
                'error' => null,
            ];

            $test = $mikrotik->testConnection();
            if (!($test['success'] ?? false)) {
                $router['error'] = $test['error'] ?? 'Connection failed';
                $routers[] = $router;
                continue;
            }

            $router['status'] = 'online';
            $router['identity'] = $test['identity'] ?? $area->name;

            // Get resource
            $resource = $mikrotik->getSystemResource();
            if ($resource['success'] ?? false) {
                $data = $resource['data'];
                $totalMem = (int)($data['total-memory'] ?? 0);
                $freeMem = (int)($data['free-memory'] ?? 0);
                $totalDisk = (int)($data['total-hdd-space'] ?? 0);
                $freeDisk = (int)($data['free-hdd-space'] ?? 0);
                $cpuLoad = (int)($data['cpu-load'] ?? 0);

                $memUsedPct = $totalMem > 0 ? round((($totalMem - $freeMem) / $totalMem) * 100) : 0;
                $diskUsedPct = $totalDisk > 0 ? round((($totalDisk - $freeDisk) / $totalDisk) * 100) : 0;

                $router['resource'] = [
                    'cpu_load' => $cpuLoad,
                    'mem_total' => $totalMem,
                    'mem_free' => $freeMem,
                    'mem_used_pct' => $memUsedPct,
                    'disk_total' => $totalDisk,
                    'disk_free' => $freeDisk,
                    'disk_used_pct' => $diskUsedPct,
                    'uptime' => $data['uptime'] ?? 'N/A',
                    'version' => $data['version'] ?? 'N/A',
                    'board_name' => $data['board-name'] ?? 'N/A',
                    'architecture' => $data['architecture-name'] ?? 'N/A',
                    'cpu_count' => (int)($data['cpu-count'] ?? 1),
                ];

                // Determine health
                if ($cpuLoad >= 80 || $memUsedPct >= 80) {
                    $router['health'] = 'critical';
                } elseif ($cpuLoad >= 60 || $memUsedPct >= 60 || $diskUsedPct >= 80) {
                    $router['health'] = 'warning';
                } else {
                    $router['health'] = 'online';
                }
            }

            // Get license
            $license = $mikrotik->getSystemLicense();
            if ($license['success'] ?? false) {
                $router['license'] = [
                    'level' => $license['data']['nlevel'] ?? $license['data']['level'] ?? 'N/A',
                    'software_id' => $license['data']['software-id'] ?? 'N/A',
                ];
            }

            // Get hardware health (optional)
            $health = $mikrotik->getSystemHealth();
            if ($health['success'] ?? false) {
                $healthData = $health['data'];
                $temp = null;
                $voltage = null;

                if (isset($healthData[0]['name'])) {
                    // ROS7 format: array of {name, value, type}
                    foreach ($healthData as $item) {
                        if (str_contains($item['name'] ?? '', 'temperature')) {
                            $temp = (float)($item['value'] ?? 0);
                        }
                        if (str_contains($item['name'] ?? '', 'voltage')) {
                            $voltage = (float)($item['value'] ?? 0);
                        }
                    }
                } else {
                    // ROS6 format: single object
                    $temp = isset($healthData[0]['cpu-temperature']) ? (float)$healthData[0]['cpu-temperature'] : null;
                    $voltage = isset($healthData[0]['voltage']) ? (float)$healthData[0]['voltage'] : null;
                }

                if ($temp !== null || $voltage !== null) {
                    $router['hardware'] = [
                        'temperature' => $temp,
                        'voltage' => $voltage,
                    ];
                    if ($temp !== null && $temp > 70) {
                        $router['health'] = 'warning';
                    }
                }
            }

            $routers[] = $router;
        }

        return response()->json(['routers' => $routers, 'timestamp' => now()->toIso8601String()]);
    }
}
