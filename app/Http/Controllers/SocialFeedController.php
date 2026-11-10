<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use App\Models\Post;
use App\Models\Comment;
use App\Models\PostLike;
use App\Services\ImageOptimizer;

class SocialFeedController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Hitung aktivitas baru yang belum dilihat oleh akun ini
        $unseenBefore = $user ? $user->getUnseenFeedActivitiesCount() : 0;

        // Tandai status bahwa aktivitas feed telah dibuka dan dilihat oleh user
        if ($user) {
            $user->update(['last_seen_feed_at' => now()]);
            \Illuminate\Support\Facades\Cache::forget("spl_user_badge_{$user->id}");
        }

        $posts = Post::with(['user', 'comments.user', 'likes'])
            ->latest()
            ->paginate(10);

        return view('social.feed', compact('posts', 'user', 'unseenBefore'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:12288', // Hingga 12MB foto HP
            'caption' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();

        // Auto-kompresi gambar dari HP menjadi ~150KB
        $optimized = ImageOptimizer::optimizeAndSave($request->file('image'), 'posts', 1200, 78);

        Post::create([
            'user_id' => $user->id,
            'caption' => $request->caption,
            'image_path' => $optimized['path'],
            'compressed_size_kb' => $optimized['size_kb'],
            'likes_count' => 0,
        ]);

        return redirect()->route('social.feed')
            ->with('success', "Foto berhasil diunggah dan dikompres otomatis menjadi {$optimized['size_kb']} KB.");
    }

    public function storeComment(Request $request, Post $post)
    {
        $request->validate([
            'content' => 'required|string|max:500',
        ]);

        $user = Auth::user();

        Comment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'content' => $request->content,
        ]);

        return back()->with('success', 'Komentar terkirim.');
    }

    public function like(Request $request, Post $post)
    {
        $user = Auth::user();
        if (!$user) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }
            return redirect()->route('login');
        }

        $alreadyLiked = PostLike::where('post_id', $post->id)
            ->where('user_id', $user->id)
            ->exists();

        // Aturan SPL: akun yang sudah like tidak bisa like untuk kedua kalinya di postingan yang sama
        if ($alreadyLiked) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'already_liked' => true,
                    'message' => 'Anda sudah menyukai foto ini.',
                    'likes_count' => (int) $post->likes_count,
                ]);
            }
            return back()->with('info', 'Anda sudah menyukai postingan ini.');
        }

        PostLike::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);

        $post->increment('likes_count');
        $post->refresh();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'already_liked' => true,
                'message' => 'Disukai',
                'likes_count' => (int) $post->likes_count,
            ]);
        }

        return back();
    }

    /**
     * Moderasi: Tombol Hapus (Tong Sampah) untuk Owner atau pembuat post
     */
    public function destroy(Post $post)
    {
        $user = Auth::user();

        if (!$post->canBeDeletedBy($user)) {
            abort(403, 'Hanya Owner atau pembuat postingan yang dapat menghapus konten ini.');
        }

        if ($post->image_path && file_exists(public_path($post->image_path))) {
            @unlink(public_path($post->image_path));
        }

        PostLike::where('post_id', $post->id)->delete();
        $post->comments()->delete();
        $post->delete();

        return back()->with('success', 'Postingan telah dihapus.');
    }

    /**
     * Auto-Cleanup Trigger: Hapus foto yang sudah lebih dari 60 hari
     */
    public function runCleanup()
    {
        $user = Auth::user();
        if (!$user->isOwner()) {
            abort(403);
        }

        Artisan::call('spl:cleanup-posts', ['--days' => 60]);
        $output = Artisan::output();

        return back()->with('success', 'Auto-cleanup berhasil dijalankan: ' . trim($output));
    }
}

