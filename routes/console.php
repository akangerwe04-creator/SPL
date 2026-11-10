<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('spl:cleanup-posts {--days=60 : Umur maksimal postingan dalam hari}', function () {
    $days = (int) $this->option('days');
    $cutoff = Carbon::now()->subDays($days);
    
    $oldPosts = Post::where('created_at', '<', $cutoff)->get();
    $count = 0;
    
    foreach ($oldPosts as $post) {
        if ($post->image_path && file_exists(public_path($post->image_path))) {
            @unlink(public_path($post->image_path));
        }
        $post->comments()->delete();
        $post->delete();
        $count++;
    }
    
    $this->info("Berhasil membersihkan {$count} postingan lama (> {$days} hari).");
})->purpose('Auto-cleanup foto dan postingan sosial media yang sudah kadaluarsa');

Schedule::command('spl:cleanup-posts')->daily();

