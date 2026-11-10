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
        // 1. Tambah safe_threshold pada tabel packages
        if (!Schema::hasColumn('packages', 'safe_threshold')) {
            Schema::table('packages', function (Blueprint $table) {
                $table->decimal('safe_threshold', 14, 2)->nullable()->after('target_nominal')
                    ->comment('Titik Aman Full Parsel SPL (Nominal minimal setoran konsumen untuk jaminan full barang parsel)');
            });
        }

        // 2. Tambah status Tutup Buku pada tabel catalog_settings
        if (!Schema::hasColumn('catalog_settings', 'is_book_closed')) {
            Schema::table('catalog_settings', function (Blueprint $table) {
                $table->boolean('is_book_closed')->default(false)->after('bank_account_info');
                $table->timestamp('book_closed_at')->nullable()->after('is_book_closed');
                $table->foreignId('book_closed_by')->nullable()->after('book_closed_at')->constrained('users')->nullOnDelete();
                $table->text('book_closing_notes')->nullable()->after('book_closed_by');
            });
        }

        // 3. Tambah flag is_bonus pada partial_fulfillment_items
        if (!Schema::hasColumn('partial_fulfillment_items', 'is_bonus')) {
            Schema::table('partial_fulfillment_items', function (Blueprint $table) {
                $table->boolean('is_bonus')->default(false)->after('subtotal');
            });
        }

        // 4. Tambah field penunjang keputusan pada customer_packages
        if (!Schema::hasColumn('customer_packages', 'fulfillment_decision')) {
            Schema::table('customer_packages', function (Blueprint $table) {
                $table->string('fulfillment_decision', 50)->nullable()->after('fulfillment_status')
                    ->comment('safe_full, custom_partial, cash_only, pending');
                $table->timestamp('decided_at')->nullable()->after('fulfillment_decision');
                $table->foreignId('decided_by')->nullable()->after('decided_at')->constrained('users')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('packages', 'safe_threshold')) {
            Schema::table('packages', function (Blueprint $table) {
                $table->dropColumn('safe_threshold');
            });
        }

        if (Schema::hasColumn('catalog_settings', 'is_book_closed')) {
            Schema::table('catalog_settings', function (Blueprint $table) {
                $table->dropForeign(['book_closed_by']);
                $table->dropColumn(['is_book_closed', 'book_closed_at', 'book_closed_by', 'book_closing_notes']);
            });
        }

        if (Schema::hasColumn('partial_fulfillment_items', 'is_bonus')) {
            Schema::table('partial_fulfillment_items', function (Blueprint $table) {
                $table->dropColumn('is_bonus');
            });
        }

        if (Schema::hasColumn('customer_packages', 'fulfillment_decision')) {
            Schema::table('customer_packages', function (Blueprint $table) {
                $table->dropForeign(['decided_by']);
                $table->dropColumn(['fulfillment_decision', 'decided_at', 'decided_by']);
            });
        }
    }
};

