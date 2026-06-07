<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AggregateTraffic extends Command
{
    protected $signature = 'traffic:aggregate';
    protected $description = 'Aggregate daily traffic into monthly summaries and clean old records';

    public function handle(): int
    {
        $month = today()->startOfMonth()->toDateString();

        // Aggregate current month
        DB::statement("
            INSERT INTO traffic_monthly (customer_id, month, total_bytes_in, total_bytes_out, area_id)
            SELECT customer_id, '{$month}', SUM(bytes_in), SUM(bytes_out), area_id
            FROM traffic_daily
            WHERE date >= '{$month}'
            GROUP BY customer_id, area_id
            ON DUPLICATE KEY UPDATE
                total_bytes_in = VALUES(total_bytes_in),
                total_bytes_out = VALUES(total_bytes_out)
        ");

        // Retention: delete daily records older than 90 days
        $cutoff = today()->subDays(90)->toDateString();
        $deleted = DB::table('traffic_daily')->where('date', '<', $cutoff)->delete();
        if ($deleted > 0) {
            Log::info("Traffic retention: deleted {$deleted} daily records older than {$cutoff}");
        }

        $this->info("Aggregation complete. Deleted {$deleted} old daily records.");
        return self::SUCCESS;
    }
}
