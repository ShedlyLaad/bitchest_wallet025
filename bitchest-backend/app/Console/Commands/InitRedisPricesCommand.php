<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RedisPriceService;
use Illuminate\Support\Facades\Log;

/**
 * Commande pour initialiser Redis depuis la base de données
 * Utile après migration ou redémarrage
 */
class InitRedisPricesCommand extends Command
{
    protected $signature = 'redis:init 
                            {--force : Forcer la réinitialisation même si Redis contient déjà des données}';
    
    protected $description = 'Initialise les prix crypto dans Redis depuis la base de données';

    public function handle(RedisPriceService $redisService): int
    {
        $this->info('🚀 Initialisation des prix dans Redis...');

        try {
            // Vérifier Redis
            if (!$redisService->isAvailable()) {
                $this->error('❌ Redis n\'est pas disponible!');
                $this->warn('   Démarrez Redis avec: docker-compose up -d redis');
                return 1;
            }

            // Vérifier si Redis contient déjà des données
            $existingPrices = $redisService->getAllPrices();
            
            if ($existingPrices->isNotEmpty() && !$this->option('force')) {
                $this->warn('⚠️  Redis contient déjà ' . $existingPrices->count() . ' cryptos.');
                $this->warn('   Utilisez --force pour forcer la réinitialisation.');
                return 0;
            }

            // Vider Redis si force
            if ($this->option('force')) {
                $this->info('🗑️  Vidage de Redis...');
                $redisService->clearAll();
            }

            // Initialiser depuis DB
            $this->info('📊 Initialisation depuis la base de données...');
            $count = $redisService->initializeFromDB();

            if ($count > 0) {
                $this->info("✅ Redis initialisé avec {$count} cryptos!");
                
                // Vérifier
                $prices = $redisService->getAllPrices();
                $this->info("✅ Vérification: {$prices->count()} cryptos disponibles dans Redis");
                
                return 0;
            } else {
                $this->warn('⚠️  Aucune crypto initialisée. Vérifiez que la base de données contient des cryptos.');
                return 1;
            }

        } catch (\Exception $e) {
            $this->error('❌ Erreur: ' . $e->getMessage());
            Log::error('Erreur InitRedisPricesCommand', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
}
