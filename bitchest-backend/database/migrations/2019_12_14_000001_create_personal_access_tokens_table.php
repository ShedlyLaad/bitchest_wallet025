<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Création de la table personal_access_tokens pour Laravel Sanctum
     * REQUIS pour l'authentification API avec tokens
     * Utilisé par HasApiTokens dans le modèle User
     */
    public function up(): void
    {
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id()->comment('Identifiant unique du token');
            
            // tokenable_type + tokenable_id (morphs() ne retourne rien : pas de ->comment())
            $table->morphs('tokenable');
            
            $table->string('name', 255)->comment('Nom du token (ex: auth-token)');
            $table->string('token', 64)->unique()->comment('Token d\'authentification (hashé)');
            $table->text('abilities')->nullable()->comment('Abilités du token (permissions)');
            
            // Gestion de l'expiration et de l'utilisation
            $table->timestamp('last_used_at')->nullable()
                ->comment('Date de dernière utilisation du token');
            $table->timestamp('expires_at')->nullable()
                ->comment('Date d\'expiration du token');
            
            $table->timestamps();
            
            // Index (pas de ->comment() sur index() selon la version Laravel)
            $table->index(['tokenable_type', 'tokenable_id'], 'idx_tokens_tokenable');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};
