<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Création de la table crypto_price_records avec optimisations
     * - Historique des prix des cryptomonnaies
     * - Index composites pour requêtes temporelles
     * - Précision de 8 décimales pour les prix
     */
    public function up(): void
    {
        Schema::create('crypto_price_records', function (Blueprint $table) {
            $table->id()->comment('Identifiant unique de l\'enregistrement de prix');
            
            // Relation avec CryptoCurrency
            $table->foreignId('crypto_currency_id')
                ->comment('Référence à la cryptomonnaie')
                ->constrained('crypto_currencies')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            
            // Prix enregistré
            $table->decimal('price', 18, 8)
                ->comment('Prix de la crypto à un moment donné (précision 8 décimales)');
            
            // Date d'enregistrement du prix
            $table->timestamp('recorded_at')
                ->comment('Date et heure d\'enregistrement du prix');
            
            $table->timestamps();
            
            // Index composites pour optimiser les requêtes historiques
            $table->index(['crypto_currency_id', 'recorded_at'], 'idx_price_crypto_recorded')
                ->comment('Index composite pour requêtes historiques par crypto et date');
            $table->index('recorded_at', 'idx_price_recorded_at')
                ->comment('Index pour tri par date d\'enregistrement');
            $table->index('crypto_currency_id', 'idx_price_crypto')
                ->comment('Index pour recherche par cryptomonnaie');
            $table->index(['crypto_currency_id', 'recorded_at', 'price'], 'idx_price_crypto_recorded_price')
                ->comment('Index composite pour requêtes complexes avec prix');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crypto_price_records');
    }
};
