<?php

namespace App\Console\Commands;

use App\Models\CommissionLog;
use App\Models\User;
use App\Notifications\CommissionRecapNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendMonthlyCommissionRecap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commission:send-monthly-recap';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send monthly commission recap to admin users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Calculating monthly commission recap...');

        // Calculate total unpaid commission across all partners
        $totalUnpaid = CommissionLog::where('status', 'unpaid')->sum('amount');

        // Get top 5 partners with highest unpaid balance
        $topPartners = CommissionLog::select('user_id', DB::raw('SUM(amount) as total_unpaid'))
            ->where('status', 'unpaid')
            ->groupBy('user_id')
            ->orderByDesc('total_unpaid')
            ->limit(5)
            ->with('user:id,name')
            ->get();

        // Format top partners data
        $topPartnersData = $topPartners->map(function ($item) {
            return [
                'name' => $item->user->name ?? 'Unknown Partner',
                'amount' => $item->total_unpaid,
            ];
        })->toArray();

        // Get the top partner (first in the list)
        $topPartner = $topPartnersData[0] ?? null;

        $this->info("Total Unpaid: Rp " . number_format($totalUnpaid, 0, ',', '.'));
        
        if ($topPartner) {
            $this->info("Top Partner: {$topPartner['name']} (Rp " . number_format($topPartner['amount'], 0, ',', '.') . ")");
        }

        // Get all admin users
        $admins = User::where('role', 'admin')->get();

        if ($admins->isEmpty()) {
            $this->warn('No admin users found. Skipping notification.');
            return Command::FAILURE;
        }

        // Send notification to each admin
        foreach ($admins as $admin) {
            $admin->notify(new CommissionRecapNotification(
                $totalUnpaid,
                $topPartner,
                $topPartnersData
            ));
            
            $this->info("Notification sent to admin: {$admin->email}");
        }

        $this->info('Monthly commission recap sent successfully!');
        
        return Command::SUCCESS;
    }
}
