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
        Schema::table('packages', function (Blueprint $table) {
            if (!Schema::hasColumn('packages', 'start_date')) {
                $table->date('start_date')->nullable()->after('period_type');
            }
            if (!Schema::hasColumn('packages', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }
        });

        Schema::table('catalog_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('catalog_settings', 'program_start_date')) {
                $table->date('program_start_date')->nullable()->after('program_period');
            }
            if (!Schema::hasColumn('catalog_settings', 'program_end_date')) {
                $table->date('program_end_date')->nullable()->after('program_start_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date']);
        });

        Schema::table('catalog_settings', function (Blueprint $table) {
            $table->dropColumn(['program_start_date', 'program_end_date']);
        });
    }
};

