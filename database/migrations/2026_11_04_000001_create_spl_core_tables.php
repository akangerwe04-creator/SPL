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
        // 1. Master Barang
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->nullable();
            $table->string('name');
            $table->decimal('unit_price', 14, 2)->default(0); // Harga ecer / nilai kasir mini
            $table->string('base_unit')->default('pcs'); // pcs, botol, kaleng, bungkus
            $table->string('wholesale_unit')->default('dus'); // dus, karton, bal
            $table->decimal('conversion_rate', 10, 2)->default(24); // misal 1 dus = 24 pcs
            $table->decimal('cost_price', 14, 2)->default(0); // Estimasi modal beli grosir per base_unit
            $table->string('category')->nullable(); // Sirup, Biskuit, Minuman, Sembako
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Master Paket
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->nullable();
            $table->string('name');
            $table->enum('type', ['parsel', 'tabungan', 'ecer'])->default('parsel');
            $table->decimal('target_nominal', 14, 2)->default(0);
            $table->integer('installment_count')->default(30); // misal 30 minggu
            $table->decimal('installment_amount', 14, 2)->default(0); // misal 25.000 / minggu
            $table->string('period_type')->default('mingguan');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Paket Items (Isi Paket & Bonus Tabungan)
        Schema::create('package_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->boolean('is_bonus')->default(false); // True jika bonus tabungan
            $table->timestamps();
        });

        // 4. Data Pelanggan Agen
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('users')->cascadeOnDelete();
            $table->string('code')->nullable();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 5. Keranjang Rekonsiliasi Kas Agen vs Owner (Batching)
        Schema::create('agent_deposits', function (Blueprint $table) {
            $table->id();
            $table->string('deposit_number')->unique();
            $table->foreignId('agent_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->enum('status', ['pending', 'submitted', 'approved', 'rejected'])->default('pending');
            $table->text('agent_notes')->nullable();
            $table->text('owner_notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 6. Paket yang diambil Konsumen
        Schema::create('customer_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            $table->foreignId('agent_id')->constrained('users')->cascadeOnDelete();
            $table->date('start_date')->nullable();
            $table->decimal('total_target', 14, 2)->default(0);
            $table->decimal('total_paid', 14, 2)->default(0);
            $table->enum('status', ['active', 'completed', 'canceled'])->default('active');
            
            // Rules Tabungan:
            $table->boolean('is_bonus_forfeited')->default(false); // Hangus jika bolong 3 minggu berturut-turut
            $table->text('forfeit_reason')->nullable();
            $table->integer('consecutive_unpaid_weeks')->default(0);
            
            // Aturan Pembatalan (10% potongan)
            $table->decimal('cancellation_fee', 14, 2)->default(0);
            $table->decimal('refund_amount', 14, 2)->default(0);
            
            // Penyesuaian Parsel / Fulfillment
            $table->enum('fulfillment_status', ['pending', 'full', 'partial', 'refund'])->default('pending');
            $table->text('fulfillment_notes')->nullable();
            $table->timestamps();
        });

        // 7. Pilihan Barang Rakitan Khusus Paket Ecer Dinamis
        Schema::create('customer_custom_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_package_id')->constrained('customer_packages')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->timestamps();
        });

        // 8. Jadwal Ceklis Pembayaran (Smart Checklist)
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_package_id')->constrained('customer_packages')->cascadeOnDelete();
            $table->foreignId('agent_deposit_id')->nullable()->constrained('agent_deposits')->nullOnDelete();
            $table->foreignId('agent_id')->constrained('users')->cascadeOnDelete();
            $table->integer('period_number'); // Minggu ke-1..30
            $table->decimal('amount', 14, 2)->default(0);
            $table->boolean('is_paid')->default(false);
            $table->timestamp('paid_at')->nullable();
            $table->boolean('is_locked')->default(false); // Terkunci setelah disahkan owner
            $table->enum('status', ['unpaid', 'pending', 'verified'])->default('unpaid');
            $table->timestamps();
        });

        // 9. Penyesuaian Parsel / Partial Fulfillment (Mini-POS)
        Schema::create('partial_fulfillments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_package_id')->constrained('customer_packages')->cascadeOnDelete();
            $table->decimal('total_paid', 14, 2)->default(0);
            $table->decimal('total_items_value', 14, 2)->default(0);
            $table->decimal('remaining_refund', 14, 2)->default(0);
            $table->foreignId('adjusted_by')->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 10. Detail Barang Pengganti Partial Fulfillment
        Schema::create('partial_fulfillment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partial_fulfillment_id')->constrained('partial_fulfillments')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->timestamps();
        });

        // 11. Feed Sosial Media Internal (Instagram Mode)
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('caption')->nullable();
            $table->string('image_path');
            $table->integer('compressed_size_kb')->default(0);
            $table->integer('likes_count')->default(0);
            $table->timestamps();
        });

        // 12. Komentar Feed Sosial
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('content');
            $table->timestamps();
        });

        // 13. Generator Pengaturan Cetak Brosur / Katalog & Profil Usaha
        Schema::create('catalog_settings', function (Blueprint $table) {
            $table->id();
            $table->string('app_name')->default('Susi Parsel Lebaran');
            $table->string('logo_path')->nullable();
            $table->string('owner_name')->default('Ibu Hj. Susi Susilawati');
            $table->text('owner_address')->nullable();
            $table->string('title')->default('KATALOG SUSI PARSEL & TABUNGAN LEBARAN');
            $table->string('program_period')->default('Periode Musim Lebaran 2026 / 1447 H (30 Minggu Tagihan)');
            $table->text('terms_and_conditions')->nullable();
            $table->string('contact_person')->default('0812-3456-7890 (Ibu Susi - Owner SPL)');
            $table->string('bank_account_info')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_settings');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('partial_fulfillment_items');
        Schema::dropIfExists('partial_fulfillments');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('customer_custom_items');
        Schema::dropIfExists('customer_packages');
        Schema::dropIfExists('agent_deposits');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('package_items');
        Schema::dropIfExists('packages');
        Schema::dropIfExists('items');
    }
};

