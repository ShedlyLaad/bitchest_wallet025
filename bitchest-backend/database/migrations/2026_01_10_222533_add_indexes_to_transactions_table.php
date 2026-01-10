<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Index composite pour optimiser les requêtes par portfolio_id et type
            // Cet index optimise les requêtes : WHERE portfolio_id = X AND type = 'buy'
            $table->index(['portfolio_id', 'type'], 'transactions_portfolio_type_idx');
            // Index pour optimiser les requêtes par date (tri rapide)
            $table->index('created_at', 'transactions_created_at_idx');
            // Note: portfolio_id a déjà un index via la foreign key, pas besoin d'en créer un autre
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('transactions_portfolio_type_idx');
            $table->dropIndex('transactions_created_at_idx');
        });
    }
};
