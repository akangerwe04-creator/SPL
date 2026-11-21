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
        if (!Schema::hasTable('period_archives')) {
            Schema::create('period_archives', function (Blueprint $table) {
                $table->id();
                $table->string('period_name');
                $table->date('program_start_date')->nullable();
                $table->date('program_end_date')->nullable();
                $table->timestamp('book_closed_at')->nullable();
                $table->string('book_closed_by_name')->nullable();
                $table->integer('total_agents')->default(0);
                $table->integer('total_items')->default(0);
                $table->integer('total_packagings')->default(0);
                $table->integer('total_packages')->default(0);
                $table->integer('total_customers')->default(0);
                $table->integer('total_orders')->default(0);
                $table->decimal('total_target_nominal', 15, 2)->default(0);
                $table->decimal('total_paid_nominal', 15, 2)->default(0);
                $table->string('archive_filename')->nullable();
                $table->string('archive_file_path')->nullable();
                $table->unsignedBigInteger('archive_file_size')->default(0);
                $table->string('archived_by_name')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('period_archives');
    }
};
