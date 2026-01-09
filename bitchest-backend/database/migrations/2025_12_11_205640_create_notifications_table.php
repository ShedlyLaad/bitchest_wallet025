<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('portfolio_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('crypto_currency_id')->nullable()->constrained()->onDelete('set null');
            
            $table->enum('type', ['profit', 'loss', 'price_alert', 'portfolio_update', 'level_up'])->default('portfolio_update');
            $table->string('title');
            $table->text('message');
            $table->string('crypto_symbol')->nullable();
            
            // Données de la notification
            $table->decimal('gain_loss', 18, 8)->nullable();
            $table->decimal('gain_loss_percent', 10, 2)->nullable();
            $table->decimal('current_price', 18, 8)->nullable();
            $table->decimal('previous_price', 18, 8)->nullable();
            $table->integer('level')->nullable();
            $table->string('level_name')->nullable();
            
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index(['user_id', 'is_read']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};

