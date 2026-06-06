<?php

namespace App\Services\Ipam;

use App\Models\Ipam\IpamIpPool;
use App\Models\Ipam\IpamRouter;
use App\Models\Ipam\IpamRouterAddress;
use App\Models\Ipam\IpamRouterRoute;
use App\Models\Ipam\IpamWireguardInterface;
use App\Models\Ipam\IpamWireguardPeer;
use App\Models\Setting;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MikroTikScannerService
{
    /**
     * Build an HTTP client configured for a specific router.
     */
    public function buildHttpClient(IpamRouter $router): PendingRequest
    {
        $useHttps = (bool) Setting::get('ipam.use_https', false);
        $scheme = $useHttps ? 'https' : 'http';
        $timeout = (int) Setting::get('ipam.request_timeout_secs', 20);
        $allowInsecure = (bool) Setting::get('ipam.allow_insecure_tls', true);

        // Router credentials override defaults
        $username = $router->auth_username ?: Setting::get('ipam.mikrotik_username', '');
        $password = $router->auth_password ?: Setting::get('ipam.mikrotik_password', '');

        $baseUrl = "{$scheme}://{$router->wireguard_ip}";

        $client = Http::baseUrl($baseUrl)
            ->withBasicAuth($username, $password)
            ->timeout($timeout)
            ->acceptJson();

        if ($allowInsecure) {
            $client = $client->withoutVerifying();
        }

        return $client;
    }

    /**
     * Check if a router can be scanned (cooldown enforcement).
     */
    public function canScan(IpamRouter $router): bool
    {
        if (is_null($router->last_scanned_at)) {
            return true;
        }

        $cooldown = (int) Setting::get('ipam.scan_cooldown_secs', 20);

        return $router->last_scanned_at->diffInSeconds(now()) >= $cooldown;
    }

    /**
     * Perform a health check on a router (verify connectivity).
     */
    public function healthCheck(IpamRouter $router): bool
    {
        try {
            $client = $this->buildHttpClient($router);
            $response = $client->get('/rest/system/resource');

            return $response->successful();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Fetch IP pools from a router.
     */
    private function fetchIpPools(PendingRequest $client): array
    {
        $response = $client->get('/rest/ip/pool');

        return $response->json() ?? [];
    }

    /**
     * Fetch IP addresses from a router.
     */
    private function fetchAddresses(PendingRequest $client): array
    {
        $response = $client->get('/rest/ip/address');

        return $response->json() ?? [];
    }

    /**
     * Fetch routes from a router.
     */
    private function fetchRoutes(PendingRequest $client): array
    {
        $response = $client->get('/rest/ip/route');

        return $response->json() ?? [];
    }

    /**
     * Fetch WireGuard interfaces from a router.
     */
    private function fetchWireguardInterfaces(PendingRequest $client): array
    {
        $response = $client->get('/rest/interface/wireguard');

        return $response->json() ?? [];
    }

    /**
     * Fetch WireGuard peers from a router.
     */
    private function fetchWireguardPeers(PendingRequest $client): array
    {
        $response = $client->get('/rest/interface/wireguard/peers');

        return $response->json() ?? [];
    }

    /**
     * Scan a single router: fetch all data, store results atomically.
     */
    public function scanRouter(IpamRouter $router): array
    {
        $result = [
            'success' => false,
            'router_id' => $router->id,
            'device_name' => $router->device_name,
            'error' => null,
        ];

        try {
            $client = $this->buildHttpClient($router);

            // Fetch all data from router
            $pools = $this->fetchIpPools($client);
            $addresses = $this->fetchAddresses($client);
            $routes = $this->fetchRoutes($client);
            $wgInterfaces = $this->fetchWireguardInterfaces($client);
            $wgPeers = $this->fetchWireguardPeers($client);

            // Store results atomically
            DB::transaction(function () use ($router, $pools, $addresses, $routes, $wgInterfaces, $wgPeers) {
                // Delete old records for this router
                $router->ipPools()->delete();
                $router->addresses()->delete();
                $router->routes()->delete();
                $router->wireguardInterfaces()->delete();
                $router->wireguardPeers()->delete();

                // Insert fresh IP pools
                foreach ($pools as $pool) {
                    IpamIpPool::create([
                        'router_id' => $router->id,
                        'pool_name' => $pool['name'] ?? '',
                        'ranges' => $pool['ranges'] ?? '',
                    ]);
                }

                // Insert fresh addresses
                foreach ($addresses as $addr) {
                    IpamRouterAddress::create([
                        'router_id' => $router->id,
                        'address' => $addr['address'] ?? '',
                        'network' => $addr['network'] ?? '',
                        'interface' => $addr['interface'] ?? '',
                        'disabled' => ($addr['disabled'] ?? 'false') === 'true',
                        'comment' => $addr['comment'] ?? null,
                    ]);
                }

                // Insert fresh routes
                foreach ($routes as $route) {
                    IpamRouterRoute::create([
                        'router_id' => $router->id,
                        'dst_address' => $route['dst-address'] ?? '',
                        'gateway' => $route['gateway'] ?? '',
                        'distance' => $route['distance'] ?? null,
                        'disabled' => ($route['disabled'] ?? 'false') === 'true',
                        'comment' => $route['comment'] ?? null,
                    ]);
                }

                // Insert fresh WireGuard interfaces
                foreach ($wgInterfaces as $wgIface) {
                    IpamWireguardInterface::create([
                        'router_id' => $router->id,
                        'name' => $wgIface['name'] ?? '',
                        'listen_port' => $wgIface['listen-port'] ?? null,
                        'public_key' => $wgIface['public-key'] ?? null,
                        'disabled' => ($wgIface['disabled'] ?? 'false') === 'true',
                        'comment' => $wgIface['comment'] ?? null,
                    ]);
                }

                // Insert fresh WireGuard peers
                foreach ($wgPeers as $peer) {
                    IpamWireguardPeer::create([
                        'router_id' => $router->id,
                        'interface_name' => $peer['interface'] ?? '',
                        'public_key' => $peer['public-key'] ?? '',
                        'allowed_address' => $peer['allowed-address'] ?? '',
                        'endpoint_address' => $peer['endpoint-address'] ?? null,
                        'endpoint_port' => $peer['endpoint-port'] ?? null,
                        'disabled' => ($peer['disabled'] ?? 'false') === 'true',
                        'comment' => $peer['comment'] ?? null,
                    ]);
                }

                // Update router status
                $router->update([
                    'connection_status' => 'connected',
                    'last_scanned_at' => now(),
                    'is_online' => true,
                    'last_ping_at' => now(),
                    'last_error' => null,
                ]);
            });

            // Audit log
            IpamAuditService::log(
                'scan',
                'router',
                $router->id,
                "Scan berhasil untuk router {$router->device_name} ({$router->wireguard_ip})"
            );

            $result['success'] = true;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $this->handleScanError($router, 'timeout', $e->getMessage());
            $result['error'] = "Connection timeout: {$e->getMessage()}";
        } catch (RequestException $e) {
            $statusCode = $e->response?->status();
            if (in_array($statusCode, [401, 403])) {
                $this->handleScanError($router, 'auth_failure', "Authentication failed (HTTP {$statusCode})");
                $result['error'] = "Authentication failed (HTTP {$statusCode})";
            } else {
                $this->handleScanError($router, 'request_error', $e->getMessage());
                $result['error'] = "Request error: {$e->getMessage()}";
            }
        } catch (\JsonException $e) {
            $this->handleScanError($router, 'invalid_json', "Invalid JSON response: {$e->getMessage()}");
            $result['error'] = "Invalid JSON: {$e->getMessage()}";
        } catch (\Throwable $e) {
            $errorMsg = $e->getMessage();

            // Detect TLS errors
            if (str_contains($errorMsg, 'SSL') || str_contains($errorMsg, 'TLS') || str_contains($errorMsg, 'certificate')) {
                $this->handleScanError($router, 'tls_error', "TLS error: {$errorMsg}");
                $result['error'] = "TLS error: {$errorMsg}";
            } else {
                $this->handleScanError($router, 'unknown', $errorMsg);
                $result['error'] = $errorMsg;
            }
        }

        return $result;
    }

    /**
     * Scan all registered routers with configurable concurrency.
     */
    public function scanAll(int $concurrency = null): Collection
    {
        $concurrency = $concurrency ?? (int) Setting::get('ipam.max_scan_concurrency', 8);
        $routers = IpamRouter::all();
        $results = collect();

        // Process routers in chunks matching concurrency
        $chunks = $routers->chunk($concurrency);

        foreach ($chunks as $chunk) {
            // Build pool of requests for concurrent execution
            $responses = Http::pool(function ($pool) use ($chunk) {
                foreach ($chunk as $router) {
                    if (!$this->canScan($router)) {
                        continue;
                    }

                    $useHttps = (bool) Setting::get('ipam.use_https', false);
                    $scheme = $useHttps ? 'https' : 'http';
                    $timeout = (int) Setting::get('ipam.request_timeout_secs', 20);
                    $allowInsecure = (bool) Setting::get('ipam.allow_insecure_tls', true);
                    $username = $router->auth_username ?: Setting::get('ipam.mikrotik_username', '');
                    $password = $router->auth_password ?: Setting::get('ipam.mikrotik_password', '');

                    $baseUrl = "{$scheme}://{$router->wireguard_ip}";

                    $request = $pool->as("health_{$router->id}")
                        ->baseUrl($baseUrl)
                        ->withBasicAuth($username, $password)
                        ->timeout($timeout)
                        ->acceptJson();

                    if ($allowInsecure) {
                        $request->withoutVerifying();
                    }

                    $request->get('/rest/system/resource');
                }
            });

            // Now sequentially scan each router that passed connectivity check
            foreach ($chunk as $router) {
                if (!$this->canScan($router)) {
                    $results->push([
                        'success' => false,
                        'router_id' => $router->id,
                        'device_name' => $router->device_name,
                        'error' => 'Cooldown active',
                    ]);
                    continue;
                }

                $result = $this->scanRouter($router);
                $results->push($result);
            }
        }

        return $results;
    }

    /**
     * Handle scan errors: update router status and log.
     */
    private function handleScanError(IpamRouter $router, string $type, string $message): void
    {
        $router->update([
            'connection_status' => 'error',
            'last_error' => $message,
            'is_online' => false,
            'last_ping_at' => now(),
        ]);

        Log::warning("IPAM scan error [{$type}] for router {$router->device_name}: {$message}");
    }
}
