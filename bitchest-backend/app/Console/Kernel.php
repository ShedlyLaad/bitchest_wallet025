<?php
namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Services\CryptoService;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // schedule a daily generation (example)
      $schedule->call(function (\App\Services\CotationGeneratorService $service) {
    $service->generateDaily();
})->hourly();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
    }
}
