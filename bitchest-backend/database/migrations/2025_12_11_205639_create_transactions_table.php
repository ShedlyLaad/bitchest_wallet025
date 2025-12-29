<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('portfolio_id')->constrained()->onDelete('cascade');

            $table->enum('type', ['buy', 'sell']);
            $table->decimal('quantity', 18, 8);
            $table->decimal('price_at_transaction', 18, 8);
            $table->decimal('euro_amount', 18, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
