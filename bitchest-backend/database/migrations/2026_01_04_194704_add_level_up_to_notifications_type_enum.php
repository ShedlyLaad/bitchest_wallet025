<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Modifier l'ENUM pour ajouter 'level_up'
        // MySQL nécessite de modifier l'ENUM en recréant la colonne
        DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM('profit', 'loss', 'price_alert', 'portfolio_update', 'level_up') DEFAULT 'portfolio_update'");
    }

    public function down(): void
    {
        // Retirer 'level_up' de l'ENUM
        DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM('profit', 'loss', 'price_alert', 'portfolio_update') DEFAULT 'portfolio_update'");
    }
};
