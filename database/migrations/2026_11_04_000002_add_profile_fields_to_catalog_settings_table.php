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
        Schema::table('catalog_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('catalog_settings', 'app_name')) {
                $table->string('app_name')->default('Susi Parsel Lebaran')->after('id');
            }
            if (!Schema::hasColumn('catalog_settings', 'logo_path')) {
                $table->string('logo_path')->nullable()->after('app_name');
            }
            if (!Schema::hasColumn('catalog_settings', 'owner_name')) {
                $table->string('owner_name')->default('Ibu Hj. Susi Susilawati')->after('logo_path');
            }
            if (!Schema::hasColumn('catalog_settings', 'owner_address')) {
                $table->text('owner_address')->nullable()->after('owner_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catalog_settings', function (Blueprint $table) {
            $table->dropColumn(['app_name', 'logo_path', 'owner_name', 'owner_address']);
        });
    }
};

