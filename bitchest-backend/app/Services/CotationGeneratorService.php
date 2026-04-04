<?php

namespace App\Services;

use App\Models\CryptoCurrency;
use App\Models\CryptoPriceRecord;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Service de génération de cotations pour les cryptomonnaies
 * 
 * Responsabilités :
 * - Génération quotidienne de nouvelles cotations
 * - Calcul des variations de prix basées sur des algorithmes
 * - Récupération de l'historique des prix
 * - Calcul du prix initial d'une cryptomonnaie
 * 
 * Compétences validées : C3, C8
 */
class CotationGeneratorService
{
    private ?CoinbaseAPIService $coinbaseAPIService;

    /**
     * Constructeur du service de génération de cotations
     * 
     * @param CoinbaseAPIService|null $coinbaseAPIService Service API Coinbase (optionnel)
     */
    public function __construct(?CoinbaseAPIService $coinbaseAPIService = null)
    {
        $this->coinbaseAPIService = $coinbaseAPIService;
    }


    /**
     * Génère une nouvelle cotation "aujourd'hui" pour chaque crypto (exécutable cron)
     */
    public function generateDaily(): void
    {
        $cryptos = CryptoCurrency::all();

        foreach ($cryptos as $crypto) {
            $last = CryptoPriceRecord::where('crypto_currency_id', $crypto->id)
                ->latest('recorded_at')
                ->first();

            $base = $last ? (float)$last->price : $this->getFirstCotation($crypto->name ?? $crypto->symbol);
            
            // Applique la variation quotidienne
            $variation = $this->getCotationFor($crypto->name ?? $crypto->symbol);
            $price = $base + $variation;
            $price = max(0.00000001, round($price, 8));

            CryptoPriceRecord::create([
                'crypto_currency_id' => $crypto->id,
                'price' => $price,
                'recorded_at' => now(),
            ]);
        }
    }

    /**
     * Renvoie la valeur de mise sur le marché de la crypto monnaie
     * Basé sur le premier caractère du nom de la crypto
     * 
     * @param string $cryptoname Le nom de la crypto monnaie
     * @return float Prix initial de la crypto
     */
    private function getFirstCotation(string $cryptoname): float
    {
        if (empty($cryptoname)) {
            return rand(1, 100);
        }
        
        return ord(substr($cryptoname, 0, 1)) + rand(0, 10);
    }

    /**
     * Renvoie la variation de cotation de la crypto monnaie sur un jour
     * 
     * @param string $cryptoname Le nom de la crypto monnaie
     * @return float Variation à appliquer au prix actuel (peut être positive ou négative)
     */
    private function getCotationFor(string $cryptoname): float
    {
        if (empty($cryptoname)) {
            return (rand(0, 1) ? 1 : -1) * rand(1, 10) * 0.01;
        }
        
        $direction = (rand(0, 99) > 40) ? 1 : -1;
        $charValue = (rand(0, 99) > 49) 
            ? ord(substr($cryptoname, 0, 1)) 
            : ord(substr($cryptoname, -1));
        $multiplier = rand(1, 10) * 0.01;
        
        return $direction * $charValue * $multiplier;
    }

    /**
     * Retourne l'historique des prix sous forme de collection
     * 
     * @param string $symbol Symbole de la cryptomonnaie
     * @param int $days Nombre de jours d'historique à récupérer (défaut: 7)
     * @return Collection Collection contenant les enregistrements avec 'recorded_at' et 'price'
     */
    public function getHistorical(string $symbol, int $days = 7): Collection
    {
        $crypto = CryptoCurrency::where('symbol', $symbol)->firstOrFail();

        return CryptoPriceRecord::where('crypto_currency_id', $crypto->id)
            ->where('recorded_at', '>=', now()->subDays($days))
            ->orderBy('recorded_at')
            ->get(['recorded_at', 'price']);
    }

    public function getCurrentPrice(string $symbol): ?float
    {
        $crypto = CryptoCurrency::where('symbol', $symbol)->first();
        if (!$crypto) return null;

        return (float) CryptoPriceRecord::where('crypto_currency_id', $crypto->id)
            ->latest('recorded_at')
            ->value('price');
    }
}
