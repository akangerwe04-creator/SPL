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
        // 1. Indeks Performa Tabel Payments (Paling Sering Diceklis & Dijumlahkan)
        Schema::table('payments', function (Blueprint $table) {
            $table->index(['customer_package_id', 'is_paid'], 'idx_payments_pkg_paid');
            $table->index(['agent_id', 'is_paid', 'is_locked'], 'idx_payments_agent_paid_lock');
            $table->index(['agent_deposit_id', 'is_paid'], 'idx_payments_deposit_paid');
            $table->index(['status'], 'idx_payments_status');
        });

        // 2. Indeks Performa Tabel Customer Packages
        Schema::table('customer_packages', function (Blueprint $table) {
            $table->index(['agent_id', 'status'], 'idx_cp_agent_status');
            $table->index(['status', 'fulfillment_decision'], 'idx_cp_status_decision');
            $table->index(['customer_id', 'status'], 'idx_cp_cust_status');
            $table->index(['package_id', 'status'], 'idx_cp_pkg_status');
        });

        // 3. Indeks Performa Tabel Customers
        Schema::table('customers', function (Blueprint $table) {
            $table->index(['agent_id', 'is_active'], 'idx_cust_agent_active');
            $table->index(['name'], 'idx_cust_name');
        });

        // 4. Indeks Performa Tabel Agent Deposits
        Schema::table('agent_deposits', function (Blueprint $table) {
            $table->index(['agent_id', 'status'], 'idx_deposits_agent_status');
            $table->index(['status'], 'idx_deposits_status');
        });

        // 5. Indeks Performa Aktivitas Feed & Notifikasi Badge
        if (Schema::hasTable('posts')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->index(['created_at', 'user_id'], 'idx_posts_created_user');
            });
        }

        if (Schema::hasTable('comments')) {
            Schema::table('comments', function (Blueprint $table) {
                $table->index(['post_id', 'created_at'], 'idx_comments_post_created');
                $table->index(['created_at', 'user_id'], 'idx_comments_created_user');
            });
        }

        if (Schema::hasTable('post_likes')) {
            Schema::table('post_likes', function (Blueprint $table) {
                $table->index(['post_id', 'user_id'], 'idx_likes_post_user');
                $table->index(['created_at', 'user_id'], 'idx_likes_created_user');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('idx_payments_pkg_paid');
            $table->dropIndex('idx_payments_agent_paid_lock');
            $table->dropIndex('idx_payments_deposit_paid');
            $table->dropIndex('idx_payments_status');
        });

        Schema::table('customer_packages', function (Blueprint $table) {
            $table->dropIndex('idx_cp_agent_status');
            $table->dropIndex('idx_cp_status_decision');
            $table->dropIndex('idx_cp_cust_status');
            $table->dropIndex('idx_cp_pkg_status');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex('idx_cust_agent_active');
            $table->dropIndex('idx_cust_name');
        });

        Schema::table('agent_deposits', function (Blueprint $table) {
            $table->dropIndex('idx_deposits_agent_status');
            $table->dropIndex('idx_deposits_status');
        });

        if (Schema::hasTable('posts')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->dropIndex('idx_posts_created_user');
            });
        }

        if (Schema::hasTable('comments')) {
            Schema::table('comments', function (Blueprint $table) {
                $table->dropIndex('idx_comments_post_created');
                $table->dropIndex('idx_comments_created_user');
            });
        }

        if (Schema::hasTable('post_likes')) {
            Schema::table('post_likes', function (Blueprint $table) {
                $table->dropIndex('idx_likes_post_user');
                $table->dropIndex('idx_likes_created_user');
            });
        }
    }
};

