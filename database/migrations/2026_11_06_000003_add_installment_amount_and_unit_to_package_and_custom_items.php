<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('package_items', function (Blueprint $table) {
            $table->string('unit')->nullable()->after('quantity');
            $table->decimal('installment_amount', 14, 2)->default(0)->after('unit');
        });

        Schema::table('customer_custom_items', function (Blueprint $table) {
            $table->string('unit')->nullable()->after('quantity');
            $table->decimal('installment_amount', 14, 2)->default(0)->after('unit');
        });
    }

    public function down(): void
    {
        Schema::table('package_items', function (Blueprint $table) {
            $table->dropColumn(['unit', 'installment_amount']);
        });

        Schema::table('customer_custom_items', function (Blueprint $table) {
            $table->dropColumn(['unit', 'installment_amount']);
        });
    }
};

