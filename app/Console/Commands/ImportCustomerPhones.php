<?php

namespace App\Console\Commands;

use App\Models\Area;
use App\Models\Customer;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportCustomerPhones extends Command
{
    protected $signature = 'customers:import-phone
        {file : Path to NETKING.xlsx}
        {--apply : Actually write to DB (default: dry-run)}
        {--sheet=0 : Sheet index (0=MASTER)}';

    protected $description = 'Import nomor HP + status (BERHENTI/GRATIS) dari Excel ke customers. Match by nama + area. DRY-RUN by default.';

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
        $this->info('  Import Customer Phones + Status');
        $this->info('═══════════════════════════════════════════');
        $this->line('Mode: ' . ($apply ? '⚠️  APPLY' : '👁️  DRY-RUN'));
        $this->newLine();

        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getSheet($sheetIndex);
        $this->line("Sheet: {$sheet->getTitle()}, Rows: {$sheet->getHighestRow()}");

        $this->buildAreaMapping();

        // Parse Excel
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

        // Match & collect updates
        $phoneUpdates = [];
        $statusUpdates = [];
        $noMatch = [];
        $ambiguous = [];

        foreach ($excelRows as $row) {
            $excelName = $this->normName($row['name']);
            $excelArea = $row['area'];
            $phone = $row['phone'];
            $ket = $row['ket'];

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
                $customer = $candidates->first();

                if ($phone && !$customer->phone) {
                    $phoneUpdates[] = ['customer' => $customer, 'phone' => $phone, 'excel' => $row];
                }

                $ketLower = strtolower(trim($ket));
                if (str_contains($ketLower, 'berhenti') && $customer->status === 'active') {
                    $statusUpdates[] = ['customer' => $customer, 'new_status' => 'suspended', 'reason' => 'BERHENTI BERLANGGANAN', 'excel' => $row];
                } elseif (str_contains($ketLower, 'gratis') && $customer->status === 'active') {
                    // Mark as gratis via package_price = 0 (keep status active, skip billing)
                    $statusUpdates[] = ['customer' => $customer, 'new_status' => '_gratis', 'reason' => 'GRATIS', 'excel' => $row];
                }
            } elseif ($candidates->count() > 1) {
                $ambiguous[] = ['excel' => $row, 'candidates' => $candidates->pluck('pppoe_user', 'id')->toArray()];
            } else {
                $noMatch[] = array_merge($row, ['reason' => 'No customer found']);
            }
        }

        // Report: Phone Updates
        $this->info("═══ PHONE UPDATES: " . count($phoneUpdates) . " ═══");
        $table = [];
        foreach (array_slice($phoneUpdates, 0, 30) as $u) {
            $table[] = [$u['customer']->id, $u['customer']->pppoe_user, $u['customer']->area?->name, $u['phone']];
        }
        if (!empty($table)) {
            $this->table(['ID', 'PPPoE', 'Area', 'Phone'], $table);
            if (count($phoneUpdates) > 30) $this->line("  ... +" . (count($phoneUpdates) - 30) . " more");
        }

        // Report: Status Updates
        $this->newLine();
        $this->info("═══ STATUS UPDATES: " . count($statusUpdates) . " ═══");
        $table = [];
        foreach (array_slice($statusUpdates, 0, 30) as $u) {
            $table[] = [$u['customer']->id, $u['customer']->pppoe_user, $u['customer']->status, $u['new_status'], $u['reason']];
        }
        if (!empty($table)) {
            $this->table(['ID', 'PPPoE', 'Current', 'New', 'Reason'], $table);
            if (count($statusUpdates) > 30) $this->line("  ... +" . (count($statusUpdates) - 30) . " more");
        }

        // Report: Ambiguous
        if (!empty($ambiguous)) {
            $this->newLine();
            $this->warn("═══ AMBIGUOUS: " . count($ambiguous) . " ═══");
            foreach (array_slice($ambiguous, 0, 10) as $a) {
                $this->line("  {$a['excel']['name']} ({$a['excel']['area']}) → " . implode(', ', $a['candidates']));
            }
        }

        // Report: No Match
        if (!empty($noMatch)) {
            $this->newLine();
            $this->warn("═══ NO MATCH: " . count($noMatch) . " ═══");
            foreach (array_slice($noMatch, 0, 20) as $nm) {
                $this->line("  {$nm['name']} | {$nm['area']} | {$nm['reason']}");
            }
            if (count($noMatch) > 20) $this->line("  ... +" . (count($noMatch) - 20) . " more");
        }

        // Summary
        $this->newLine();
        $this->info('═══════════════════════════════════════════');
        $this->line("  Phone to update: " . count($phoneUpdates));
        $this->line("  Status to update: " . count($statusUpdates));
        $this->line("  Ambiguous: " . count($ambiguous));
        $this->line("  No match: " . count($noMatch));
        $this->info('═══════════════════════════════════════════');

        // Apply
        if ($apply) {
            $phoneDone = 0;
            foreach ($phoneUpdates as $u) {
                $u['customer']->update(['phone' => $u['phone']]);
                $phoneDone++;
            }

            $statusDone = 0;
            foreach ($statusUpdates as $u) {
                if ($u['new_status'] === '_gratis') {
                    // Keep status active, set package_price = 0 to skip billing
                    $u['customer']->update(['package_price' => 0]);
                } else {
                    $u['customer']->update(['status' => $u['new_status']]);
                }
                $statusDone++;
            }

            $this->newLine();
            $this->info("✓ Updated {$phoneDone} phones, {$statusDone} statuses.");
        } else {
            $this->newLine();
            $this->warn('DRY-RUN. Jalankan dengan --apply untuk write ke DB.');
        }

        return 0;
    }

    private function parseExcelRows($sheet): \Illuminate\Support\Collection
    {
        $rows = collect();
        $maxRow = $sheet->getHighestRow();

        for ($row = 3; $row <= $maxRow; $row++) {
            $name = trim((string) ($sheet->getCell("B{$row}")->getValue() ?? ''));
            $area = trim((string) ($sheet->getCell("D{$row}")->getValue() ?? ''));
            $phoneRaw = trim((string) ($sheet->getCell("F{$row}")->getValue() ?? ''));
            $ket = trim((string) ($sheet->getCell("I{$row}")->getValue() ?? ''));

            if (!$name || !$area) continue;

            // Clean phone number
            $phone = $this->cleanPhone($phoneRaw);

            $rows->push([
                'row' => $row,
                'name' => $name,
                'area' => $area,
                'phone' => $phone,
                'ket' => $ket,
            ]);
        }

        return $rows;
    }

    private function cleanPhone(string $raw): string
    {
        if (!$raw) return '';

        // Remove all non-digit characters except leading +
        $raw = preg_replace('/[^0-9+]/', '', $raw);

        // Convert +62 to 0
        if (str_starts_with($raw, '+62')) {
            $raw = '0' . substr($raw, 3);
        } elseif (str_starts_with($raw, '62') && strlen($raw) > 10) {
            $raw = '0' . substr($raw, 2);
        }

        // Must start with 0 and be 10-13 digits
        if (!str_starts_with($raw, '0') || strlen($raw) < 10 || strlen($raw) > 14) {
            return '';
        }

        return $raw;
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
