<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'address')) {
                $table->text('address')->nullable()->after('phone');
            }
        });

        \Illuminate\Support\Facades\DB::table('users')->where('username', 'budi')->update(['address' => 'Kp. Babakan RT 02/RW 04, Paseh']);
        \Illuminate\Support\Facades\DB::table('users')->where('username', 'siti')->update(['address' => 'Kp. Cijambe No. 12, Majalaya']);
        \Illuminate\Support\Facades\DB::table('users')->where('username', 'agen_sukses')->update(['address' => 'Jl. Raya Cipaku No. 45, Paseh']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'address')) {
                $table->dropColumn('address');
            }
        });
    }
};
