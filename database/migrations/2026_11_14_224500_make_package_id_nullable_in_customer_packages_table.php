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
        Schema::table('customer_packages', function (Blueprint $table) {
            $table->dropForeign(['package_id']);
        });

        Schema::table('customer_packages', function (Blueprint $table) {
            $table->unsignedBigInteger('package_id')->nullable()->change();
            $table->foreign('package_id')->references('id')->on('packages')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_packages', function (Blueprint $table) {
            $table->dropForeign(['package_id']);
        });

        Schema::table('customer_packages', function (Blueprint $table) {
            $table->unsignedBigInteger('package_id')->nullable(false)->change();
            $table->foreign('package_id')->references('id')->on('packages')->cascadeOnDelete();
        });
    }
};
