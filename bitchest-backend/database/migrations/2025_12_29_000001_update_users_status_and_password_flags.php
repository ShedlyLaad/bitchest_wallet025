<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'must_change_password')) {
                $table->boolean('must_change_password')
                    ->default(false)
                    ->after('password');
            }
        });

        DB::statement("ALTER TABLE users MODIFY status ENUM('pending','pending_validation','active','blocked') DEFAULT 'pending'");
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'must_change_password')) {
                $table->dropColumn('must_change_password');
            }
        });

        DB::statement("ALTER TABLE users MODIFY status ENUM('pending','active') DEFAULT 'pending'");
    }
};

