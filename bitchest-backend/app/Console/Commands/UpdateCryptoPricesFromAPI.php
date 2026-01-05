<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CryptoCurrency;
use App\Models\CryptoPriceRecord;
use App\Services\CoinbaseAPIService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UpdateCryptoPricesFromAPI extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crypto:update-prices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update crypto prices from Coinbase API (runs every 24h)';

    /**
     * Execute the console command.
     */
    public function handle(CoinbaseAPIService $coinbaseAPIService)
    {
        $this->info('Starting crypto prices update from Coinbase API...');
        
        $cryptos = CryptoCurrency::where('is_active', true)->get();
        
        if ($cryptos->isEmpty()) {
            $this->warn('No active cryptocurrencies found.');
            return 0;
        }

        $symbols = $cryptos->pluck('symbol')->toArray();
        $this->info('Fetching prices for: ' . implode(', ', $symbols));

        // Récupérer tous les prix depuis Coinbase
        $liveData = $coinbaseAPIService->getMultipleCryptoData($symbols);

        $updated = 0;
        $failed = 0;
        $now = Carbon::now();

        foreach ($cryptos as $crypto) {
            $symbol = strtoupper($crypto->symbol);
            $apiData = $liveData[$symbol] ?? null;

            if ($apiData && isset($apiData['price']) && $apiData['price'] > 0) {
                // Enregistrer dans crypto_price_records (table unifiée)
                CryptoPriceRecord::create([
                    'crypto_currency_id' => $crypto->id,
                    'price' => $apiData['price'],
                    'recorded_at' => $now,
                ]);

                $this->info("✓ {$crypto->symbol}: {$apiData['price']} EUR (24h: {$apiData['change24h']}%)");
                $updated++;
            } else {
                $this->warn("✗ {$crypto->symbol}: Failed to fetch price from API");
                $failed++;
                Log::warning("Failed to update price for {$crypto->symbol} from Coinbase API");
            }
        }

        $this->info("\nUpdate completed: {$updated} updated, {$failed} failed");
        
        return 0;
    }
}

