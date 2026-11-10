<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('plain_password')->nullable()->after('password');
        });

        // Set default plain password untuk akun agen yang sudah ada
        DB::table('users')
            ->where('role', 'agent')
            ->whereNull('plain_password')
            ->update(['plain_password' => 'password']);

        // Set default plain password untuk owner & admin bawaan
        DB::table('users')
            ->where('username', 'owner')
            ->whereNull('plain_password')
            ->update(['plain_password' => 'ownerspl']);

        DB::table('users')
            ->where('username', 'admin')
            ->whereNull('plain_password')
            ->update(['plain_password' => 'admin123']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('plain_password');
        });
    }
};

