<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SeedTestPayments extends Command
{
    protected $signature = 'test:seed-payments {--clean : Hapus semua data test dulu}';
    protected $description = '[TEST ONLY] Buat data pembayaran pending palsu untuk test UI review.';

    public function handle(): int
    {
        if ($this->option('clean')) {
            $deleted = Payment::where('catatan', 'LIKE', '[TEST]%')->delete();
            $this->info("Hapus {$deleted} payment test.");
            return self::SUCCESS;
        }

        // Ambil 5 customer aktif acak yang berbeda area
        $customers = Customer::with(['area', 'package', 'partner'])
            ->where('status', 'active')
            ->whereNotNull('area_id')
            ->inRandomOrder()
            ->limit(5)
            ->get();

        if ($customers->isEmpty()) {
            $this->error('Tidak ada customer aktif ditemukan.');
            return self::FAILURE;
        }

        // Download & simpan gambar placeholder bukti transfer
        $proofImages = $this->ensureTestImages();

        $scenarios = [
            ['isolated' => true,  'menit' => 5,    'catatan' => 'Sudah ditransfer tadi pagi'],
            ['isolated' => true,  'menit' => 90,   'catatan' => 'Mohon segera di-approve'],
            ['isolated' => false, 'menit' => 180,  'catatan' => ''],
            ['isolated' => false, 'menit' => 1440, 'catatan' => 'Transfer BCA ke BRI sudah'],
            ['isolated' => false, 'menit' => 30,   'catatan' => '[TEST] Tanpa bukti foto'],
        ];

        $created = 0;
        foreach ($customers as $i => $customer) {
            $scenario = $scenarios[$i] ?? $scenarios[0];

            // Tandai customer sebagai terisolir jika scenario butuh
            if ($scenario['isolated'] && !$customer->is_isolated) {
                $customer->update(['is_isolated' => true, 'isolated_at' => now()]);
                $this->line("  Set is_isolated=true → {$customer->name}");
            }

            $buktiFake = ($i < count($proofImages)) ? $proofImages[$i] : null;

            Payment::create([
                'customer_id'   => $customer->id,
                'periode_bulan' => now()->month,
                'periode_tahun' => now()->year,
                'jumlah'        => $customer->package?->price ?? 150000,
                'metode'        => $i % 2 === 0 ? 'transfer' : 'transfer',
                'rekening_tujuan' => collect(['BRI - 123456789', 'BCA - 987654321', 'QRIS Netking'])->random(),
                'bukti_path'    => $buktiFake,
                'status'        => 'pending',
                'catatan'       => '[TEST] ' . $scenario['catatan'],
                'created_at'    => now()->subMinutes($scenario['menit']),
                'updated_at'    => now()->subMinutes($scenario['menit']),
            ]);

            $created++;
            $tag = $scenario['isolated'] ? '🔴 terisolir' : '🟢 normal';
            $this->line("  ✅ {$customer->name} ({$tag}) — {$scenario['menit']} menit lalu");
        }

        $this->newLine();
        $this->info("Berhasil buat {$created} payment test pending.");
        $this->warn("Buka /admin/payments/review untuk melihat hasilnya.");
        $this->warn("Hapus data test setelah selesai: php artisan test:seed-payments --clean");

        return self::SUCCESS;
    }

    private function ensureTestImages(): array
    {
        $dir   = 'bukti-test';
        $paths = [];

        // Gunakan placeholder image dari placehold.co (external URL disimpan lokal)
        $fakeImages = [
            'test-bukti-1.png',
            'test-bukti-2.png',
            'test-bukti-3.png',
            'test-bukti-4.png',
        ];

        foreach ($fakeImages as $filename) {
            $path = "{$dir}/{$filename}";
            if (!Storage::disk('public')->exists($path)) {
                // Buat gambar PNG sederhana via GD (built-in PHP, no dependency)
                $img = imagecreatetruecolor(600, 400);
                $bg  = imagecolorallocate($img, 240, 248, 255);
                $fg  = imagecolorallocate($img, 37, 99, 235);
                $gray = imagecolorallocate($img, 100, 116, 139);
                imagefill($img, 0, 0, $bg);

                // Border
                imagerectangle($img, 1, 1, 598, 398, imagecolorallocate($img, 203, 213, 225));

                // Header bar
                imagefilledrectangle($img, 0, 0, 600, 60, $fg);
                $white = imagecolorallocate($img, 255, 255, 255);
                imagestring($img, 5, 20, 20, 'BUKTI TRANSFER - CONTOH TEST', $white);

                // Bank logo area
                imagefilledrectangle($img, 20, 80, 580, 140, imagecolorallocate($img, 226, 232, 240));
                imagestring($img, 4, 30, 100, 'Bank BRI / BCA / BSI', $gray);

                // Fake transaction details
                imagestring($img, 3, 20, 160, 'Nominal    : Rp 150.000', $fg);
                imagestring($img, 3, 20, 185, 'Tgl Bayar  : ' . now()->format('d/m/Y H:i'), $gray);
                imagestring($img, 3, 20, 210, 'No. Rek    : 1234-5678-9012', $gray);
                imagestring($img, 3, 20, 235, 'Atas Nama  : NETKING INTERNET', $gray);
                imagestring($img, 3, 20, 260, 'Ref        : TRF' . rand(100000, 999999), $gray);

                // Watermark
                $red = imagecolorallocate($img, 220, 38, 38);
                imagestring($img, 2, 180, 360, '[CONTOH TEST - BUKAN BUKTI ASLI]', $red);

                ob_start();
                imagepng($img);
                $imageData = ob_get_clean();
                imagedestroy($img);

                Storage::disk('public')->put($path, $imageData);
            }
            $paths[] = $path;
        }

        return $paths;
    }
}
