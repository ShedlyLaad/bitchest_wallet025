<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CryptoDataCompressionService;

/**
 * Commande pour nettoyer les anciennes données statiques de crypto_price_records
 * Garde uniquement les données des X derniers jours pour optimiser la base de données
 */
class CleanOldCryptoData extends Command
{
    protected $signature = 'crypto:clean-old-data {--days=7 : Nombre de jours à garder}';
    protected $description = 'Nettoie les anciennes données statiques de crypto_price_records (garde les X derniers jours)';

    public function handle(CryptoDataCompressionService $compressionService)
    {
        $keepDays = (int) $this->option('days');
        
        $this->info("Nettoyage des données crypto (garde les {$keepDays} derniers jours)...");
        
        $deleted = $compressionService->cleanOldStaticData($keepDays);
        
        $this->info("✓ {$deleted} enregistrements supprimés");
        $this->info("✓ Cache nettoyé");
        
        return 0;
    }
}

