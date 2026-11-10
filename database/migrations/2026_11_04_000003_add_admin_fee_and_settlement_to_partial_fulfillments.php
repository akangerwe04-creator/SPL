<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('partial_fulfillments', function (Blueprint $table) {
            $table->decimal('admin_fee', 14, 2)->default(0)->after('total_paid');
            $table->string('settlement_type', 50)->default('goods')->after('admin_fee'); // 'goods' (barang) or 'cash' (tabungan)
            $table->decimal('cash_payout', 14, 2)->default(0)->after('settlement_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partial_fulfillments', function (Blueprint $table) {
            $table->dropColumn(['admin_fee', 'settlement_type', 'cash_payout']);
        });
    }
};

