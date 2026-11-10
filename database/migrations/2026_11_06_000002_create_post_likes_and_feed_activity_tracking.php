<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Like Postingan (1 like per user per post)
        if (!Schema::hasTable('post_likes')) {
            Schema::create('post_likes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->timestamps();

                $table->unique(['post_id', 'user_id']);
            });
        }

        // 2. Kolom waktu terakhir user melihat menu feed (untuk tracking status sudah dilihat)
        if (!Schema::hasColumn('users', 'last_seen_feed_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('last_seen_feed_at')->nullable()->after('remember_token');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('post_likes');

        if (Schema::hasColumn('users', 'last_seen_feed_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('last_seen_feed_at');
            });
        }
    }
};

