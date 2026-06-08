<?php

namespace App\Console\Commands;

use App\Models\Area;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportBillingDates extends Command
{
    protected $signature = 'import:billing-dates
        {file : Path to NETKING.xlsx}
        {--apply : Actually write billing_start_date to DB (default: dry-run preview)}
        {--sheet=0 : Sheet index to read (0=MASTER)}';

    protected $description = 'Import billing start dates from Excel, matching by customer name + area. DRY-RUN by default.';

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
        $this->info('  Import Billing Dates from Excel');
        $this->info('═══════════════════════════════════════════');
        $this->line('Mode: ' . ($apply ? '⚠️  APPLY (WRITE TO DB)' : '👁️  DRY-RUN (preview only)'));
        $this->newLine();

        // Load Excel
        $this->info('Loading Excel...');
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getSheet($sheetIndex);
        $this->line("Sheet: {$sheet->getTitle()}, Rows: {$sheet->getHighestRow()}");

        // Build area mapping (Excel area name → DB area)
        $this->buildAreaMapping();

        // Parse Excel rows
        $excelRows = $this->parseExcelRows($sheet);
        $this->info("Parsed {$excelRows->count()} rows with name + area from Excel.");
        $this->newLine();

        // Load all customers from DB
        $customers = Customer::whereNotNull('pppoe_user')
            ->where('pppoe_user', '!=', '')
            ->with('area')
            ->get();

        $this->info("DB customers: {$customers->count()}");
        $this->newLine();

        // Matching
        $matched = [];
        $ambiguous = [];
        $noMatch = [];
        $noDate = [];

        foreach ($excelRows as $row) {
            $excelName = $this->normName($row['name']);
            $excelArea = $row['area'];
            $excelDate = $row['date'];
            $excelPrice = $row['price'];

            if (!$excelDate) {
                $noDate[] = $row;
                continue;
            }

            // Find DB area(s) matching this Excel area
            $dbAreaIds = $this->resolveAreaIds($excelArea);

            if (empty($dbAreaIds)) {
                $noMatch[] = array_merge($row, ['reason' => "Area not mapped: {$excelArea}"]);
                continue;
            }

            // Find customers in these areas matching by name
            $candidates = $customers->filter(function ($c) use ($excelName, $dbAreaIds) {
                if (!in_array($c->area_id, $dbAreaIds)) return false;

                // Match: customer name contains excel name, or excel name contains customer name
                $dbName = $this->normName($c->name);
                $pppoeUser = $this->normName($c->pppoe_user);

                return $dbName === $excelName
                    || str_contains($dbName, $excelName)
                    || str_contains($excelName, $dbName)
                    || str_contains($pppoeUser, $excelName);
            });

            if ($candidates->count() === 1) {
                $customer = $candidates->first();
                $matched[] = [
                    'excel' => $row,
                    'customer' => $customer,
                    'date' => $excelDate,
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
        $matchTable = [];
        foreach (array_slice($matched, 0, 50) as $m) {
            $c = $m['customer'];
            $currentDate = $c->billing_start_date ? $c->billing_start_date->format('Y-m-d') : 'NULL';
            $matchTable[] = [
                $c->id,
                $c->pppoe_user,
                $c->name,
                $c->area?->name ?? '-',
                $m['excel']['name'],
                $m['date'],
                $currentDate,
                $currentDate === 'NULL' ? 'NEW' : ($currentDate === $m['date'] ? 'SAME' : 'UPDATE'),
            ];
        }
        if (!empty($matchTable)) {
            $this->table(
                ['ID', 'PPPoE', 'DB Name', 'DB Area', 'Excel Name', 'Excel Date', 'Current Date', 'Action'],
                $matchTable
            );
            if (count($matched) > 50) {
                $this->line("  ... and " . (count($matched) - 50) . " more matches.");
            }
        }

        // Report: Ambiguous
        if (!empty($ambiguous)) {
            $this->newLine();
            $this->warn("═══ AMBIGUOUS (multiple candidates): " . count($ambiguous) . " ═══");
            foreach (array_slice($ambiguous, 0, 20) as $a) {
                $this->line("  Excel: {$a['excel']['name']} ({$a['excel']['area']})");
                foreach ($a['candidates'] as $id => $pppoe) {
                    $this->line("    → DB #{$id}: {$pppoe}");
                }
            }
        }

        // Report: No Match
        if (!empty($noMatch)) {
            $this->newLine();
            $this->warn("═══ NO MATCH: " . count($noMatch) . " ═══");
            foreach (array_slice($noMatch, 0, 30) as $nm) {
                $this->line("  {$nm['name']} | {$nm['area']} | {$nm['date']} — {$nm['reason']}");
            }
            if (count($noMatch) > 30) {
                $this->line("  ... and " . (count($noMatch) - 30) . " more.");
            }
        }

        // Report: No Date
        if (!empty($noDate)) {
            $this->newLine();
            $this->line("═══ NO DATE (skipped): " . count($noDate) . " (PELANGGAN EXISTING / empty date)");
        }

        // Summary
        $this->newLine();
        $this->info('═══════════════════════════════════════════');
        $this->info('  SUMMARY');
        $this->info('═══════════════════════════════════════════');
        $this->line("  Matched:   " . count($matched));
        $this->line("  Ambiguous: " . count($ambiguous));
        $this->line("  No Match:  " . count($noMatch));
        $this->line("  No Date:   " . count($noDate));

        $needUpdate = collect($matched)->filter(fn($m) => !$m['customer']->billing_start_date || $m['customer']->billing_start_date->format('Y-m-d') !== $m['date'])->count();
        $this->line("  Will update billing_start_date: {$needUpdate}");
        $this->info('═══════════════════════════════════════════');

        // Apply
        if ($apply && $needUpdate > 0) {
            $this->newLine();
            $updated = 0;
            foreach ($matched as $m) {
                $c = $m['customer'];
                $newDate = $m['date'];
                if (!$c->billing_start_date || $c->billing_start_date->format('Y-m-d') !== $newDate) {
                    $c->update(['billing_start_date' => $newDate]);
                    $updated++;
                }
            }
            $this->info("✓ Updated {$updated} customers with billing_start_date.");
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

        for ($row = 2; $row <= $maxRow; $row++) {
            $name = trim((string) ($sheet->getCell("C{$row}")->getValue() ?? ''));
            $area = trim((string) ($sheet->getCell("E{$row}")->getValue() ?? ''));
            $dateRaw = $sheet->getCell("F{$row}")->getValue();
            $priceRaw = $sheet->getCell("I{$row}")->getValue();

            if (!$name || !$area) continue;

            $date = $this->parseDate($dateRaw);
            $price = $this->parsePrice($priceRaw);

            $rows->push([
                'row' => $row,
                'name' => $name,
                'area' => $area,
                'date' => $date,
                'price' => $price,
                'date_raw' => $dateRaw,
            ]);
        }

        return $rows;
    }

    private function parseDate($raw): ?string
    {
        if (!$raw) return null;

        $raw = trim((string) $raw);

        // Skip non-date values
        if (str_contains(strtolower($raw), 'existing') || str_contains(strtolower($raw), 'pelanggan') || str_contains(strtolower($raw), 'gratis') || str_contains(strtolower($raw), 'bayar') || str_contains(strtolower($raw), 'belum') || str_contains(strtolower($raw), 'bundling')) {
            return null;
        }

        // Clean up: remove extra quotes and whitespace
        $raw = str_replace(['"', "\u{00A0}"], ['', ' '], $raw);
        $raw = preg_replace('/\s+/', ' ', $raw);
        $raw = trim($raw);

        // Try various date formats
        $formats = [
            'd M Y',      // "02 Feb 2026"
            'd F Y',      // "02 February 2026"
            'j M Y',      // "2 Feb 2026"
            'j F Y',      // "2 February 2026"
            'd M y',
            'j M y',
            'd F y',
            'j F y',
        ];

        // Map Indonesian month names
        $months = [
            'jan' => 'Jan', 'feb' => 'Feb', 'mar' => 'Mar', 'apr' => 'Apr',
            'mei' => 'May', 'jun' => 'Jun', 'jul' => 'Jul', 'agu' => 'Aug',
            'sep' => 'Sep', 'okt' => 'Oct', 'nov' => 'Nov', 'des' => 'Dec',
            'januari' => 'January', 'februari' => 'February', 'maret' => 'March',
            'april' => 'April', 'mei' => 'May', 'juni' => 'June', 'juli' => 'July',
            'agustus' => 'August', 'september' => 'September', 'oktober' => 'October',
            'november' => 'November', 'desember' => 'December',
        ];

        $normalized = strtolower($raw);
        foreach ($months as $id => $en) {
            $normalized = str_replace($id, $en, $normalized);
        }
        $normalized = ucwords($normalized);

        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $normalized);
                if ($date && $date->year >= 2024 && $date->year <= 2027) {
                    return $date->format('Y-m-d');
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Try numeric Excel serial date
        if (is_numeric($raw)) {
            try {
                $date = Carbon::createFromTimestamp(($raw - 25569) * 86400);
                if ($date->year >= 2024 && $date->year <= 2027) {
                    return $date->format('Y-m-d');
                }
            } catch (\Exception $e) {
                // ignore
            }
        }

        return null;
    }

    private function parsePrice($raw): int
    {
        if (!$raw) return 0;
        $raw = (string) $raw;
        $raw = preg_replace('/[^0-9]/', '', $raw);
        return (int) $raw;
    }

    private function buildAreaMapping(): void
    {
        $areas = Area::all();
        $this->areaMapping = [];

        foreach ($areas as $area) {
            $key = $this->normName($area->name);
            $this->areaMapping[$key] = $area->id;
        }
    }

    private function resolveAreaIds(string $excelArea): array
    {
        $norm = $this->normName($excelArea);
        $ids = [];

        foreach ($this->areaMapping as $dbName => $areaId) {
            if ($dbName === $norm || str_contains($dbName, $norm) || str_contains($norm, $dbName)) {
                $ids[] = $areaId;
            }
        }

        // Manual mappings for known mismatches
        $manual = [
            'cikalong wetan' => 'cikalong wetan',
            'cicadas' => 'cicadas',
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
            'tasikmalaya / cibeureum' => 'tasikmalaya - cintaraja',
            'tasikmalaya / indihiang' => 'tasikmalaya - indihiang',
            'tasikmalaya / singaparna' => 'tasikmalaya',
            'tasikmalaya / tamansari' => 'tasikmalaya',
            'sipur' => 'pangalengan - sipur',
            'cikolotok' => 'pangalengan - sipur',
            'babakan cieurih' => 'pangalengan - sipur',
            'rusun baleendah' => 'baleendah',
            'bojongasih' => 'majalaya',
            'limbangan garut' => 'garut',
            'ciganitri' => 'cicadas',
            'cimahi' => 'cimahi',
            'subang' => 'subang',
            'margahayu' => 'margahayu',
            'kasepen' => 'kasepen',
            'negla tasikmalaya' => 'tasikmalaya',
            'bojong blokraton' => 'sukabumi',
            'warudoyong' => 'sukabumi',
            'blokraton' => 'sukabumi',
        ];

        if (empty($ids) && isset($manual[$norm])) {
            $target = $this->normName($manual[$norm]);
            foreach ($this->areaMapping as $dbName => $areaId) {
                if (str_contains($dbName, $target) || str_contains($target, $dbName)) {
                    $ids[] = $areaId;
                }
            }
        }

        return array_unique($ids);
    }

    private function normName(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = preg_replace('/\s+/', ' ', $value);
        // Remove common suffixes/noise
        $value = str_replace(['(gratis)', '(server)', '(fasum)'], '', $value);
        return trim($value);
    }
}
