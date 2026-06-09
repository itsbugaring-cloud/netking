<?php

namespace App\Console\Commands;

use App\Models\Package;
use Illuminate\Console\Command;

class UpdatePackagePrices extends Command
{
    protected $signature = 'packages:update-prices
        {--apply : Terapkan perubahan (default: dry-run)}';

    protected $description = 'Update package prices berdasarkan speed standar (6M=100rb, 8M=125rb, 10M=150rb, 15M=175rb, 20M=200rb)';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        $priceMap = [
            6 => 100000,
            7 => 100000,  // round down
            8 => 125000,
            9 => 125000,  // round to nearest
            10 => 150000,
            15 => 175000,
            20 => 200000,
            22 => 200000, // 20Mbps variants
        ];

        $this->info('=== Update Package Prices ===');
        $this->line('Mode: ' . ($apply ? 'APPLY' : 'DRY-RUN'));
        $this->newLine();

        // Only update packages with price = 0
        $packages = Package::where('price', 0)
            ->orWhereNull('price')
            ->orderBy('area_id')
            ->orderBy('speed_down')
            ->get();

        if ($packages->isEmpty()) {
            $this->info('Semua packages sudah punya harga.');
            return self::SUCCESS;
        }

        $this->line("Packages tanpa harga: {$packages->count()}");
        $this->newLine();

        $updated = 0;
        $noMatch = 0;

        foreach ($packages as $pkg) {
            $speed = (int) $pkg->speed_down;
            $price = $priceMap[$speed] ?? null;

            if ($price === null) {
                // Try nearest
                $closest = collect(array_keys($priceMap))
                    ->sortBy(fn($s) => abs($s - $speed))
                    ->first();
                $price = $closest ? $priceMap[$closest] : null;
            }

            if ($price && $speed > 0) {
                $areaName = $pkg->area?->name ?? "Area #{$pkg->area_id}";
                $this->line("  {$areaName} | {$pkg->name} ({$speed}M) → Rp " . number_format($price, 0, ',', '.'));

                if ($apply) {
                    $pkg->update(['price' => $price]);
                }
                $updated++;
            } else {
                $areaName = $pkg->area?->name ?? "Area #{$pkg->area_id}";
                $this->warn("  {$areaName} | {$pkg->name} (speed={$speed}M) → NO MATCH (skip)");
                $noMatch++;
            }
        }

        $this->newLine();
        $this->info("Updated: {$updated} | No match: {$noMatch}");

        if (!$apply) {
            $this->warn('DRY-RUN — jalankan dengan --apply untuk menerapkan.');
        }

        return self::SUCCESS;
    }
}
