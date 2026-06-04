<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\ActivityLog;
use App\Services\ProvisioningService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SuspendOverdueCustomers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customers:suspend-overdue 
                            {--days=0 : Grace period in days before suspension}
                            {--dry-run : Simulate suspension without applying changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Suspend customers with invoices overdue by X days';

    /**
     * Execute the console command.
     */
    public function handle(ProvisioningService $provisioningService)
    {
        // Use CLI --days option, or fall back to config value
        $graceDays = (int) ($this->option('days') ?: config('billing.suspend_grace_days', 0));
        $isDryRun = $this->option('dry-run');

        $this->info("Checking for customers with overdue invoices (Grace period: {$graceDays} days)...");

        // Find customers who:
        // 1. Are currently 'active'
        // 2. Have at least one 'unpaid' invoice
        // 3. The invoice due_date + grace_days is in the past (overdue beyond grace period)

        $cutoffDate = now()->subDays($graceDays)->format('Y-m-d');

        $overdueCustomers = Customer::where('status', 'active')
            ->whereHas('invoices', function ($query) use ($cutoffDate) {
                $query->where('status', 'unpaid')
                    ->where('due_date', '<', $cutoffDate)
                    ->where(function ($review) {
                        $review->whereNull('payment_review_status')
                            ->orWhere('payment_review_status', '!=', 'submitted');
                    });
            })
            ->with(['area', 'invoices' => function ($query) use ($cutoffDate) {
                $query->where('status', 'unpaid')
                    ->where('due_date', '<', $cutoffDate)
                    ->where(function ($review) {
                        $review->whereNull('payment_review_status')
                            ->orWhere('payment_review_status', '!=', 'submitted');
                    })
                    ->orderBy('due_date');
            }])
            ->get();

        if ($overdueCustomers->isEmpty()) {
            $this->info("No overdue customers found.");
            return 0;
        }

        $this->info("Found {$overdueCustomers->count()} customers to suspend.");
        $bar = $this->output->createProgressBar($overdueCustomers->count());
        $bar->start();

        foreach ($overdueCustomers as $customer) {
            $oldestInvoice = $customer->invoices->first();

            if ($isDryRun) {
                $this->line("\n[DRY RUN] Would suspend: {$customer->name} ({$customer->pppoe_user}) - Due: {$oldestInvoice->due_date->format('Y-m-d')}");
                $bar->advance();
                continue;
            }

            try {
                // 1. Update status in local DB
                $customer->update([
                    'status' => 'suspended',
                    'error_message' => 'Suspended due to overdue invoice #' . $oldestInvoice->invoice_number,
                ]);

                // 2. Disable PPPoE on this customer's area-specific MikroTik router
                try {
                    $mikrotik = \App\Services\MikroTikService::forArea($customer->area);
                    if ($mikrotik->isConnected()) {
                        $mikrotik->toggleSecret($customer->pppoe_user, false);
                        $mikrotik->disconnectSession($customer->pppoe_user);
                        $this->line("\n   ✓ MikroTik: Disabled PPPoE for {$customer->pppoe_user}");
                    }
                } catch (\Exception $e) {
                    Log::warning("MikroTik disable failed for customer {$customer->id}: " . $e->getMessage());
                    $this->warn("\n   ⚠ MikroTik disable failed for {$customer->pppoe_user}: {$e->getMessage()}");
                }

                // 3. Log activity
                ActivityLog::logActivity(
                    'suspend',
                    "Auto-suspended customer {$customer->name} due to unpaid invoice {$oldestInvoice->invoice_number}",
                    null, // System performed
                    Customer::class,
                    $customer->id
                );

                Log::info("Auto-suspended customer {$customer->id} due to overdue invoice.");
            } catch (\Exception $e) {
                Log::error("Failed to suspend customer {$customer->id}: " . $e->getMessage());
                $this->error("\nFailed to suspend {$customer->name}: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Suspension process completed.");

        return 0;
    }
}
