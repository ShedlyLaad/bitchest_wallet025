<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Création de la table users avec optimisations
     * - Index sur colonnes fréquemment utilisées
     * - Commentaires explicatifs
     * - Types de données optimisés
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id()->comment('Identifiant unique de l\'utilisateur');
            
            // Informations personnelles
            $table->string('name', 255)->comment('Nom complet de l\'utilisateur');
            $table->string('first_name', 100)->nullable()->comment('Prénom');
            $table->string('last_name', 100)->nullable()->comment('Nom de famille');
            
            // Authentification
            $table->string('email', 255)->unique()->comment('Email unique pour connexion');
            $table->string('phone', 20)->nullable()->comment('Numéro de téléphone');
            $table->string('password', 255)->comment('Mot de passe hashé (bcrypt)');
            $table->boolean('must_change_password')->default(false)->comment('Obligation de changer le mot de passe');
            $table->rememberToken()->comment('Token pour "Se souvenir de moi"');
            
            // Rôles et statuts
            $table->enum('role', ['admin', 'client'])
                ->default('client')
                ->comment('Rôle : admin ou client');
            
            $table->enum('status', ['pending', 'pending_validation', 'active', 'blocked'])
                ->default('pending')
                ->comment('Statut du compte : pending, pending_validation, active, blocked');
            
            $table->timestamp('email_verified_at')->nullable()->comment('Date de vérification email');
            
            // Données financières
            $table->decimal('euro_balance', 12, 2)
                ->default(0)
                ->comment('Solde en euros de l\'utilisateur');
            
            // Système de niveaux (gamification)
            $table->integer('level')
                ->default(1)
                ->comment('Niveau de l\'utilisateur (gamification)');
            
            $table->integer('experience_points')
                ->default(0)
                ->comment('Points d\'expérience pour monter de niveau');
            
            // Profil utilisateur
            $table->string('profile_picture', 255)->nullable()->comment('Chemin vers la photo de profil');
            $table->string('profile_banner', 255)->nullable()->comment('Chemin vers la bannière de profil');
            
            $table->timestamps();
            
            // Index pour optimiser les requêtes fréquentes
            $table->index('email', 'idx_users_email')->comment('Index pour recherche par email');
            $table->index('role', 'idx_users_role')->comment('Index pour filtrage par rôle');
            $table->index('status', 'idx_users_status')->comment('Index pour filtrage par statut');
            $table->index(['role', 'status'], 'idx_users_role_status')->comment('Index composite pour requêtes combinées');
            $table->index('created_at', 'idx_users_created_at')->comment('Index pour tri par date de création');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
