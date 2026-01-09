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
        Schema::create('crypto_price_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crypto_currency_id')
                ->constrained('crypto_currencies')
                ->onDelete('cascade');
            $table->decimal('price', 18, 8); // Prix avec 8 décimales pour précision
            $table->timestamp('recorded_at'); // Date d'enregistrement du prix
            $table->timestamps(); // created_at et updated_at
            
            // Index pour optimiser les requêtes
            $table->index(['crypto_currency_id', 'recorded_at']);
            $table->index('recorded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crypto_price_records');
    }
};

