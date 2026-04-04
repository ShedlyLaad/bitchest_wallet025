<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CoinbaseAPIService
{
    private const BASE_URL = 'https://api.coinbase.com/v2';
    private const REQUEST_DELAY = 150000;
    private const TIMEOUT = 10;
    
    private const CRYPTO_SYMBOLS = [
        'BTC' => 'BTC',
        'ETH' => 'ETH',
        'XRP' => 'XRP',
        'BCH' => 'BCH',
        'ADA' => 'ADA',
        'LTC' => 'LTC',
        'XLM' => 'XLM',
        'DASH' => 'DASH',
        'XEM' => 'AVAX',
        'MIOTA' => 'AAVE',
    ];
    
    private const SUPPORTED_CRYPTOS = [
        'BTC', 'ETH', 'XRP', 'BCH', 'ADA', 'LTC', 'XLM', 'DASH', 'XEM', 'MIOTA'
    ];
    
    public function isSupported(string $symbol): bool
    {
        return in_array(strtoupper($symbol), self::SUPPORTED_CRYPTOS);
    }
    
    public function getHistoricalPrices(string $symbol, Carbon $startDate, Carbon $endDate): array
    {
        return [];
    }
    
    public function getCryptoData(string $symbol): ?array
    {
        $upperSymbol = strtoupper($symbol);
        
        if (!$this->isSupported($upperSymbol)) {
            return null;
        }
        
        $cryptoSymbol = self::CRYPTO_SYMBOLS[$upperSymbol] ?? $upperSymbol;
        
        try {
            $url = self::BASE_URL . "/prices/{$cryptoSymbol}-EUR/spot";
            
            $response = Http::withOptions(['verify' => false])
                ->timeout(self::TIMEOUT)
                ->get($url);
            
            if ($response->status() === 429) {
                Log::warning("Rate limit Coinbase API for {$symbol}");
                return null;
            }
            
            if ($response->status() === 404 || !$response->ok()) {
                return null;
            }
            
            $data = $response->json();
            
            if (!isset($data['data']['amount'])) {
                return null;
            }
            
            $priceEUR = (float) $data['data']['amount'];
            
            if ($priceEUR <= 0) {
                return null;
            }
            
            return [
                'price' => $priceEUR,
                'change24h' => 0.0,
                'marketCap' => 0.0,
                'volume24h' => 0.0,
            ];
        } catch (\Exception $e) {
            Log::warning("Coinbase API error for {$symbol}: " . $e->getMessage());
            return null;
        }
    }
    
    public function getMultipleCryptoData(array $symbols): array
    {
        $result = [];
        
        $supportedSymbols = array_filter($symbols, function($symbol) {
            return $this->isSupported($symbol);
        });
        
        foreach ($supportedSymbols as $symbol) {
            try {
                $data = $this->getCryptoData($symbol);
                if ($data && isset($data['price']) && $data['price'] > 0) {
                    $result[strtoupper($symbol)] = $data;
                }
            } catch (\Exception $e) {
                Log::warning("Error retrieving {$symbol}: " . $e->getMessage());
            }
            
            if (count($supportedSymbols) > 1) {
                usleep(self::REQUEST_DELAY);
            }
        }
        
        return $result;
    }
}
