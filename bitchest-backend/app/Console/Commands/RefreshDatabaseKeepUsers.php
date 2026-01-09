<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

/**
 * Commande pour rafraîchir complètement la base de données
 * en gardant uniquement la table users et ses données
 */
class RefreshDatabaseKeepUsers extends Command
{
    protected $signature = 'db:refresh-keep-users {--seed : Exécuter les seeders après le refresh}';
    protected $description = 'Rafraîchit complètement la base de données en gardant uniquement la table users';

    public function handle()
    {
        $this->info('🔄 Rafraîchissement de la base de données (conservation de la table users)...');
        
        // Désactiver temporairement les foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        try {
            // Liste des tables à supprimer (toutes sauf users et migrations)
            // IMPORTANT: Ordre de suppression pour respecter les foreign keys
            $tablesToDrop = [
                'crypto_price_records',  // Dépend de crypto_currencies
                'transactions',           // Dépend de portfolios
                'portfolios',            // Dépend de users et crypto_currencies
                'notifications',          // Dépend de users, portfolios, crypto_currencies
                'crypto_currencies',      // Table indépendante
                'password_reset_tokens',
                'failed_jobs',
                'personal_access_tokens',
            ];
            
            $this->info('🗑️  Suppression des tables...');
            foreach ($tablesToDrop as $table) {
                if (Schema::hasTable($table)) {
                    DB::statement("DROP TABLE IF EXISTS `{$table}`");
                    $this->line("  ✓ Table {$table} supprimée");
                }
            }
            
            // Réactiver les foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            
            $this->info('✅ Tables supprimées (users conservée)');
            
            // Marquer la migration users comme déjà exécutée si la table existe
            if (Schema::hasTable('users')) {
                $this->info('📝 Conservation de la migration users (table déjà existante)...');
                // Supprimer toutes les entrées de migrations sauf celle de users
                $usersMigration = '2014_10_12_000000_create_users_table';
                DB::table('migrations')->where('migration', '!=', $usersMigration)->delete();
                
                // Si la migration users n'est pas dans la table migrations, l'ajouter
                if (!DB::table('migrations')->where('migration', $usersMigration)->exists()) {
                    DB::table('migrations')->insert([
                        'migration' => $usersMigration,
                        'batch' => 1
                    ]);
                }
            } else {
                // Si users n'existe pas, réinitialiser complètement
                DB::table('migrations')->truncate();
            }
            
            // Exécuter les migrations pour recréer les tables
            $this->info('📦 Exécution des migrations...');
            Artisan::call('migrate', ['--force' => true], $this->getOutput());
            
            // Exécuter les seeders si demandé
            if ($this->option('seed')) {
                $this->info('🌱 Exécution des seeders...');
                Artisan::call('db:seed', [], $this->getOutput());
            }
            
            $this->info("\n✅ Base de données rafraîchie avec succès!");
            $this->info("💡 La table users et ses données ont été conservées");
            
            if (!$this->option('seed')) {
                $this->info("💡 Exécutez 'php artisan db:seed' pour initialiser les données");
            }
            
            return 0;
            
        } catch (\Exception $e) {
            // Réactiver les foreign key checks en cas d'erreur
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            
            $this->error("❌ Erreur: " . $e->getMessage());
            return 1;
        }
    }
}

