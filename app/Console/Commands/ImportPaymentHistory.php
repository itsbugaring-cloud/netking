<?php

namespace App\Console\Commands;

use App\Models\Area;
use App\Models\Customer;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportPaymentHistory extends Command
{
    protected $signature = 'payments:import-history
        {file : Path to NETKING.xlsx}
        {--apply : Actually write to DB (default: dry-run)}
        {--sheet=1 : Sheet index (1=PEMBAYARAN PELANGGAN)}';

    protected $description = 'Import payment history from Excel. Match customer by name + area. DRY-RUN by default.';

    private array $areaMapping = [];

    public function handle(): int
    {
        $file = $this->argument('file');
        $apply = (bool) $this->option('apply');
        $sheetIndex = (int) $this->option('sheet');

        if (!file_exists($file)) {
            $this->error("File not found: {$file}");
            return 1;
        }

        $this->info('═══════════════════════════════════════════');
        $this->info('  Import Payment History');
        $this->info('═══════════════════════════════════════════');
        $this->line('Mode: ' . ($apply ? '⚠️  APPLY (WRITE TO DB)' : '👁️  DRY-RUN (preview only)'));
        $this->newLine();

        $this->info('Loading Excel...');
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getSheet($sheetIndex);
        $this->line("Sheet: {$sheet->getTitle()}, Rows: {$sheet->getHighestRow()}");

        $this->buildAreaMapping();

        // Parse Excel rows
        $excelRows = $this->parseExcelRows($sheet);
        $this->info("Parsed {$excelRows->count()} rows from Excel.");
        $this->newLine();

        // Load customers
        $customers = Customer::whereNotNull('pppoe_user')
            ->where('pppoe_user', '!=', '')
            ->with('area')
            ->get();

        $this->info("DB customers: {$customers->count()}");
        $this->newLine();

        // Match
        $matched = [];
        $noMatch = [];
        $ambiguous = [];

        foreach ($excelRows as $row) {
            $excelName = $this->normName($row['name']);
            $excelArea = $row['area'];

            $dbAreaIds = $this->resolveAreaIds($excelArea);
            if (empty($dbAreaIds)) {
                $noMatch[] = array_merge($row, ['reason' => "Area not mapped: {$excelArea}"]);
                continue;
            }

            $candidates = $customers->filter(function ($c) use ($excelName, $dbAreaIds) {
                if (!in_array($c->area_id, $dbAreaIds)) return false;

                $dbName = $this->normName($c->name);
                $pppoeUser = $this->normName($c->pppoe_user);

                $excelClean = preg_replace('/^(ibu|bu|bapak|pak|teh|mang|ceu|bi|ma|ust)\s+/i', '', $excelName);
                $dbClean = preg_replace('/^(ibu|bu|bapak|pak|teh|mang|ceu|bi|ma|ust)\s+/i', '', $dbName);

                $pppoeCompact = str_replace(['-', '_', ' '], '', $pppoeUser);
                $excelCompact = str_replace(['-', '_', ' ', '/'], '', $excelName);
                $excelCleanCompact = str_replace(['-', '_', ' ', '/'], '', $excelClean);

                if ($dbName === $excelName || $dbName === $excelClean) return true;
                if (strlen($excelName) >= 4 && str_contains($dbName, $excelName)) return true;
                if (strlen($dbName) >= 4 && str_contains($excelName, $dbName)) return true;
                if (strlen($excelCompact) >= 4 && str_contains($pppoeCompact, $excelCompact)) return true;
                if (strlen($excelCleanCompact) >= 4 && str_contains($pppoeCompact, $excelCleanCompact)) return true;
                if ($excelClean && $dbClean && strlen($excelClean) >= 4 && ($dbClean === $excelClean || str_contains($dbClean, $excelClean))) return true;

                return false;
            });

            if ($candidates->count() === 1) {
                $matched[] = [
                    'excel' => $row,
                    'customer' => $candidates->first(),
                ];
            } elseif ($candidates->count() > 1) {
                $ambiguous[] = [
                    'excel' => $row,
                    'candidates' => $candidates->pluck('pppoe_user', 'id')->toArray(),
                ];
            } else {
                $noMatch[] = array_merge($row, ['reason' => 'No customer found']);
            }
        }

        // Report: Matched
        $this->info("═══ MATCHED: " . count($matched) . " ═══");
        $table = [];
        foreach (array_slice($matched, 0, 50) as $m) {
            $c = $m['customer'];
            $e = $m['excel'];
            $table[] = [
                $c->id,
                $c->pppoe_user,
                $c->area?->name ?? '-',
                $e['name'],
                sprintf('%02d/%04d', $e['month'], $e['year']),
                number_format($e['nominal'], 0, ',', '.'),
                $e['rekening'],
                $e['tanggal_bayar'] ?? '-',
            ];
        }
        if (!empty($table)) {
            $this->table(['ID', 'PPPoE', 'DB Area', 'Excel Name', 'Periode', 'Nominal', 'Rekening', 'Tgl Bayar'], $table);
            if (count($matched) > 50) {
                $this->line("  ... and " . (count($matched) - 50) . " more matches.");
            }
        }

        // Report: Ambiguous
        if (!empty($ambiguous)) {
            $this->newLine();
            $this->warn("═══ AMBIGUOUS (multiple candidates): " . count($ambiguous) . " ═══");
            foreach (array_slice($ambiguous, 0, 20) as $a) {
                $this->line("  {$a['excel']['name']} ({$a['excel']['area']}) → " . implode(', ', $a['candidates']));
            }
            if (count($ambiguous) > 20) $this->line("  ... +" . (count($ambiguous) - 20) . " more");
        }

        // Report: No Match
        if (!empty($noMatch)) {
            $this->newLine();
            $this->warn("═══ NO MATCH: " . count($noMatch) . " ═══");
            foreach (array_slice($noMatch, 0, 30) as $nm) {
                $this->line("  {$nm['name']} | {$nm['area']} | {$nm['reason']}");
            }
            if (count($noMatch) > 30) $this->line("  ... +" . (count($noMatch) - 30) . " more");
        }

        // Summary
        $this->newLine();
        $this->info('═══════════════════════════════════════════');
        $this->info('  SUMMARY');
        $this->info('═══════════════════════════════════════════');
        $this->line("  Matched:   " . count($matched));
        $this->line("  Ambiguous: " . count($ambiguous));
        $this->line("  No Match:  " . count($noMatch));
        $this->line("  Will import: " . count($matched) . " payment records");
        $this->info('═══════════════════════════════════════════');

        // Apply
        if ($apply && !empty($matched)) {
            $this->newLine();
            $created = 0;
            foreach ($matched as $m) {
                $c = $m['customer'];
                $e = $m['excel'];

                Payment::create([
                    'customer_id' => $c->id,
                    'periode_bulan' => $e['month'],
                    'periode_tahun' => $e['year'],
                    'jumlah' => $e['nominal'],
                    'metode' => 'transfer',
                    'rekening_tujuan' => $e['rekening'],
                    'status' => 'approved',
                    'approved_at' => $e['tanggal_bayar'],
                    'catatan' => 'Import historis',
                ]);
                $created++;
            }
            $this->info("✓ Created {$created} payment records.");
        } elseif (!$apply) {
            $this->newLine();
            $this->warn('DRY-RUN mode. Jalankan dengan --apply untuk write ke DB.');
        }

        return 0;
    }

    private function parseExcelRows($sheet): \Illuminate\Support\Collection
    {
        $rows = collect();
        $maxRow = $sheet->getHighestRow();

        for ($row = 3; $row <= $maxRow; $row++) {
            $name = trim((string) ($sheet->getCell("B{$row}")->getValue() ?? ''));
            $area = trim((string) ($sheet->getCell("C{$row}")->getValue() ?? ''));
            $monthRaw = $sheet->getCell("D{$row}")->getValue();
            $yearRaw = $sheet->getCell("E{$row}")->getValue();
            $nominalRaw = $sheet->getCell("F{$row}")->getValue();
            $rekening = trim((string) ($sheet->getCell("G{$row}")->getValue() ?? ''));
            $tanggalRaw = $sheet->getCell("H{$row}")->getValue();

            if (!$name || !$area) continue;

            $month = $this->parseMonth($monthRaw);
            $year = $this->parseYear($yearRaw);
            $nominal = $this->parseNominal($nominalRaw);
            $tanggalBayar = $this->parseDate($tanggalRaw);
            $rekening = $this->normalizeRekening($rekening);

            if (!$month || !$year || !$nominal) continue;

            $rows->push([
                'row' => $row,
                'name' => $name,
                'area' => $area,
                'month' => $month,
                'year' => $year,
                'nominal' => $nominal,
                'rekening' => $rekening,
                'tanggal_bayar' => $tanggalBayar,
            ]);
        }

        return $rows;
    }

    private function parseMonth($raw): ?int
    {
        if (!$raw) return null;

        if (is_numeric($raw) && (int) $raw >= 1 && (int) $raw <= 12) {
            return (int) $raw;
        }

        $raw = mb_strtolower(trim((string) $raw));
        $months = [
            'jan' => 1, 'januari' => 1, 'january' => 1,
            'feb' => 2, 'februari' => 2, 'february' => 2,
            'mar' => 3, 'maret' => 3, 'march' => 3,
            'apr' => 4, 'april' => 4,
            'mei' => 5, 'may' => 5,
            'jun' => 6, 'juni' => 6, 'june' => 6,
            'jul' => 7, 'juli' => 7, 'july' => 7,
            'agu' => 8, 'agustus' => 8, 'aug' => 8, 'august' => 8,
            'sep' => 9, 'september' => 9,
            'okt' => 10, 'oktober' => 10, 'oct' => 10, 'october' => 10,
            'nov' => 11, 'november' => 11,
            'des' => 12, 'desember' => 12, 'dec' => 12, 'december' => 12,
        ];

        foreach ($months as $key => $val) {
            if (str_starts_with($raw, $key)) return $val;
        }

        return null;
    }

    private function parseYear($raw): ?int
    {
        if (!$raw) return null;
        $val = (int) preg_replace('/[^0-9]/', '', (string) $raw);
        if ($val >= 2020 && $val <= 2030) return $val;
        if ($val >= 20 && $val <= 30) return 2000 + $val;
        return null;
    }

    private function parseNominal($raw): int
    {
        if (!$raw) return 0;
        $raw = (string) $raw;
        $raw = preg_replace('/[^0-9]/', '', $raw);
        return (int) $raw;
    }

    private function normalizeRekening(string $raw): string
    {
        $raw = mb_strtoupper(trim($raw));

        if (str_contains($raw, 'BRI')) return 'BRI';
        if (str_contains($raw, 'BNI')) return 'BNI';
        if (str_contains($raw, 'MANDIRI')) return 'Mandiri';
        if (str_contains($raw, 'BCA')) return 'BCA';
        if (str_contains($raw, 'QRIS')) return 'QRIS';

        return $raw ?: 'Transfer';
    }

    private function parseDate($raw): ?string
    {
        if (!$raw) return null;

        $raw = trim((string) $raw);

        // Excel serial date number
        if (is_numeric($raw) && (int) $raw > 40000 && (int) $raw < 50000) {
            try {
                $unix = ((int) $raw - 25569) * 86400;
                $date = Carbon::createFromTimestamp($unix);
                if ($date->year >= 2020 && $date->year <= 2030) {
                    return $date->format('Y-m-d');
                }
            } catch (\Exception $e) {
                // fall through
            }
        }

        // Clean up
        $raw = str_replace(['"', "\u{00A0}"], ['', ' '], $raw);
        $raw = preg_replace('/\s+/', ' ', trim($raw));

        // Map Indonesian month names
        $months = [
            'jan' => 'Jan', 'feb' => 'Feb', 'mar' => 'Mar', 'apr' => 'Apr',
            'mei' => 'May', 'jun' => 'Jun', 'jul' => 'Jul', 'agu' => 'Aug',
            'sep' => 'Sep', 'okt' => 'Oct', 'nov' => 'Nov', 'des' => 'Dec',
            'januari' => 'January', 'februari' => 'February', 'maret' => 'March',
            'april' => 'April', 'juni' => 'June', 'juli' => 'July',
            'agustus' => 'August', 'september' => 'September', 'oktober' => 'October',
            'november' => 'November', 'desember' => 'December',
        ];

        $normalized = strtolower($raw);
        foreach ($months as $id => $en) {
            $normalized = str_replace($id, $en, $normalized);
        }
        $normalized = ucwords($normalized);

        $formats = ['d M Y', 'd F Y', 'j M Y', 'j F Y', 'd M y', 'j M y', 'd/m/Y', 'd-m-Y'];

        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $normalized);
                if ($date && $date->year >= 2020 && $date->year <= 2030) {
                    return $date->format('Y-m-d');
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Try numeric serial as last fallback
        if (is_numeric($raw)) {
            try {
                $date = Carbon::createFromTimestamp(((float) $raw - 25569) * 86400);
                if ($date->year >= 2020 && $date->year <= 2030) {
                    return $date->format('Y-m-d');
                }
            } catch (\Exception $e) {
                // ignore
            }
        }

        return null;
    }

    private function buildAreaMapping(): void
    {
        $areas = Area::all();
        $this->areaMapping = [];
        foreach ($areas as $area) {
            $this->areaMapping[$this->normName($area->name)] = $area->id;
        }
    }

    private function resolveAreaIds(string $excelArea): array
    {
        $norm = $this->normName($excelArea);

        $manual = [
            'cikalong wetan' => 'cikalong wetan',
            'cicadas' => 'cicaheum',
            'sumedang' => 'sumedang',
            'tasikmalaya' => 'tasikmalaya',
            'padalarang' => 'padalarang',
            'majalaya' => 'majalaya',
            'pangalengan' => 'pangalengan',
            'kwa batujaya' => 'karawang - batujaya',
            'kwa jamblang' => 'karawang - jamblang',
            'kwa kalangsuria' => 'karawang - kalangsuria',
            'pamengpeuk' => 'garut - pamengpeuk',
            'cisompet' => 'garut - cisompet',
            'cisompet(garsel)' => 'garut - cisompet',
            'bayongbong' => 'garut - bayongbong',
            'santolo' => 'garut - santolo',
            'sukabumi' => 'sukabumi',
            'sukabumi / mangkalaya' => 'sukabumi',
            'situ cileunca' => 'situ cileunca',
            'mekar cangkring' => 'cangkring',
            'tasikmalaya / mangunreja' => 'tasikmalaya - mangunreja',
            'tasikmalaya / cibeureum' => 'tasikmalaya - cibereum',
            'tasikmalaya / indihiang' => 'tasikmalaya - indihiang',
            'tasikmalaya / singaparna' => 'tasikmalaya - singaparna',
            'tasikmalaya / tamansari' => 'tasikmalaya - tamansari',
            'sipur' => 'pangalengan - sipur',
            'cikolotok' => 'pangalengan - sipur',
            'babakan cieurih' => 'pangalengan - sipur',
            'rusun baleendah' => 'baleendah',
            'bojongasih' => 'majalaya',
            'limbangan garut' => 'garut',
            'ciganitri' => 'ciganitri',
            'cimahi' => 'cimahi',
            'subang' => 'subang',
            'margahayu' => 'margahayu',
            'kasepen' => 'kasepen',
            'negla tasikmalaya' => 'tasikmalaya - negla',
            'bojong blokraton' => 'sukabumi',
            'warudoyong' => 'sukabumi',
            'blokraton' => 'sukabumi',
            'kwa mekarjati' => 'karawang - mekarjati',
            'cikutra' => 'cikutra',
            'baleendah' => 'baleendah',
            'gunung datar' => 'sumedang - gunung datar',
            'jatihurip' => 'sumedang - jatihurip',
            'tanjungsari' => 'sumedang - tanjungsari',
            'sukajadi' => 'subang - sukajadi',
        ];

        if (isset($manual[$norm])) {
            $target = $this->normName($manual[$norm]);
            $ids = [];
            foreach ($this->areaMapping as $dbName => $areaId) {
                if ($dbName === $target || str_contains($dbName, $target) || str_contains($target, $dbName)) {
                    $ids[] = $areaId;
                }
            }
            if (!empty($ids)) return array_unique($ids);
        }

        $ids = [];
        foreach ($this->areaMapping as $dbName => $areaId) {
            if ($dbName === $norm || str_contains($dbName, $norm) || str_contains($norm, $dbName)) {
                $ids[] = $areaId;
            }
        }

        return array_unique($ids);
    }

    private function normName(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = preg_replace('/\s+/', ' ', $value);
        $value = str_replace(['(gratis)', '(server)', '(fasum)'], '', $value);
        return trim($value);
    }
}
