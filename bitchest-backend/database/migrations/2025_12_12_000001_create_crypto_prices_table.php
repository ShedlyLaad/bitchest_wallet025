<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('crypto_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crypto_currency_id')->constrained('crypto_currencies')->cascadeOnDelete();
            $table->decimal('price', 18, 8);
            $table->timestamp('recorded_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crypto_prices');
    }
};
