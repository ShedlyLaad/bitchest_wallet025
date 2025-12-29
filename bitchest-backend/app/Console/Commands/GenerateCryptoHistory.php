<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CotationGeneratorService;

class GenerateCryptoHistory extends Command
{
    protected $signature = 'crypto:generate-history {days=30} {--force=false}';
    protected $description = 'Generate crypto price history for all supported cryptos';

    private CotationGeneratorService $service;

    public function __construct(CotationGeneratorService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function handle()
    {
        $days = (int)$this->argument('days');
        $force = filter_var($this->option('force'), FILTER_VALIDATE_BOOLEAN);

        $this->info("Generating {$days} days of history (force={$force})...");
        $this->service->generateHistory($days, $force);
        $this->info("Done.");
        return 0;
    }
}
