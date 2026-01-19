<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Création de la table crypto_currencies avec optimisations
     */
    public function up(): void
    {
        Schema::create('crypto_currencies', function (Blueprint $table) {
            $table->id()->comment('Identifiant unique de la cryptomonnaie');
            
            $table->string('name', 100)->comment('Nom complet de la cryptomonnaie (ex: Bitcoin)');
            $table->string('symbol', 10)
                ->unique()
                ->comment('Symbole unique de la cryptomonnaie (ex: BTC, ETH)');
            
            $table->boolean('is_active')
                ->default(true)
                ->comment('Indique si la crypto est active et tradable');
            
            $table->timestamps();
            
            // Index pour optimiser les recherches
            $table->index('symbol', 'idx_crypto_symbol')->comment('Index pour recherche rapide par symbole');
            $table->index('is_active', 'idx_crypto_is_active')->comment('Index pour filtrage des cryptos actives');
            $table->index(['is_active', 'symbol'], 'idx_crypto_active_symbol')->comment('Index composite pour requêtes fréquentes');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crypto_currencies');
    }
};
