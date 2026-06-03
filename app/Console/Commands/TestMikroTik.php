<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MikroTikService;

class TestMikroTik extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mikrotik:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test MikroTik RouterOS API connection and operations';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('===========================================');
        $this->info('  MikroTik RouterOS API Test Suite');
        $this->info('===========================================');
        $this->newLine();
        
        $mikrotik = app(MikroTikService::class);
        
        // Test 1: Connection
        $this->info('[TEST 1] Testing MikroTik connection...');
        $result = $mikrotik->testConnection();
        
        if ($result['success']) {
            $this->info('✓ Connected successfully');
            $this->line('  Router Identity: ' . $result['identity']);
            $this->line('  Host: ' . $result['host']);
        } else {
            $this->error('✗ Connection failed: ' . $result['error']);
            $this->newLine();
            $this->error('Please check your .env MikroTik credentials!');
            return 1;
        }
        
        $this->newLine();
        
        // Test 2: Create Secret
        $this->info('[TEST 2] Testing create PPPoE secret...');
        $testUsername = 'test_' . time();
        $testPassword = 'testpass' . rand(100, 999);
        
        $result = $mikrotik->createSecret($testUsername, $testPassword);
        
        if ($result['success']) {
            $this->info('✓ PPPoE secret created successfully');
            $this->line('  Username: ' . $testUsername);
            $this->line('  Password: ' . $testPassword);
        } else {
            $this->error('✗ Create secret failed: ' . $result['error']);
            return 1;
        }
        
        $this->newLine();
        
        // Test 3: Disable Secret
        $this->info('[TEST 3] Testing disable secret...');
        $result = $mikrotik->toggleSecret($testUsername, false);
        
        if ($result['success']) {
            $this->info('✓ Secret disabled successfully');
        } else {
            $this->error('✗ Disable failed: ' . $result['error']);
        }
        
        $this->newLine();
        
        // Test 4: Enable Secret
        $this->info('[TEST 4] Testing enable secret...');
        $result = $mikrotik->toggleSecret($testUsername, true);
        
        if ($result['success']) {
            $this->info('✓ Secret enabled successfully');
        } else {
            $this->error('✗ Enable failed: ' . $result['error']);
        }
        
        $this->newLine();
        
        // Test 5: Get Active Sessions
        $this->info('[TEST 5] Testing get active sessions...');
        $result = $mikrotik->getActiveSessions();
        
        if ($result['success']) {
            $count = count($result['data']);
            $this->info('✓ Retrieved active sessions');
            $this->line('  Total active sessions: ' . $count);
        } else {
            $this->error('✗ Get sessions failed: ' . $result['error']);
        }
        
        $this->newLine();
        
        // Test 6: Delete Secret
        $this->info('[TEST 6] Testing delete secret...');
        $result = $mikrotik->deleteSecret($testUsername);
        
        if ($result['success']) {
            $this->info('✓ Secret deleted successfully');
        } else {
            $this->error('✗ Delete failed: ' . $result['error']);
        }
        
        $this->newLine();
        $this->info('===========================================');
        $this->info('  All tests completed! ✓');
        $this->info('===========================================');
        $this->newLine();
        
        $this->info('MikroTik integration is ready to use!');
        
        return 0;
    }
}
