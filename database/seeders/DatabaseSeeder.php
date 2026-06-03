<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Area;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create default area if not exists
        $area = Area::firstOrCreate(
            ['name' => 'Default Area'],
            [
                'router_ip' => env('MIKROTIK_HOST', ''),
                'router_user' => env('MIKROTIK_USERNAME', ''),
                'router_pass' => env('MIKROTIK_PASSWORD', ''),
                'ip_pool_start' => '10.10.1.1',
                'ip_pool_end' => '10.10.1.254',
            ]
        );

        $this->command->info('✓ Default area created/verified');

        // Create admin user from env (required in production)
        $adminPassword = env('ADMIN_DEFAULT_PASSWORD');

        if (empty($adminPassword)) {
            $this->command->error('✗ ADMIN_DEFAULT_PASSWORD must be set in .env');
            return;
        }

        $admin = User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'caesarbugar@netking.local')],
            [
                'name'       => 'Caesar Bugar',
                'password'   => Hash::make($adminPassword),
                'role'       => 'admin',
                'area_id'    => $area->id,
                'wallet_balance' => 0,
            ]
        );

        $this->command->info('✓ Admin user created/verified');
        $this->command->line("  Email: {$admin->email}");

        $this->command->newLine();
        $this->command->warn('⚠️  Remember to change the default password after first login!');
    }
}
