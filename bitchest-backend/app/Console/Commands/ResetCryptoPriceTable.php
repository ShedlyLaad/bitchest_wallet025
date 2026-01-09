<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CryptoPriceRecord;
use App\Services\CryptoDataCompressionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Commande pour réinitialiser complètement la table crypto_price_records
 * Utile pour les tests et le développement
 * Fusionne aussi la fonctionnalité de nettoyage des anciennes données
 */
class ResetCryptoPriceTable extends Command
{
    protected $signature = 'crypto:reset-table 
                            {--confirm : Confirmer la suppression sans prompt}
                            {--keep-days= : Garder les X derniers jours au lieu de tout supprimer}';
    protected $description = 'Réinitialise la table crypto_price_records (supprime toutes les données ou garde les X derniers jours)';

    public function handle(CryptoDataCompressionService $compressionService = null)
    {
        $confirm = $this->option('confirm');
        $keepDays = $this->option('keep-days');
        
        // Si keep-days est spécifié, utiliser la logique de nettoyage
        if ($keepDays !== null) {
            $keepDays = (int) $keepDays;
            if ($keepDays <= 0) {
                $this->error("Le nombre de jours doit être supérieur à 0");
                return 1;
            }
            
            if (!$compressionService) {
                $compressionService = app(CryptoDataCompressionService::class);
            }
            
            $this->info("🧹 Nettoyage des données (garde les {$keepDays} derniers jours)...");
            $deleted = $compressionService->cleanOldStaticData($keepDays);
            
            $this->info("✓ {$deleted} enregistrements supprimés");
            $this->info("✓ Cache nettoyé");
            $this->info("\n✅ Nettoyage terminé avec succès!");
            
            return 0;
        }
        
        // Sinon, supprimer toutes les données
        if (!$confirm) {
            if (!$this->confirm('⚠️  Êtes-vous sûr de vouloir supprimer TOUTES les données de crypto_price_records ?', false)) {
                $this->info('Opération annulée.');
                return 0;
            }
        }

        $this->info("🗑️  Suppression de toutes les données...");
        
        $count = CryptoPriceRecord::count();
        DB::table('crypto_price_records')->truncate();
        
        // Nettoyer aussi le cache si le service est disponible
        if ($compressionService) {
            $compressionService->clearCache();
        }
        
        $this->info("✓ {$count} enregistrements supprimés");
        $this->info("✓ Table réinitialisée (auto-increment remis à zéro)");
        $this->info("✓ Cache nettoyé");
        
        Log::info("Table crypto_price_records réinitialisée: {$count} enregistrements supprimés");
        
        $this->info("\n✅ Table réinitialisée avec succès!");
        $this->info("💡 Exécutez 'php artisan db:seed --class=CryptoAndPricesSeeder' pour réinitialiser les données");
        
        return 0;
    }
}

