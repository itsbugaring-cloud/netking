<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AcsService
{
    private string $url;
    private int $timeout;

    public function __construct()
    {
        $this->url     = rtrim(config('genieacs.url', 'http://10.88.0.100:7557'), '/');
        $this->timeout = (int) config('genieacs.timeout', 10);
    }

    // ── Queries ──

    // Full projection for ACS management page
    public const PROJECTION_FULL = [
        '_id', '_deviceId', '_lastInform',
        'InternetGatewayDevice.DeviceInfo.SoftwareVersion',
        'InternetGatewayDevice.DeviceInfo.HardwareVersion',
        'InternetGatewayDevice.DeviceInfo.UpTime',
        'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.ExternalIPAddress',
        'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.ConnectionStatus',
        'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Username',
        'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.DefaultGateway',
        'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANIPConnection.1.ExternalIPAddress',
        'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANIPConnection.1.ConnectionStatus',
        'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANIPConnection.1.ConnectionType',
        'InternetGatewayDevice.WANDevice.1.WANGponInterfaceConfig.RXPower',
        'InternetGatewayDevice.WANDevice.1.WANGponInterfaceConfig.TXPower',
        'InternetGatewayDevice.WANDevice.1.WANGponInterfaceConfig.BiasCurrent',
        'InternetGatewayDevice.WANDevice.1.WANGponInterfaceConfig.TransceiverTemperature',
        'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID',
        'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.Channel',
        'InternetGatewayDevice.LANDevice.1.WLANConfiguration.5.SSID',
        'InternetGatewayDevice.LANDevice.1.WLANConfiguration.5.Channel',
    ];

    // Minimal projection fields for dashboard/NMS snapshot (much smaller response)
    public const PROJECTION_SUMMARY = [
        '_id', '_deviceId', '_lastInform',
        'InternetGatewayDevice.DeviceInfo.SoftwareVersion',
        'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Username',
        'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANIPConnection.1.ExternalIPAddress',
        'InternetGatewayDevice.WANDevice.1.WANGponInterfaceConfig.RXPower',
        'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID',
    ];

    public function getDevices(int $limit = 100, int $skip = 0, string $search = '', ?array $projection = null): array
    {
        try {
            $params = ['limit' => $limit, 'skip' => $skip];
            if ($search !== '') {
                $params['query'] = json_encode(['_deviceId._SerialNumber' => trim($search)]);
            }
            if ($projection !== null) {
                $params['projection'] = implode(',', $projection);
            }

            $response = $this->request()->get("{$this->url}/devices", $params);

            if (!$response->successful()) {
                Log::warning('ACS getDevices non-200: ' . $response->status());
                return [];
            }

            $json = $response->json();
            if (is_array($json) && array_is_list($json)) {
                return $json;
            }
            return [];
        } catch (\Exception $e) {
            Log::warning('ACS getDevices failed: ' . $e->getMessage());
            return [];
        }
    }

    public function getDevice(string $deviceId): ?array
    {
        $devices = $this->getDevices(1, 0, $deviceId);
        return $devices[0] ?? null;
    }

    // ── Parse GenieACS native format ──

    public function parseDevice(array $raw): array
    {
        $igd   = $raw['InternetGatewayDevice'] ?? [];
        $devId = $raw['_deviceId'] ?? [];

        // Serial / Identity from _deviceId
        $serial       = $devId['_SerialNumber'] ?? '';
        $manufacturer = $devId['_Manufacturer'] ?? 'Unknown';
        $model        = $devId['_ProductClass'] ?? 'Unknown';

        // Online detection via _lastInform (15-minute window)
        $lastInformTs = null;
        if (!empty($raw['_lastInform'])) {
            $lastInformTs = strtotime($raw['_lastInform']);
        }
        $isOnline = $lastInformTs && (time() - $lastInformTs) < 900;
        $lastSeen = $lastInformTs ? $this->humanTime(time() - $lastInformTs) : 'Unknown';

        // Device info
        $firmware      = $this->nv($igd, ['DeviceInfo', 'SoftwareVersion']) ?? '';
        $hardware      = $this->nv($igd, ['DeviceInfo', 'HardwareVersion']) ?? '';
        $uptimeSeconds = (int) ($this->nv($igd, ['DeviceInfo', 'UpTime']) ?? 0);

        // WiFi 2.4GHz
        $ssid24      = $this->nv($igd, ['LANDevice', '1', 'WLANConfiguration', '1', 'SSID']) ?? '';
        $wifiChannel = $this->nv($igd, ['LANDevice', '1', 'WLANConfiguration', '1', 'Channel']) ?? '';
        $wifiSec     = $this->nv($igd, ['LANDevice', '1', 'WLANConfiguration', '1', 'BeaconType']) ?? '';
        $wifiClients = (int) ($this->nv($igd, ['LANDevice', '1', 'WLANConfiguration', '1', 'TotalAssociations']) ?? 0);

        // WiFi 5GHz
        $ssid5    = $this->nv($igd, ['LANDevice', '1', 'WLANConfiguration', '5', 'SSID']) ?? '';
        $wifi5Ch  = (int) ($this->nv($igd, ['LANDevice', '1', 'WLANConfiguration', '5', 'Channel']) ?? 0);
        $wifi5Sec = $this->nv($igd, ['LANDevice', '1', 'WLANConfiguration', '5', 'BeaconType']) ?? '';
        $wifi5Cl  = (int) ($this->nv($igd, ['LANDevice', '1', 'WLANConfiguration', '5', 'TotalAssociations']) ?? 0);
        $hasDualBand = $ssid5 !== '' || $wifi5Ch > 14;

        // WAN — search multiple WANConnectionDevice indices for PPPoE/IP
        $wanIp     = null;
        $wanStatus = null;
        $pppoeUser = null;
        $wanMode   = null;
        $wanGw     = null;
        $wanDns    = '';

        $wanDevices = $igd['WANDevice'] ?? [];
        foreach (['1', '2', '3'] as $wi) {
            $wanDev  = $wanDevices[$wi] ?? [];
            $connDevs = $wanDev['WANConnectionDevice'] ?? [];
            foreach (['1', '2', '3'] as $ci) {
                $conn = $connDevs[$ci] ?? [];

                // Try PPPoE first
                $ppp   = $conn['WANPPPConnection']['1'] ?? [];
                $pppIp = $this->nvArr($ppp, 'ExternalIPAddress');
                if ($pppIp && $pppIp !== '0.0.0.0' && !$wanIp) {
                    $wanIp     = $pppIp;
                    $wanStatus = $this->nvArr($ppp, 'ConnectionStatus');
                    $pppoeUser = $this->nvArr($ppp, 'Username');
                    $wanMode   = $this->nvArr($ppp, 'ConnectionType') ?? 'PPPoE';
                    $wanGw     = $this->nvArr($ppp, 'DefaultGateway');
                    $wanDns    = $this->nvArr($ppp, 'DNSServers') ?? '';
                }

                // Try IP (DHCP)
                $ipConn = $conn['WANIPConnection']['1'] ?? [];
                $ipIp   = $this->nvArr($ipConn, 'ExternalIPAddress');
                if ($ipIp && $ipIp !== '0.0.0.0' && !$wanIp) {
                    $wanIp     = $ipIp;
                    $wanStatus = $this->nvArr($ipConn, 'ConnectionStatus');
                    $wanMode   = $this->nvArr($ipConn, 'ConnectionType') ?? 'DHCP';
                    $wanGw     = $this->nvArr($ipConn, 'DefaultGateway');
                    $wanDns    = $this->nvArr($ipConn, 'DNSServers') ?? '';
                }
            }
        }

        $dns        = array_filter(array_map('trim', explode(',', (string) $wanDns)));
        $wanProfile = $pppoeUser ? 'pppoe' : ($wanIp ? 'dhcp' : 'unknown');

        // Optical (GPON)
        $gponCfg     = $igd['WANDevice']['1']['WANGponInterfaceConfig'] ?? [];
        $rxPowerRaw  = $this->nvArr($gponCfg, 'RXPower');
        $txPowerRaw  = $this->nvArr($gponCfg, 'TXPower');
        $biasCurrent = $this->nvArr($gponCfg, 'BiasCurrent') ?? '';
        $temperature = $this->nvArr($gponCfg, 'TransceiverTemperature') ?? '';
        $supplyVolt  = $this->nvArr($gponCfg, 'SupplyVottage') ?? '';
        $gponStatus  = $this->nv($igd, ['WANDevice', '1', 'WANConnectionDevice', '1', 'WANGponLinkConfig', 'GPONLinkStatus']) ?? '';

        // LAN
        $lanIp     = $this->nv($igd, ['LANDevice', '1', 'LANHostConfigManagement', 'IPInterface', '1', 'IPInterfaceIPAddress']) ?? '';
        $lanSubnet = $this->nv($igd, ['LANDevice', '1', 'LANHostConfigManagement', 'IPInterface', '1', 'IPInterfaceSubnetMask']) ?? '';
        $dhcpCl    = (int) ($this->nv($igd, ['LANDevice', '1', 'LANHostConfigManagement', 'DHCPLeaseNumberOfEntries']) ?? 0);

        return [
            'id'             => $serial,
            'serial'         => $serial,
            'serial_number'  => $serial,
            'manufacturer'   => $manufacturer,
            'model'          => $model ?: 'Unknown',
            'oui'            => $devId['_OUI'] ?? '',
            'tags'           => [],
            'online'         => $isOnline,
            'status'         => $isOnline ? 'online' : 'offline',
            'last_seen'      => $lastSeen,
            'last_inform'    => $lastInformTs ? date('Y-m-d H:i:s', $lastInformTs) : null,
            'firmware'       => $firmware,
            'hardware'       => $hardware,
            'uptime'         => $uptimeSeconds > 0 ? $this->formatDuration($uptimeSeconds) : '',
            'uptime_seconds' => $uptimeSeconds,
            'ssid'           => $ssid24,
            'wifi_channel'   => (string) $wifiChannel,
            'wifi_security'  => $wifiSec,
            'wifi_clients'   => $wifiClients,
            'ssid5'          => $ssid5,
            'wifi5_channel'  => $hasDualBand ? (string) $wifi5Ch : '',
            'wifi5_security' => $wifi5Sec,
            'wifi5_clients'  => $hasDualBand ? $wifi5Cl : 0,
            'dual_band'      => $hasDualBand,
            'band_label'     => $hasDualBand ? 'Dual Band' : 'Single Band',
            'wan_ip'         => $wanIp ?? '',
            'wan_gateway'    => $wanGw ?? '',
            'wan_dns'        => $dns,
            'wan_status'     => $wanStatus ?? '',
            'wan_mode'       => $wanMode ?? '',
            'wan_profile'    => $wanProfile,
            'nat_enabled'    => null,
            'pppoe_user'     => $pppoeUser ?? '',
            'rx_power'       => $this->parseOpticalPower($rxPowerRaw),
            'tx_power'       => $this->parseOpticalPower($txPowerRaw),
            'rx_power_raw'   => $rxPowerRaw,
            'tx_power_raw'   => $txPowerRaw,
            'gpon_status'    => $gponStatus,
            'bias_current'   => $biasCurrent,
            'temperature'    => $temperature,
            'supply_voltage' => $supplyVolt,
            'lan_ip'         => $lanIp,
            'lan_subnet'     => $lanSubnet,
            'dhcp_clients'   => $dhcpCl,
        ];
    }

    // ── Actions (GenieACS Task API) ──

    public function reboot(string $deviceId): bool
    {
        return $this->sendTask($deviceId, ['name' => 'reboot']);
    }

    public function refresh(string $deviceId): bool
    {
        return $this->sendTask($deviceId, [
            'name'           => 'getParameterValues',
            'parameterNames' => [''],
        ]);
    }

    public function factoryReset(string $deviceId): bool
    {
        return $this->sendTask($deviceId, ['name' => 'factoryReset']);
    }

    public function setSsid(string $deviceId, string $ssid, ?string $password = null): bool
    {
        $params = [
            ['InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID', $ssid, 'xsd:string'],
        ];
        if ($password) {
            $params[] = ['InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.KeyPassphrase', $password, 'xsd:string'];
        }
        return $this->sendTask($deviceId, [
            'name'            => 'setParameterValues',
            'parameterValues' => $params,
        ]);
    }

    public function setSsid5(string $deviceId, string $ssid, ?string $password = null): bool
    {
        $params = [
            ['InternetGatewayDevice.LANDevice.1.WLANConfiguration.5.SSID', $ssid, 'xsd:string'],
        ];
        if ($password) {
            $params[] = ['InternetGatewayDevice.LANDevice.1.WLANConfiguration.5.KeyPassphrase', $password, 'xsd:string'];
        }
        return $this->sendTask($deviceId, [
            'name'            => 'setParameterValues',
            'parameterValues' => $params,
        ]);
    }

    public function setPppoe(string $deviceId, string $username, string $password): bool
    {
        return $this->sendTask($deviceId, [
            'name'            => 'setParameterValues',
            'parameterValues' => [
                ['InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Username', $username, 'xsd:string'],
                ['InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Password', $password, 'xsd:string'],
            ],
        ]);
    }

    public function setWanConfig(string $deviceId, string $mode, ?string $username = null, ?string $password = null, ?bool $natEnabled = null): bool
    {
        $mode = strtolower(trim($mode));
        if ($mode === 'pppoe' && $username) {
            return $this->setPppoe($deviceId, $username, $password ?? '');
        }
        return false;
    }

    // ── Unused stubs (not applicable to native GenieACS NBI) ──

    public function getWan(string $deviceId): ?array    { return null; }
    public function getWifi(string $deviceId): ?array   { return null; }
    public function getOptical(string $deviceId): ?array { return null; }

    // ── Helpers ──

    /**
     * Nested Value — traverse nested array by key path and return _value.
     * e.g. nv($igd, ['DeviceInfo', 'SoftwareVersion']) returns _value string
     */
    private function nv(array $data, array $keys): ?string
    {
        $cur = $data;
        foreach ($keys as $key) {
            if (!is_array($cur) || !isset($cur[$key])) return null;
            $cur = $cur[$key];
        }
        if (!is_array($cur)) return (string) $cur;
        $v = $cur['_value'] ?? null;
        if ($v === null || $v === '') return null;
        return (string) $v;
    }

    /**
     * Get _value from a direct child of a node.
     */
    private function nvArr(array $node, string $key): ?string
    {
        $child = $node[$key] ?? null;
        if (!is_array($child)) return null;
        $v = $child['_value'] ?? null;
        if ($v === null || $v === '') return null;
        return (string) $v;
    }

    private function sendTask(string $deviceId, array $task): bool
    {
        try {
            $url      = "{$this->url}/devices/{$deviceId}/tasks?timeout=3000";
            $response = $this->request()->post($url, $task);
            return $response->successful() || $response->status() === 202;
        } catch (\Exception $e) {
            Log::error('ACS sendTask failed: ' . $e->getMessage());
            return false;
        }
    }

    private function request()
    {
        return Http::timeout($this->timeout);
    }

    private function humanTime(int $seconds): string
    {
        if ($seconds < 60)    return $seconds . 's ago';
        if ($seconds < 3600)  return floor($seconds / 60) . 'm ago';
        if ($seconds < 86400) return floor($seconds / 3600) . 'h ago';
        return floor($seconds / 86400) . 'd ago';
    }

    private function formatDuration(int $seconds): string
    {
        $days    = intdiv($seconds, 86400);
        $hours   = intdiv($seconds % 86400, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $parts   = [];
        if ($days > 0)    $parts[] = $days . ' hari';
        if ($hours > 0)   $parts[] = $hours . ' jam';
        if ($minutes > 0 || empty($parts)) $parts[] = $minutes . ' menit';
        return implode(' ', array_slice($parts, 0, 2));
    }

    private function parseOpticalPower($raw): ?string
    {
        if ($raw === null || $raw === '') return null;
        $val = (float) $raw;
        if ($val === 0.0) return null;
        if (abs($val) > 100) $val = $val / 1000;
        return number_format($val, 2) . ' dBm';
    }

    public static function b64id(string $deviceId): string
    {
        return rtrim(strtr(base64_encode($deviceId), '+/', '-_'), '=');
    }
}
