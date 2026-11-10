<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Master Data Kemasan (Koper, Keranjang Kecil, Kardus Box, dll.)
        Schema::create('packagings', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable()->unique();
            $table->string('name');
            $table->decimal('cost_price', 14, 2)->default(0); // Harga modal / kulak owner
            $table->decimal('price', 14, 2)->default(0);      // Harga jual tambahan ke konsumen
            $table->string('image_path')->nullable();         // Foto tampak kemasan
            $table->text('description')->nullable();          // Keterangan / ukuran / material kemasan
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Opsi Kemasan yang Ditawarkan Owner pada Setiap Paket (Bisa lebih dari satu pilihan)
        Schema::create('package_packagings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            $table->foreignId('packaging_id')->constrained('packagings')->cascadeOnDelete();
            $table->boolean('is_default')->default(false);
            $table->decimal('custom_price', 14, 2)->nullable(); // Harga khusus kemasan untuk paket ini (jika ada)
            $table->timestamps();
        });

        // 3. Tambahkan Pilihan Kemasan yang Dipilih Konsumen ke CustomerPackage
        Schema::table('customer_packages', function (Blueprint $table) {
            $table->foreignId('packaging_id')->nullable()->constrained('packagings')->nullOnDelete();
            $table->decimal('packaging_price', 14, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('customer_packages', function (Blueprint $table) {
            $table->dropForeign(['packaging_id']);
            $table->dropColumn(['packaging_id', 'packaging_price']);
        });

        Schema::dropIfExists('package_packagings');
        Schema::dropIfExists('packagings');
    }
};

