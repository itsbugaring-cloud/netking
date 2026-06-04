<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckOntStatus extends Command
{
    protected $signature = 'netking:check-ont-status';
    protected $description = 'Check all ONT status and log changes (ACS/WhatsApp features removed)';

    public function handle(): int
    {
        // [REMOVED] ACS/GenieACS and WhatsApp features removed.
        // This command is now a no-op placeholder.
        $this->info('ONT status check skipped — ACS and WhatsApp features have been removed.');
        $this->info('Use OLT-based ONT monitoring instead.');

        return 0;
    }
}
