<?php

namespace Database\Factories;

use App\Models\CryptoCurrency;
use App\Models\PriceHistory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class PriceHistoryFactory extends Factory
{
    protected $model = PriceHistory::class;

    public function definition(): array
    {
        // Générer une date aléatoire dans les 30 derniers jours
        $daysAgo = fake()->numberBetween(0, 30);
        $date = Carbon::now()->subDays($daysAgo)->startOfDay()->addHours(fake()->numberBetween(0, 23))->addMinutes(fake()->numberBetween(0, 59));

        // Utiliser un ID de crypto existant ou créer une nouvelle si aucune n'existe
        $cryptoId = CryptoCurrency::inRandomOrder()->value('id') ?? CryptoCurrency::factory()->create()->id;

        return [
            'crypto_currency_id' => $cryptoId,
            'price' => fake()->randomFloat(8, 0.00000001, 100000),
            'recorded_at' => $date,
        ];
    }

    /**
     * Génère 30 jours d'historique pour une crypto donnée
     * 
     * @param int $cryptoCurrencyId ID de la crypto
     * @param Carbon|null $startDate Date de début (par défaut: il y a 30 jours)
     * @param Carbon|null $endDate Date de fin (par défaut: aujourd'hui)
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function forCrypto(int $cryptoCurrencyId, ?Carbon $startDate = null, ?Carbon $endDate = null)
    {
        $startDate = $startDate ?? Carbon::now()->subDays(30)->startOfDay();
        $endDate = $endDate ?? Carbon::now()->endOfDay();
        
        $basePrice = fake()->randomFloat(8, 100, 50000);
        
        return $this->state(function (array $attributes) use ($cryptoCurrencyId, $startDate, $endDate, $basePrice) {
            $currentDate = $startDate->copy();
            $price = $basePrice;
            $results = [];

            // Générer un prix par jour sur 30 jours
            while ($currentDate->lte($endDate)) {
                // Variation quotidienne de ±5%
                $variation = fake()->randomFloat(8, -0.05, 0.05);
                $price = $price * (1 + $variation);
                $price = max(0.00000001, round($price, 8)); // Prix toujours positif

                $results[] = [
                    'crypto_currency_id' => $cryptoCurrencyId,
                    'price' => $price,
                    'recorded_at' => $currentDate->copy()->addHours(fake()->numberBetween(0, 23))->addMinutes(fake()->numberBetween(0, 59)),
        ];

                $currentDate->addDay();
            }

            return $results[0] ?? $attributes; // Retourne le premier pour la factory, mais on utilisera le seeder pour créer tous les jours
        });
    }
}
