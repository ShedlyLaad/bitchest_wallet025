<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Création de la table portfolios
     * OPTIMISATION : Index composites pour requêtes fréquentes
     * Note : Un utilisateur peut avoir plusieurs portfolios (un par crypto)
     * Relation 1:N User-Portfolio (un user a plusieurs portfolios, un par crypto)
     */
    public function up(): void
    {
        Schema::create('portfolios', function (Blueprint $table) {
            $table->id()->comment('Identifiant unique du portfolio');
            
            // Relation N:1 avec User (un user peut avoir plusieurs portfolios, un par crypto)
            $table->foreignId('user_id')
                ->comment('Référence à l\'utilisateur (relation N:1)')
                ->constrained('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            
            // Relation N:1 avec CryptoCurrency
            $table->foreignId('crypto_currency_id')
                ->comment('Référence à la cryptomonnaie')
                ->constrained('crypto_currencies')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            
            // Valeur totale investie en crypto (en euros)
            $table->decimal('total_crypto_value', 18, 8)
                ->default(0)
                ->comment('Valeur totale investie pour cette crypto dans le portfolio');
            
            $table->timestamps();
            
            // Index composites pour optimiser les requêtes fréquentes
            $table->index(['user_id', 'crypto_currency_id'], 'idx_portfolio_user_crypto')
                ->comment('Index composite pour recherche par user et crypto');
            $table->index('user_id', 'idx_portfolio_user')
                ->comment('Index pour recherche par utilisateur');
            $table->index('crypto_currency_id', 'idx_portfolio_crypto')
                ->comment('Index pour recherche par cryptomonnaie');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolios');
    }
};
