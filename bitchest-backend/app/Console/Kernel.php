<?php
namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Services\CryptoService;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // Update crypto prices from Coinbase API every 24 hours at midnight.
        // Concurrency (vs. a manual run or an Admin "Approve" click) is
        // guarded inside CryptoPriceUpdateService itself via a cache lock,
        // so no extra flag is needed here.
        $schedule->command('crypto:update-prices')
            ->daily()
            ->at('00:00')
            ->appendOutputTo(storage_path('logs/crypto-update.log'));

        // Generate crypto prices hourly (for crypto_price_records historique)
        $schedule->call(function (\App\Services\CotationGeneratorService $service) {
            $service->generateDaily();
        })->hourly();

        // Check portfolio notifications every 5 minutes
        $schedule->command('notifications:check-portfolio')
            ->everyFiveMinutes()
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }
}
