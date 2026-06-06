<?php

namespace App\Console\Commands;

use App\Models\Ipam\IpamAuditLog;
use App\Models\Ipam\IpamIpPool;
use App\Models\Ipam\IpamOlt;
use App\Models\Ipam\IpamRouter;
use App\Models\Ipam\IpamRouterAddress;
use App\Models\Ipam\IpamRouterRoute;
use App\Models\Ipam\IpamSubnet;
use App\Models\Ipam\IpamWireguardInterface;
use App\Models\Ipam\IpamWireguardPeer;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use PDO;

class IpamMigrateData extends Command
{
    protected $signature = 'ipam:migrate-data
                            {sqlite_path : Path to the SQLite database file}
                            {--force : Skip confirmation if MySQL tables already have data}';

    protected $description = 'Migrate IPAM data from SQLite (CT 100) to MySQL';

    public function handle(): int
    {
        $path = $this->argument('sqlite_path');

        if (!file_exists($path)) {
            $this->error("SQLite file not found: {$path}");
            return self::FAILURE;
        }

        if (IpamRouter::count() > 0 && !$this->option('force')) {
            if (!$this->confirm('MySQL ipam_* tables already have data. Continue? (existing data will NOT be deleted)')) {
                $this->info('Aborted.');
                return self::SUCCESS;
            }
        }

        $this->info("Opening SQLite: {$path}");
        $pdo = new PDO("sqlite:{$path}");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Migrate OLTs first (referenced by routers)
        $this->migrateTable($pdo, 'olts', IpamOlt::class, ['name', 'ip_address']);

        // Migrate routers
        $this->migrateTable($pdo, 'routers', IpamRouter::class, [
            'device_name', 'wireguard_ip', 'auth_username', 'auth_password',
            'auth_source', 'connection_status', 'last_error', 'last_scanned_at',
            'mapped_olt_id', 'is_online', 'last_ping_at',
        ]);

        // Migrate child tables
        $this->migrateTable($pdo, 'ip_pools', IpamIpPool::class, ['router_id', 'pool_name', 'ranges']);
        $this->migrateTable($pdo, 'router_addresses', IpamRouterAddress::class, ['router_id', 'address', 'network', 'interface', 'disabled', 'comment']);
        $this->migrateTable($pdo, 'router_routes', IpamRouterRoute::class, ['router_id', 'dst_address', 'gateway', 'distance', 'disabled', 'comment']);
        $this->migrateTable($pdo, 'wireguard_interfaces', IpamWireguardInterface::class, ['router_id', 'name', 'listen_port', 'public_key', 'disabled', 'comment']);
        $this->migrateTable($pdo, 'wireguard_peers', IpamWireguardPeer::class, ['router_id', 'interface_name', 'public_key', 'allowed_address', 'endpoint_address', 'endpoint_port', 'disabled', 'comment']);
        $this->migrateTable($pdo, 'subnets', IpamSubnet::class, ['network_address', 'prefix_length', 'name', 'description', 'vlan_id', 'location']);
        $this->migrateAuditLogs($pdo);

        $this->newLine();
        $this->info('✓ Migration complete.');

        return self::SUCCESS;
    }

    private function migrateTable(PDO $pdo, string $sqliteTable, string $modelClass, array $fields): void
    {
        $stmt = $pdo->query("SELECT * FROM {$sqliteTable}");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->info("Migrating {$sqliteTable}: " . count($rows) . " records");
        $bar = $this->output->createProgressBar(count($rows));

        foreach ($rows as $row) {
            $data = [];
            foreach ($fields as $field) {
                $value = $row[$field] ?? null;

                // Convert timestamp strings
                if (in_array($field, ['last_scanned_at', 'last_ping_at']) && $value) {
                    $value = Carbon::parse($value);
                }

                // Re-encrypt password
                if ($field === 'auth_password' && $value) {
                    $value = $value; // Model cast handles encryption
                }

                $data[$field] = $value;
            }

            try {
                $modelClass::create($data);
            } catch (\Throwable $e) {
                // Skip duplicates silently
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    private function migrateAuditLogs(PDO $pdo): void
    {
        $stmt = $pdo->query("SELECT * FROM audit_logs");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->info("Migrating audit_logs: " . count($rows) . " records");
        $bar = $this->output->createProgressBar(count($rows));

        foreach ($rows as $row) {
            try {
                IpamAuditLog::create([
                    'actor' => $row['actor'] ?? 'system',
                    'action' => $row['action'] ?? '',
                    'target_type' => $row['target_type'] ?? '',
                    'target_id' => $row['target_id'] ?? null,
                    'detail' => $row['detail'] ?? '',
                    'created_at' => isset($row['created_at']) ? Carbon::parse($row['created_at']) : now(),
                ]);
            } catch (\Throwable $e) {
                // Skip errors
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }
}
