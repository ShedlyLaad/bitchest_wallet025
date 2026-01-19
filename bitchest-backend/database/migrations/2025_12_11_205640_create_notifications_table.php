<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Création de la table notifications avec optimisations
     * - Relations avec User, Portfolio (optionnelle), CryptoCurrency (optionnelle)
     * - Index composites pour requêtes fréquentes (notifications non lues, tri par date)
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id()->comment('Identifiant unique de la notification');
            
            // Relation avec User (obligatoire)
            $table->foreignId('user_id')
                ->comment('Utilisateur destinataire de la notification')
                ->constrained('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            
            // Relation avec Portfolio (optionnelle)
            $table->foreignId('portfolio_id')
                ->nullable()
                ->comment('Portfolio concerné (optionnel)')
                ->constrained('portfolios')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            
            // Relation avec CryptoCurrency (optionnelle)
            $table->foreignId('crypto_currency_id')
                ->nullable()
                ->comment('Cryptomonnaie concernée (optionnelle)')
                ->constrained('crypto_currencies')
                ->onDelete('set null') // Garder la notification même si crypto supprimée
                ->onUpdate('cascade');
            
            // Type de notification
            $table->enum('type', [
                'profit',           // Profit réalisé
                'loss',             // Perte réalisée
                'price_alert',      // Alerte de prix
                'portfolio_update', // Mise à jour portfolio
                'level_up'          // Montée de niveau
            ])->default('portfolio_update')
                ->comment('Type de notification');
            
            // Contenu de la notification
            $table->string('title', 255)->comment('Titre de la notification');
            $table->text('message')->comment('Message détaillé de la notification');
            $table->string('crypto_symbol', 10)->nullable()->comment('Symbole de la crypto concernée');
            
            // Données financières (optionnelles)
            $table->decimal('gain_loss', 18, 8)->nullable()
                ->comment('Gain ou perte en euros');
            
            $table->decimal('gain_loss_percent', 10, 2)->nullable()
                ->comment('Gain ou perte en pourcentage');
            
            $table->decimal('current_price', 18, 8)->nullable()
                ->comment('Prix actuel de la crypto');
            
            $table->decimal('previous_price', 18, 8)->nullable()
                ->comment('Prix précédent de la crypto');
            
            // Données de niveau (pour notifications level_up)
            $table->integer('level')->nullable()
                ->comment('Niveau atteint');
            
            $table->string('level_name', 50)->nullable()
                ->comment('Nom du niveau atteint');
            
            // État de lecture
            $table->boolean('is_read')->default(false)
                ->comment('Indique si la notification a été lue');
            
            $table->timestamp('read_at')->nullable()
                ->comment('Date et heure de lecture');
            
            $table->timestamps();
            
            // Index composites pour optimiser les requêtes fréquentes
            $table->index(['user_id', 'is_read'], 'idx_notification_user_read')
                ->comment('Index pour récupérer les notifications non lues par utilisateur');
            $table->index(['user_id', 'created_at'], 'idx_notification_user_created')
                ->comment('Index pour tri par utilisateur et date de création');
            $table->index(['user_id', 'type'], 'idx_notification_user_type')
                ->comment('Index pour filtrage par utilisateur et type');
            $table->index('portfolio_id', 'idx_notification_portfolio')
                ->comment('Index pour recherche par portfolio');
            $table->index('crypto_currency_id', 'idx_notification_crypto')
                ->comment('Index pour recherche par cryptomonnaie');
            $table->index('created_at', 'idx_notification_created_at')
                ->comment('Index pour tri par date de création');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
