<?php
namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Services\CryptoService;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // Generate crypto prices hourly
        $schedule->call(function (\App\Services\CotationGeneratorService $service) {
            $service->generateDaily();
        })->hourly();

        // Check portfolio notifications every 5 minutes
        $schedule->command('notifications:check-portfolio')
            ->everyFiveMinutes()
            ->withoutOverlapping();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
    }
}
