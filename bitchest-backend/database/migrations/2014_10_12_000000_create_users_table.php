<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('password');
            $table->boolean('must_change_password')->default(false);
            $table->enum('role', ['admin', 'client'])->default('client');
            $table->enum('status', ['pending', 'pending_validation', 'active', 'blocked'])->default('pending');
            $table->timestamp('email_verified_at')->nullable();
            $table->decimal('euro_balance', 12, 2)->default(0);
            $table->integer('level')->default(1);
            $table->integer('experience_points')->default(0);
            $table->string('profile_picture')->nullable();
            $table->string('profile_banner')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
