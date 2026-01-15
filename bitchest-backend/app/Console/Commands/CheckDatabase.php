<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Redis;
use App\Models\CryptoCurrency;
use App\Models\User;

class CheckDatabase extends Command
{
    protected $signature = 'db:check';
    protected $description = 'Vérifie l\'état de la base de données et des tables';

    public function handle()
    {
        $this->info('=== DIAGNOSTIC BASE DE DONNÉES ===');
        $this->newLine();

        // 1. Test de connexion
        $this->info('1. Test de connexion à la base de données...');
        try {
            DB::connection()->getPdo();
            $this->info('   ✓ Connexion réussie');
            $this->info('   Base de données: ' . DB::connection()->getDatabaseName());
        } catch (\Exception $e) {
            $this->error('   ✗ ERREUR: ' . $e->getMessage());
            $this->newLine();
            $this->warn('Vérifiez votre fichier .env:');
            $this->line('   DB_CONNECTION=mysql');
            $this->line('   DB_HOST=127.0.0.1');
            $this->line('   DB_PORT=3306');
            $this->line('   DB_DATABASE=votre_base');
            $this->line('   DB_USERNAME=votre_user');
            $this->line('   DB_PASSWORD=votre_password');
            return 1;
        }

        // 2. Vérifier les tables
        $this->newLine();
        $this->info('2. Vérification des tables...');
        $requiredTables = [
            'users',
            'crypto_currencies',
            'portfolios',
            'transactions',
            'personal_access_tokens',
            'notifications',
            'migrations'
        ];
        $missingTables = [];

        foreach ($requiredTables as $table) {
            try {
                if (Schema::hasTable($table)) {
                    $count = DB::table($table)->count();
                    $this->info("   ✓ Table '{$table}' existe ({$count} enregistrements)");
                } else {
                    $this->error("   ✗ Table '{$table}' MANQUANTE");
                    $missingTables[] = $table;
                }
            } catch (\Exception $e) {
                $this->error("   ✗ Erreur lors de la vérification de '{$table}': " . $e->getMessage());
                $missingTables[] = $table;
            }
        }

        if (!empty($missingTables)) {
            $this->newLine();
            $this->warn('⚠️  Tables manquantes détectées. Exécutez les migrations:');
            $this->line('   php artisan migrate');
            $this->line('   php artisan migrate:fresh --seed (pour réinitialiser)');
        }

        // 3. Vérifier les cryptos
        $this->newLine();
        $this->info('3. Vérification des cryptomonnaies...');
        try {
            $cryptoCount = CryptoCurrency::count();
            if ($cryptoCount > 0) {
                $this->info("   ✓ {$cryptoCount} cryptomonnaies trouvées");
                $activeCount = CryptoCurrency::where('is_active', true)->count();
                $this->info("   ✓ {$activeCount} cryptomonnaies actives");
            } else {
                $this->warn('   ⚠️  Aucune cryptomonnaie trouvée. Exécutez le seeder:');
                $this->line('      php artisan db:seed --class=CryptoAndPricesSeeder');
            }
        } catch (\Exception $e) {
            $this->error('   ✗ Erreur: ' . $e->getMessage());
        }

        // 4. Vérifier les utilisateurs
        $this->newLine();
        $this->info('4. Vérification des utilisateurs...');
        try {
            $userCount = User::count();
            $this->info("   ✓ {$userCount} utilisateurs trouvés");
        } catch (\Exception $e) {
            $this->error('   ✗ Erreur: ' . $e->getMessage());
        }

        // 5. Vérifier Redis
        $this->newLine();
        $this->info('5. Test de connexion Redis...');
        try {
            Redis::connection()->ping();
            $this->info('   ✓ Redis connecté');
        } catch (\Exception $e) {
            $this->warn('   ⚠️  Redis non disponible: ' . $e->getMessage());
            $this->line('   Le cache fonctionnera avec le driver \'file\' par défaut');
        }

        $this->newLine();
        $this->info('=== DIAGNOSTIC TERMINÉ ===');

        if (empty($missingTables)) {
            $this->newLine();
            $this->info('✓ Toutes les tables sont présentes. La base de données semble correcte.');
            return 0;
        } else {
            $this->newLine();
            $this->warn('⚠️  Des problèmes ont été détectés. Corrigez-les avant de continuer.');
            return 1;
        }
    }
}
