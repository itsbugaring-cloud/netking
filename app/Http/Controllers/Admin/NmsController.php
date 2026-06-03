<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Area;
use App\Models\Customer;
use App\Models\Olt;
use App\Models\Ont;
use App\Services\AcsService;
use App\Services\MikroTikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class NmsController extends Controller
{
    /**
     * NMS Dashboard — OLT health, ACS stats, router connectivity
     */
    public function dashboard()
    {
        $olts = Olt::withCount([
            'onts',
            'onts as online_count' => fn($q) => $q->where('status', 'online'),
        ])->get();
        $areas = Area::withCount('customers')->get();
        $onts = Ont::with('area:id,name')->get(['id', 'area_id', 'status', 'rx_power']);

        $ontTotal = Ont::count();
        $ontOnline = Ont::where('status', 'online')->count();

        $acsDevices = $this->acsSnapshot();
        $acsTotal = $acsDevices->count();
        $acsOnline = $acsDevices->where('online', true)->count();
        $acsByArea = $acsDevices->groupBy(fn ($device) => $device['area'] ?? 'Unassigned');
        $ontsByArea = $onts->groupBy('area_id');

        $areaHealth = $areas->map(function ($area) use ($acsByArea, $ontsByArea) {
            $areaOnts = $ontsByArea->get($area->id, collect());
            $ontOnline = $areaOnts->where('status', 'online')->count();
            $ontTotal = $areaOnts->count();
            $ontOffline = max(0, $ontTotal - $ontOnline);

            $areaAcs = $acsByArea->get($area->name, collect());
            $acsOnline = $areaAcs->where('online', true)->count();
            $acsTotal = $areaAcs->count();
            $acsOffline = max(0, $acsTotal - $acsOnline);

            $scoreParts = [];
            if ($ontTotal > 0) {
                $scoreParts[] = ($ontOnline / $ontTotal) * 100;
            }
            if ($acsTotal > 0) {
                $scoreParts[] = ($acsOnline / $acsTotal) * 100;
            }

            $healthScore = !empty($scoreParts) ? (int) round(array_sum($scoreParts) / count($scoreParts)) : 0;

            return [
                'id' => $area->id,
                'name' => $area->name,
                'customers_count' => $area->customers_count,
                'ont_total' => $ontTotal,
                'ont_online' => $ontOnline,
                'ont_offline' => $ontOffline,
                'acs_total' => $acsTotal,
                'acs_online' => $acsOnline,
                'acs_offline' => $acsOffline,
                'router_ready' => !empty($area->router_ip),
                'health_score' => $healthScore,
            ];
        })->sortByDesc('health_score')->values();

        $acsUnassigned = $acsDevices->where('area', 'Unassigned')->count();
        $acsAmbiguous = $acsDevices->where('area', 'Ambiguous PPPoE')->count();
        $criticalOpticalCount = Ont::whereNotNull('rx_power')->where('rx_power', '<', -27)->count();

        $quickAlerts = [
            [
                'label' => 'ONT Offline',
                'value' => $ontTotal - $ontOnline,
                'tone' => ($ontTotal - $ontOnline) > 0 ? 'badge-failed' : 'badge-active',
            ],
            [
                'label' => 'ACS Unassigned',
                'value' => $acsUnassigned,
                'tone' => $acsUnassigned > 0 ? 'badge-pending' : 'badge-active',
            ],
            [
                'label' => 'Ambiguous PPPoE',
                'value' => $acsAmbiguous,
                'tone' => $acsAmbiguous > 0 ? 'badge-pending' : 'badge-active',
            ],
            [
                'label' => 'Optical Critical',
                'value' => $criticalOpticalCount,
                'tone' => $criticalOpticalCount > 0 ? 'badge-failed' : 'badge-active',
            ],
        ];

        $criticalOpticals = Ont::with(['area:id,name', 'olt:id,name', 'customer:id,name'])
            ->whereNotNull('rx_power')
            ->where('rx_power', '<', -27)
            ->orderBy('rx_power')
            ->limit(8)
            ->get();

        $topProblemAreas = $areaHealth
            ->sortByDesc(fn ($row) => ($row['ont_offline'] + $row['acs_offline']))
            ->take(5)
            ->values();

        $worstOlts = $olts
            ->map(function ($olt) {
                $offlineCount = max(0, $olt->onts_count - $olt->online_count);

                return [
                    'id' => $olt->id,
                    'name' => $olt->name,
                    'brand' => trim(($olt->brand ?? '') . ' ' . ($olt->model ?? '')),
                    'offline_count' => $offlineCount,
                    'online_count' => $olt->online_count,
                    'total_count' => $olt->onts_count,
                ];
            })
            ->sortByDesc('offline_count')
            ->take(5)
            ->values();

        $stats = [
            'olt_count' => $olts->count(),
            'ont_total' => $ontTotal,
            'ont_online' => $ontOnline,
            'ont_offline' => $ontTotal - $ontOnline,
            'acs_total' => $acsTotal,
            'acs_online' => $acsOnline,
            'area_count' => $areas->count(),
        ];

        return view('admin.nms.dashboard', compact('olts', 'areas', 'stats', 'quickAlerts', 'areaHealth', 'criticalOpticals', 'topProblemAreas', 'worstOlts'));
    }

    /**
     * NMS Devices — combined view of ACS + OLT devices
     */
    public function devices()
    {
        $olts = Olt::withCount([
            'onts',
            'onts as online_count' => fn($q) => $q->where('status', 'online'),
        ])->get();

        $acsDevices = $this->acsSnapshot();

        $areas = Area::all();

        return view('admin.nms.devices', compact('olts', 'acsDevices', 'areas'));
    }

    /**
     * Port Traffic — ONT port status per OLT
     */
    public function ports()
    {
        $olts = Olt::with(['onts' => fn($q) => $q->orderBy('pon_port')->orderBy('olt_port_index')])->get();
        return view('admin.nms.ports', compact('olts'));
    }

    /**
     * Alert Rules — recent issues and event log
     */
    public function alertRules()
    {
        // Recent activity related to errors, suspensions, status changes
        $alerts = ActivityLog::whereIn('action', ['status_changed', 'suspended', 'provisioned', 'failed', 'deleted'])
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        // Offline ONTs as alerts
        $offlineOnts = Ont::where('status', '!=', 'online')
            ->with('olt')
            ->orderBy('updated_at', 'desc')
            ->limit(30)
            ->get();

        return view('admin.nms.alert-rules', compact('alerts', 'offlineOnts'));
    }

    /**
     * Syslog — all activity logs
     */
    public function syslog()
    {
        $logs = ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        return view('admin.nms.syslog', compact('logs'));
    }

    /**
     * Topology — network map (vis.js LLDP-based)
     */
    public function topology()
    {
        $olts  = Olt::withCount('onts')->get();
        $areas = Area::withCount(['customers', 'odps'])->get();
        return view('admin.nms.nms_topology', compact('olts', 'areas'));
    }

    /**
     * Live Traffic — real-time bandwidth & session monitor per area
     */
    public function liveTraffic()
    {
        $areas = Area::whereNotNull('router_ip')->where('router_ip', '!=', '')->orderBy('name')->get();
        return view('admin.nms.nms_live_traffic', compact('areas'));
    }

    /**
     * Live Traffic API — fetches fresh data every 25s, calculates speed server-side.
     * Speed = (rx_bytes_now - rx_bytes_prev) / elapsed_seconds → converted to Mbps.
     * First call returns rx_mbps=null (not enough data yet).
     */
    public function liveTrafficData()
    {
        $areas = Area::whereNotNull('router_ip')->where('router_ip', '!=', '')->get();

        // Cache full fetch for 20s — monitorTraffic returns instantaneous speed (no delta needed)
        $payload = Cache::remember('nms_live_traffic_v3', 20, function () use ($areas) {
            $result    = [];
            $fetchedAt = time();

            foreach ($areas as $area) {
                $row = [
                    'area_id'   => $area->id,
                    'area_name' => $area->name,
                    'router_ip' => $area->router_ip,
                    'online'    => false,
                    'identity'  => null,
                    'sessions'  => 0,
                    'iface'     => null,
                    'rx_mbps'   => 0,
                    'tx_mbps'   => 0,
                    'error'     => null,
                ];
                try {
                    $mikrotik = MikroTikService::forArea($area);

                    // 1. Auto-detect WAN interface (also confirms connectivity)
                    $wanIface = $mikrotik->detectWanInterface();

                    if (!$wanIface) {
                        $row['error'] = 'No WAN interface found';
                        $result[] = $row;
                        continue;
                    }

                    $row['online'] = true;
                    $row['iface']  = $wanIface;

                    // 2. Get router identity
                    try {
                        $conn = $mikrotik->testConnection();
                        $row['identity'] = $conn['identity'] ?? $area->name;
                    } catch (\Exception $e) {
                        $row['identity'] = $area->name;
                    }

                    // 3. Active PPPoE sessions
                    try {
                        $sessions        = $mikrotik->getActiveSessions();
                        $row['sessions'] = $sessions['success'] ? count($sessions['data']) : 0;
                    } catch (\Exception $e) {
                        $row['sessions'] = 0;
                    }

                    // 4. Real-time traffic — monitor ALL meaningful interfaces at once
                    //    monitorAllInterfaces() returns instantaneous bps (like Winbox)
                    $allTraffic      = $mikrotik->monitorAllInterfaces();
                    $row['interfaces'] = $allTraffic;

                    // WAN total = the detected WAN interface stats
                    $wanTraffic      = collect($allTraffic)->firstWhere('name', $wanIface)
                                       ?? ($allTraffic[0] ?? null);
                    $row['rx_mbps']  = $wanTraffic['rx_mbps'] ?? 0;
                    $row['tx_mbps']  = $wanTraffic['tx_mbps'] ?? 0;

                } catch (\Exception $e) {
                    $row['error'] = $e->getMessage();
                }
                $result[] = $row;
            }

            return ['rows' => $result, 'fetched_at' => $fetchedAt];
        });

        return response()->json([
            'data'       => $payload['rows'],
            'updated_at' => date('H:i:s', $payload['fetched_at']),
            'ts'         => $payload['fetched_at'],
            'cache_ttl'  => 25,
        ]);
    }

    /**
     * Diagnostics — Ping / Traceroute dari dashboard
     */
    public function diagnostics()
    {
        $areas = Area::whereNotNull('router_ip')->where('router_ip', '!=', '')->orderBy('name')->get();
        return view('admin.nms.nms_diagnostics', compact('areas'));
    }

    /**
     * Diagnostics API — jalankan ping/traceroute via MikroTik
     */
    public function runDiagnostic(Request $request)
    {
        $request->validate([
            'area_id' => 'required|exists:areas,id',
            'tool'    => 'required|in:ping,traceroute',
            'target'  => 'required|string|max:253',
        ]);

        $area = Area::findOrFail($request->area_id);

        if (!$area->router_ip) {
            return response()->json(['success' => false, 'error' => 'Area ini tidak punya router IP.']);
        }

        try {
            $mikrotik = MikroTikService::forArea($area);

            if ($request->tool === 'ping') {
                $result = $mikrotik->ping($request->target, 5);
            } else {
                $result = $mikrotik->traceroute($request->target, 15);
            }

            $result['area_name']   = $area->name;
            $result['router_ip']   = $area->router_ip;
            $result['executed_at'] = now()->format('H:i:s');

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('NMS Diagnostic failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Topology API — ambil neighbors dari semua router (LLDP)
     */
    public function topologyData()
    {
        $areas = Area::whereNotNull('router_ip')->where('router_ip', '!=', '')->get();

        $nodes = []; $edges = []; $seen = [];

        // Node: VPS/Hub
        $nodes[] = ['id' => 'vps', 'label' => "VPS\nHub", 'group' => 'vps', 'title' => 'VPS 103.127.137.24'];

        foreach ($areas as $area) {
            $nodeId = 'area_' . $area->id;
            $nodes[] = [
                'id'    => $nodeId,
                'label' => $area->name,
                'group' => 'router',
                'title' => "Router: {$area->router_ip}\nArea: {$area->name}",
                'ip'    => $area->router_ip,
            ];
            $edges[] = ['from' => 'vps', 'to' => $nodeId, 'title' => 'WireGuard VPN'];

            // Ambil LLDP neighbors dari router
            try {
                $mikrotik  = MikroTikService::forArea($area);
                $neighbors = $mikrotik->getNeighbors();

                foreach ($neighbors as $neighbor) {
                    $nId = 'nbr_' . md5($neighbor['address'] . $neighbor['identity']);
                    if (!isset($seen[$nId])) {
                        $seen[$nId] = true;
                        $nodes[] = [
                            'id'    => $nId,
                            'label' => $neighbor['identity'],
                            'group' => 'neighbor',
                            'title' => "IP: {$neighbor['address']}\nPlatform: {$neighbor['platform']}\nBoard: {$neighbor['board']}",
                            'ip'    => $neighbor['address'],
                        ];
                    }
                    $edgeKey = $nodeId . '-' . $nId;
                    $edges[] = [
                        'from'  => $nodeId,
                        'to'    => $nId,
                        'title' => "Interface: {$neighbor['interface']}",
                        'id'    => $edgeKey,
                    ];
                }
            } catch (\Exception $e) {
                // skip router yang tidak bisa diakses
            }
        }

        return response()->json(compact('nodes', 'edges'));
    }

    /**
     * Router Status API — cek semua router online/offline
     */
    public function routerStatus()
    {
        $areas = Area::whereNotNull('router_ip')->where('router_ip', '!=', '')->get();

        $statuses = Cache::remember('nms_router_status', 30, function () use ($areas) {
            return $areas->map(function ($area) {
                try {
                    $mikrotik = MikroTikService::forArea($area);
                    $result   = $mikrotik->testConnection();
                    return [
                        'area_id'   => $area->id,
                        'area_name' => $area->name,
                        'router_ip' => $area->router_ip,
                        'online'    => $result['success'],
                        'identity'  => $result['identity'] ?? null,
                        'error'     => $result['error'] ?? null,
                    ];
                } catch (\Exception $e) {
                    return [
                        'area_id'   => $area->id,
                        'area_name' => $area->name,
                        'router_ip' => $area->router_ip,
                        'online'    => false,
                        'error'     => $e->getMessage(),
                    ];
                }
            })->values();
        });

        return response()->json(['statuses' => $statuses]);
    }

    public function deviceMonitor(int $id)
    {
        return view('admin.nms.device-monitor', compact('id'));
    }

    /**
     * NMS API endpoint — returns live stats as JSON
     */
    public function apiData(Request $request)
    {
        $action = $request->get('action', 'summary');

        if ($action === 'summary') {
            $ontTotal = Ont::count();
            $ontOnline = Ont::where('status', 'online')->count();

            try {
                $allDevices = $this->acsSnapshot();
                $acsTotal = $allDevices->count();
                $acsOnline = $allDevices->where('online', true)->count();
            } catch (\Exception $e) {
                $acsTotal = 0;
                $acsOnline = 0;
            }

            return response()->json([
                'olt_count' => Olt::count(),
                'ont_total' => $ontTotal,
                'ont_online' => $ontOnline,
                'acs_total' => $acsTotal,
                'acs_online' => $acsOnline,
                'area_count' => Area::count(),
            ]);
        }

        if ($action === 'mikrotik_test') {
            $areaId = $request->get('area_id');
            $area = Area::find($areaId);
            if (!$area || !$area->router_ip) {
                return response()->json(['success' => false, 'error' => 'Area not found']);
            }
            try {
                $mikrotik = \App\Services\MikroTikService::forArea($area);
                $result = $mikrotik->testConnection();
                return response()->json($result);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'error' => $e->getMessage()]);
            }
        }

        return response()->json(['error' => 'Unknown action'], 400);
    }

    /**
     * Legacy API Proxy (fallback for old NMS backend)
     */
    public function apiProxy(Request $request, string $endpoint)
    {
        return response()->json(['success' => false, 'error' => 'NMS backend not configured. Use built-in NMS dashboard.'], 503);
    }

    // ──────────────────────────────────────────────────────────────
    //  IP POOL PER AREA
    // ──────────────────────────────────────────────────────────────

    /**
     * IP Pool page — shows per-area IP pool allocation and utilization
     */
    public function ipPool()
    {
        $areas = Area::whereNotNull('router_ip')->where('router_ip', '!=', '')->orderBy('name')->get();
        return view('admin.nms.nms_ip_pool', compact('areas'));
    }

    /**
     * IP Pool API — fetches live pool data from each area's router
     */
    public function ipPoolData()
    {
        $areas = Area::whereNotNull('router_ip')->where('router_ip', '!=', '')->get();

        $payload = Cache::remember('nms_ip_pool_v1', 60, function () use ($areas) {
            $result = [];
            foreach ($areas as $area) {
                $row = [
                    'area_id'   => $area->id,
                    'area_name' => $area->name,
                    'router_ip' => $area->router_ip,
                    'online'    => false,
                    'pools'     => [],
                    'error'     => null,
                ];
                try {
                    $mikrotik  = MikroTikService::forArea($area);
                    $pools     = $mikrotik->getIpPools();
                    $row['online'] = true;
                    $row['pools']  = $pools;
                } catch (\Exception $e) {
                    $row['error'] = $e->getMessage();
                }
                $result[] = $row;
            }
            return $result;
        });

        return response()->json(['data' => $payload, 'updated_at' => now()->format('H:i:s')]);
    }

    // ──────────────────────────────────────────────────────────────
    //  SESSION KILL (JSON endpoint)
    // ──────────────────────────────────────────────────────────────

    /**
     * Session Kill — disconnect a PPPoE session via MikroTik API
     * POST /admin/nms/session-kill
     * Body: { area_id, username }
     */
    public function sessionKill(Request $request)
    {
        $request->validate([
            'area_id'  => 'required|exists:areas,id',
            'username' => 'required|string|max:100',
        ]);

        $area = Area::findOrFail($request->area_id);

        if (!$area->router_ip) {
            return response()->json(['success' => false, 'error' => 'Area ini tidak punya router IP.'], 422);
        }

        try {
            $mikrotik = MikroTikService::forArea($area);
            $result   = $mikrotik->disconnectSession($request->username);

            Log::info('NMS Session Kill', [
                'area'     => $area->name,
                'username' => $request->username,
                'result'   => $result,
            ]);

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('NMS Session Kill failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    //  BGP MONITOR
    // ──────────────────────────────────────────────────────────────

    /**
     * BGP Monitor page
     */
    public function bgpMonitor()
    {
        $areas = Area::whereNotNull('router_ip')->where('router_ip', '!=', '')->orderBy('name')->get();
        return view('admin.nms.nms_bgp', compact('areas'));
    }

    /**
     * BGP Monitor API — fetches BGP peer status from all routers
     */
    public function bgpData()
    {
        $areas = Area::whereNotNull('router_ip')->where('router_ip', '!=', '')->get();

        $payload = Cache::remember('nms_bgp_v1', 30, function () use ($areas) {
            $result = [];
            foreach ($areas as $area) {
                $row = [
                    'area_id'   => $area->id,
                    'area_name' => $area->name,
                    'router_ip' => $area->router_ip,
                    'online'    => false,
                    'peers'     => [],
                    'error'     => null,
                ];
                try {
                    $mikrotik    = MikroTikService::forArea($area);
                    $peers       = $mikrotik->getBgpPeers();
                    $row['online'] = true;
                    $row['peers']  = $peers;
                } catch (\Exception $e) {
                    $row['error'] = $e->getMessage();
                }
                $result[] = $row;
            }
            return $result;
        });

        return response()->json(['data' => $payload, 'updated_at' => now()->format('H:i:s')]);
    }

    /**
     * Live Traffic Sessions API — returns active PPPoE sessions for a specific area
     * GET /admin/nms/live-traffic/{area}/sessions
     */
    public function liveTrafficSessions(int $areaId)
    {
        $area = Area::findOrFail($areaId);

        if (!$area->router_ip) {
            return response()->json(['success' => false, 'error' => 'No router configured']);
        }

        try {
            $mikrotik = MikroTikService::forArea($area);
            $sessions = $mikrotik->getActiveSessions();

            if (!$sessions['success']) {
                return response()->json(['success' => false, 'error' => $sessions['error'] ?? 'Failed']);
            }

            // Return minimal session info (username, uptime, IP, bytes, current rate)
            $data = collect($sessions['data'])->map(fn($s) => [
                'username'  => $s['name'] ?? '—',
                'address'   => $s['address'] ?? '—',
                'uptime'    => $s['uptime'] ?? '—',
                'bytes_in'  => (int)($s['bytes-in']  ?? 0),
                'bytes_out' => (int)($s['bytes-out'] ?? 0),
                'rate_in'   => (int)($s['rate-in']   ?? 0),
                'rate_out'  => (int)($s['rate-out']  ?? 0),
                'caller_id' => $s['caller-id'] ?? '—',
                'service'   => $s['service'] ?? '—',
            ])->values();

            return response()->json(['success' => true, 'area_name' => $area->name, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    private function acsSnapshot()
    {
        return Cache::remember('nms_acs_snapshot', now()->addSeconds(60), function () {
            try {
                $acs = app(AcsService::class);
                $customersByPppoe = Customer::with('area')
                    ->whereNotNull('pppoe_user')
                    ->get()
                    ->groupBy(fn ($c) => strtolower(trim($c->pppoe_user)));
                $ontsBySn = Ont::with(['area', 'customer'])
                    ->whereNotNull('serial_number')
                    ->get()
                    ->keyBy(fn ($o) => strtoupper(trim($o->serial_number)));

                return collect($acs->getDevices(500, 0, "", \App\Services\AcsService::PROJECTION_SUMMARY))
                    ->map(function ($d) use ($acs, $customersByPppoe, $ontsBySn) {
                        $parsed = $acs->parseDevice($d);
                        $parsed['area'] = 'Unassigned';
                        $parsed['customer_name'] = null;

                        $sn = strtoupper(trim($parsed['serial'] ?? ''));
                        if ($sn !== '' && isset($ontsBySn[$sn])) {
                            $ont = $ontsBySn[$sn];
                            $parsed['area'] = $ont->area->name ?? 'Unassigned';
                            $parsed['customer_name'] = $ont->customer->name ?? null;

                            if (!$parsed['pppoe_user'] && $ont->customer?->pppoe_user) {
                                $parsed['pppoe_user'] = $ont->customer->pppoe_user;
                            }

                            if (!$parsed['rx_power'] && $ont->rx_power !== null) {
                                $parsed['rx_power'] = number_format((float) $ont->rx_power, 2) . ' dBm';
                            }
                        }

                        $pppoe = strtolower(trim($parsed['pppoe_user'] ?? ''));
                        if ($parsed['customer_name'] === null && $pppoe !== '' && isset($customersByPppoe[$pppoe])) {
                            $candidates = $customersByPppoe[$pppoe];
                            if ($candidates->count() === 1) {
                                $customer = $candidates->first();
                                $parsed['area'] = $customer->area->name ?? $parsed['area'];
                                $parsed['customer_name'] = $customer->name;
                                $parsed['pppoe_user'] = $parsed['pppoe_user'] ?: $customer->pppoe_user;
                            } else {
                                $parsed['area'] = 'Ambiguous PPPoE';
                            }
                        }

                        return $parsed;
                    })
                    ->filter(fn($d) => !str_starts_with((string) ($d['id'] ?? ''), 'DISCOVERY'))
                    ->values();
            } catch (\Exception $e) {
                return collect();
            }
        });
    }
}
