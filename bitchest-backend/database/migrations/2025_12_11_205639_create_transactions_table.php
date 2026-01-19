<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Création de la table transactions avec optimisations
     * - Index sur colonnes fréquemment utilisées pour calculs
     * - Types de données optimisés pour précision financière
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id()->comment('Identifiant unique de la transaction');
            
            // Relation avec Portfolio
            $table->foreignId('portfolio_id')
                ->comment('Référence au portfolio concerné')
                ->constrained('portfolios')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            
            // Type de transaction
            $table->enum('type', ['buy', 'sell'])
                ->comment('Type de transaction : achat (buy) ou vente (sell)');
            
            // Détails de la transaction
            $table->decimal('quantity', 18, 8)
                ->comment('Quantité de crypto achetée/vendue (précision 8 décimales)');
            
            $table->decimal('price_at_transaction', 18, 8)
                ->comment('Prix de la crypto au moment de la transaction (précision 8 décimales)');
            
            $table->decimal('euro_amount', 18, 2)
                ->comment('Montant en euros de la transaction (précision 2 décimales)');
            
            $table->timestamps();
            
            // Index pour optimiser les requêtes fréquentes
            $table->index('portfolio_id', 'idx_transaction_portfolio')
                ->comment('Index pour recherche par portfolio');
            $table->index('type', 'idx_transaction_type')
                ->comment('Index pour filtrage par type (buy/sell)');
            $table->index('created_at', 'idx_transaction_created_at')
                ->comment('Index pour tri par date de création');
            $table->index(['portfolio_id', 'type'], 'idx_transaction_portfolio_type')
                ->comment('Index composite pour calculs de quantités (somme buy - somme sell)');
            $table->index(['portfolio_id', 'created_at'], 'idx_transaction_portfolio_created')
                ->comment('Index composite pour historique trié par portfolio et date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
