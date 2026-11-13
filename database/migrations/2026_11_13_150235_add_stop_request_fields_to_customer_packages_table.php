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
            if (!Schema::hasColumn('customer_packages', 'stop_status')) {
                $table->string('stop_status', 20)->default('none')->after('status')->index()
                    ->comment('none, pending, approved, rejected');
            }
            if (!Schema::hasColumn('customer_packages', 'stop_reason')) {
                $table->text('stop_reason')->nullable()->after('stop_status');
            }
            if (!Schema::hasColumn('customer_packages', 'stop_requested_at')) {
                $table->timestamp('stop_requested_at')->nullable()->after('stop_reason');
            }
            if (!Schema::hasColumn('customer_packages', 'stop_requested_by')) {
                $table->foreignId('stop_requested_by')->nullable()->after('stop_requested_at')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('customer_packages', 'stop_approved_at')) {
                $table->timestamp('stop_approved_at')->nullable()->after('stop_requested_by');
            }
            if (!Schema::hasColumn('customer_packages', 'stop_approved_by')) {
                $table->foreignId('stop_approved_by')->nullable()->after('stop_approved_at')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('customer_packages', 'stop_rejection_reason')) {
                $table->text('stop_rejection_reason')->nullable()->after('stop_approved_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_packages', function (Blueprint $table) {
            if (Schema::hasColumn('customer_packages', 'stop_requested_by')) {
                $table->dropForeign(['stop_requested_by']);
            }
            if (Schema::hasColumn('customer_packages', 'stop_approved_by')) {
                $table->dropForeign(['stop_approved_by']);
            }
            $columnsToDrop = array_filter([
                'stop_status',
                'stop_reason',
                'stop_requested_at',
                'stop_requested_by',
                'stop_approved_at',
                'stop_approved_by',
                'stop_rejection_reason',
            ], fn($col) => Schema::hasColumn('customer_packages', $col));

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
