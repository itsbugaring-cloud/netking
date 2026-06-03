<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\CommissionLog;
use App\Models\ActivityLog;
use App\Services\BillingCalculator;
use Illuminate\Console\Command;

class GenerateMonthlyInvoices extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'invoices:generate
                            {--test : Run in test mode without creating invoices}';

    /**
     * The console command description.
     */
    protected $description = 'Generate monthly invoices for all active customers';

    /**
     * Execute the console command.
     */
    public function handle(BillingCalculator $billing): int
    {
        $this->info('Starting monthly invoice generation...');

        $testMode = $this->option('test');
        $periodYear = now()->year;
        $periodMonth = now()->month;
        $dueDate = $billing->resolveDueDateForPeriod($periodYear, $periodMonth);

        if ($testMode) {
            $this->warn('Running in TEST mode - no invoices will be created');
        }

        // Get all active customers
        $customers = Customer::where('status', 'active')
            ->with(['area', 'partner', 'package'])
            ->get();

        if ($customers->isEmpty()) {
            $this->warn('No active customers found');
            return self::FAILURE;
        }

        $this->info("Found {$customers->count()} active customers");

        $created = 0;
        $skipped = 0;
        $errors = 0;

        $bar = $this->output->createProgressBar($customers->count());
        $bar->start();

        foreach ($customers as $customer) {
            try {
                // Check if invoice already exists for this month
                $existingInvoice = Invoice::where('customer_id', $customer->id)
                    ->where(function ($q) use ($periodYear, $periodMonth) {
                        $q->where(function ($q2) use ($periodYear, $periodMonth) {
                            $q2->whereNotNull('period_year')
                                ->where('period_year', $periodYear)
                                ->where('period_month', $periodMonth);
                        })->orWhere(function ($q2) use ($periodYear, $periodMonth) {
                            $q2->whereNull('period_year')
                                ->whereYear('due_date', $periodYear)
                                ->whereMonth('due_date', $periodMonth);
                        });
                    })
                    ->first();

                if ($existingInvoice) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                $calculated = $billing->calculateForPeriod($customer, $periodYear, $periodMonth);
                if ($calculated['skip']) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                if (!$testMode) {
                    // Create invoice
                    $invoice = Invoice::create([
                        'invoice_number' => Invoice::generateInvoiceNumber(),
                        'customer_id' => $customer->id,
                        'amount' => $calculated['amount'],
                        'base_amount' => $calculated['base_amount'],
                        'billed_days' => $calculated['billed_days'],
                        'period_days' => $calculated['period_days'],
                        'period_month' => $calculated['period_month'],
                        'period_year' => $calculated['period_year'],
                        'is_prorated' => $calculated['is_prorated'],
                        'status' => 'unpaid',
                        'due_date' => $dueDate,
                    ]);

                    // Log activity
                    ActivityLog::logActivity(
                        'create',
                        "Invoice {$invoice->invoice_number} generated for customer {$customer->name}",
                        null, // System generated
                        Invoice::class,
                        $invoice->id
                    );

                    // Auto-create commission for partner (pending until customer pays)
                    if ($customer->partner_id) {
                        $commissionAmount = round(((float) $invoice->amount) / 3);

                        if ($commissionAmount > 0) {
                            CommissionLog::create([
                                'user_id' => $customer->partner_id,
                                'customer_id' => $customer->id,
                                'invoice_id' => $invoice->id,
                                'amount' => $commissionAmount,
                                'month' => now()->month,
                                'year' => now()->year,
                                'status' => 'pending',
                            ]);
                        }
                    }

                    $created++;
                } else {
                    $this->line("\nWould create invoice for: {$customer->name} - {$customer->pppoe_user}");
                    $created++;
                }

                $bar->advance();
            } catch (\Exception $e) {
                $errors++;
                $this->error("\nError creating invoice for customer {$customer->id}: {$e->getMessage()}");
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);

        // Summary
        $this->info('Invoice Generation Summary:');
        $this->table(
            ['Status', 'Count'],
            [
                ['Created', $created],
                ['Skipped (already exists)', $skipped],
                ['Errors', $errors],
            ]
        );

        if (!$testMode && $created > 0) {
            $this->info("✓ Successfully created {$created} invoices");

            // Log system activity
            ActivityLog::logActivity(
                'bulk_create',
                "Monthly invoice generation completed. Created: {$created}, Skipped: {$skipped}, Errors: {$errors}",
                null,
                null,
                null
            );
        }

        return self::SUCCESS;
    }
}
