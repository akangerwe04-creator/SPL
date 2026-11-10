@extends('layouts.app')

@section('title', 'Feed Komunitas Agen & Owner')
@section('page_title', 'Komunitas & Feed Lapangan')

@section('header_badge')
    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-pink-100 text-pink-700 border border-pink-200">
        <i class="fa-brands fa-instagram mr-1"></i> Instagram Mode
    </span>
@endsection

@section('content')
<div class="max-w-xl mx-auto space-y-6">

    <!-- TOP COMMUNITY BANNER -->
    <div class="bg-gradient-to-r from-pink-500 via-rose-500 to-amber-500 rounded-2xl p-4 text-white shadow-sm flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-xl font-bold backdrop-blur">
                <i class="fa-brands fa-instagram"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold text-pink-100 uppercase tracking-wider">Feed Komunitas Internal</div>
                <div class="font-heading font-extrabold text-sm md:text-base leading-tight">Aktivitas Agen & Bukti Parsel</div>
            </div>
        </div>

        @if(auth()->user()->isOwner())
            <a href="{{ route('owner.dashboard') }}" class="px-3 py-1.5 rounded-xl bg-white text-slate-800 font-bold text-xs hover:bg-slate-50 transition shadow-xs flex items-center">
                <i class="fa-solid fa-chart-pie mr-1 text-emerald-600"></i> Ke Dasbor
            </a>
        @endif
    </div>

    <!-- NOTIFIKASI AKTIVITAS FEED BARU (OTOMATIS TERBACA DAN HILANG KETIKA DIBUKA) -->
    @if(isset($unseenBefore) && $unseenBefore > 0)
        <div class="bg-gradient-to-r from-pink-50 to-rose-50 border border-pink-200 rounded-2xl p-3.5 flex items-center justify-between text-xs text-pink-950 shadow-2xs animate-in fade-in duration-300">
            <div class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-xl bg-rose-600 text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-xs">
                    <i class="fa-solid fa-bell animate-bounce"></i>
                </div>
                <div>
                    <div class="font-black text-rose-950 flex items-center space-x-1.5">
                        <span>Aktivitas Baru</span>
                        <span class="w-2 h-2 rounded-full bg-rose-600"></span>
                    </div>
                    <div class="text-[11px] text-rose-800">
                        Ada <strong>{{ $unseenBefore }} aktivitas baru</strong> (postingan/komentar/like) yang kini telah Anda lihat.
                    </div>
                </div>
            </div>
            <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-white text-rose-700 border border-rose-200 shadow-2xs shrink-0">
                Sudah Dilihat
            </span>
        </div>
    @endif

    <!-- CREATE POST BOX -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4" x-data="{ openUpload: false, previewUrl: null }">
        <div class="flex items-center space-x-3 cursor-pointer" @click="openUpload = true">
            <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 font-bold flex items-center justify-center flex-shrink-0 border border-emerald-200">
                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
            </div>
            <div class="flex-1 bg-slate-50 hover:bg-slate-100 rounded-xl py-2.5 px-4 text-xs text-slate-400 font-medium transition flex items-center justify-between">
                <span>Bagikan foto bukti parsel atau kegiatan hari ini...</span>
                <i class="fa-solid fa-camera text-slate-400"></i>
            </div>
        </div>

        <!-- UPLOAD FORM (MODAL OR EXPANDED) -->
        <div x-show="openUpload" x-cloak class="mt-4 pt-4 border-t border-slate-100">
            <form action="{{ route('social.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <div>
                    <textarea name="caption" rows="2" placeholder="Tuliskan keterangan / lokasi kegiatan lapangan..." class="w-full text-xs p-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-pink-500"></textarea>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <label class="cursor-pointer inline-flex items-center space-x-2 text-xs font-bold text-pink-600 hover:text-pink-700">
                        <i class="fa-solid fa-image text-base"></i>
                        <span>Pilih Foto dari HP/Kamera (Auto-Compress)</span>
                        <input type="file" name="image" accept="image/*" required class="hidden"
                            @change="const file = $event.target.files[0]; if(file) previewUrl = URL.createObjectURL(file)">
                    </label>

                    <div class="flex items-center space-x-2">
                        <button type="button" @click="openUpload = false; previewUrl = null" class="px-3 py-1.5 text-xs text-slate-500 hover:text-slate-800">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-1.5 rounded-lg bg-pink-600 hover:bg-pink-700 text-white font-bold text-xs shadow-xs transition">
                            Posting Foto
                        </button>
                    </div>
                </div>

                <!-- PREVIEW -->
                <template x-if="previewUrl">
                    <div class="mt-2 relative rounded-xl overflow-hidden max-h-48 bg-slate-100">
                        <img :src="previewUrl" class="w-full h-48 object-cover">
                    </div>
                </template>
            </form>
        </div>
    </div>

    <!-- POSTS TIMELINE -->
    <div class="space-y-6">
        @forelse($posts as $post)
            @php
                $isLikedByMe = $post->isLikedBy(auth()->user());
            @endphp
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden"
                 x-data="postCardHandler({{ $post->id }}, {{ $post->likes_count }}, '{{ route('social.like', $post) }}', {{ $isLikedByMe ? 'true' : 'false' }})">
                
                <!-- POST HEADER -->
                <div class="p-3.5 flex items-center justify-between border-b border-slate-100">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-9 h-9 rounded-full bg-slate-100 text-slate-700 font-bold flex items-center justify-center text-xs border border-slate-200">
                            {{ strtoupper(substr($post->user->name, 0, 2)) }}
                        </div>
                        <div>
                            <div class="font-bold text-xs text-slate-900 flex items-center space-x-1.5">
                                <span>{{ $post->user->name }}</span>
                                @if($post->user->isOwner())
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800">
                                        Owner
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-medium bg-slate-100 text-slate-600">
                                        Agen
                                    </span>
                                @endif
                            </div>
                            <div class="text-[10px] text-slate-400">
                                {{ $post->created_at->diffForHumans() }}
                                @if($post->compressed_size_kb > 0)
                                    &bull; <span class="text-emerald-600 font-medium"><i class="fa-solid fa-compress mr-0.5"></i>{{ $post->compressed_size_kb }} KB</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- MODERASI OWNER: TOMBOL HAPUS (TONG SAMPAH) -->
                    @if($post->canBeDeletedBy(auth()->user()))
                        <form action="{{ route('social.destroy', $post) }}" method="POST" onsubmit="return confirm('Hapus postingan ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" title="Moderasi: Hapus Postingan" class="p-1.5 text-slate-300 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                <i class="fa-solid fa-trash-can text-sm"></i>
                            </button>
                        </form>
                    @endif
                </div>

                <!-- POST IMAGE (DOUBLE CLICK / DOUBLE TAP INSTAGRAM HEART) -->
                <div class="bg-slate-900 relative max-h-[500px] overflow-hidden flex items-center justify-center cursor-pointer select-none group"
                     @dblclick="triggerHeartAnimation()"
                     @click="detectDoubleTap()">
                    <img src="{{ asset($post->image_path) }}" alt="Foto SPL" class="w-full object-cover max-h-[500px] pointer-events-none" loading="lazy">

                    <!-- Instagram Pop Heart Overlay -->
                    <template x-if="showHeart">
                        <div class="absolute top-1/2 left-1/2 pointer-events-none z-20 ig-heart-pop text-rose-500 drop-shadow-[0_12px_24px_rgba(225,29,72,0.9)]">
                            <i class="fa-solid fa-heart text-7xl md:text-8xl"></i>
                        </div>
                    </template>

                    <template x-if="showHeart">
                        <div class="absolute inset-0 pointer-events-none z-10 flex items-center justify-center">
                            <span class="text-3xl animate-ping opacity-75">💖</span>
                        </div>
                    </template>

                    <!-- Double-Tap Hint Tooltip on Desktop Hover -->
                    <div class="absolute bottom-2 right-2 bg-black/50 backdrop-blur text-white text-[10px] font-semibold px-2 py-0.5 rounded-full opacity-0 group-hover:opacity-100 transition duration-200 pointer-events-none">
                        <i class="fa-solid fa-heart text-rose-400 mr-1"></i> Ketuk 2x untuk suka
                    </div>
                </div>

                <!-- POST ACTIONS & CAPTION -->
                <div class="p-4 space-y-3">
                    
                    <!-- LIKE & COMMENTS COUNT -->
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <div class="flex items-center space-x-3">
                            <button type="button" @click="handleLikeButton()" class="flex items-center space-x-1.5 transition font-bold" :class="liked ? 'text-rose-600 cursor-default' : 'text-slate-700 hover:text-rose-600 cursor-pointer'">
                                <i class="text-base transition-transform" :class="liked ? 'fa-solid fa-heart text-rose-600 scale-110' : 'fa-regular fa-heart text-rose-500 hover:scale-110'"></i>
                                <span x-text="likesCount + ' Suka'">{{ $post->likes_count }} Suka</span>
                                <template x-if="liked">
                                    <span class="text-[9px] font-extrabold text-rose-700 bg-rose-50 px-1.5 py-0.2 rounded border border-rose-200 ml-0.5">Disukai</span>
                                </template>
                            </button>
                            <div class="flex items-center space-x-1 font-medium">
                                <i class="fa-regular fa-comment text-base text-slate-400"></i>
                                <span>{{ $post->comments->count() }} Komentar</span>
                            </div>
                        </div>
                    </div>

                    <!-- CAPTION -->
                    @if($post->caption)
                        <div class="text-xs text-slate-800 leading-relaxed">
                            <strong class="font-bold text-slate-900 mr-1">{{ $post->user->name }}</strong>
                            {{ $post->caption }}
                        </div>
                    @endif

                    <!-- COMMENTS LIST -->
                    @if($post->comments->isNotEmpty())
                        <div class="pt-2 border-t border-slate-100 space-y-1.5 max-h-40 overflow-y-auto">
                            @foreach($post->comments as $comment)
                                <div class="text-xs text-slate-700 bg-slate-50 p-2 rounded-lg">
                                    <strong class="font-bold text-slate-900 mr-1">{{ $comment->user->name }}:</strong>
                                    {{ $comment->content }}
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- COMMENT INPUT FORM -->
                    <form action="{{ route('social.comment', $post) }}" method="POST" class="pt-2 flex items-center space-x-2">
                        @csrf
                        <input type="text" name="content" required placeholder="Tulis komentar untuk {{ $post->user->name }}..." class="flex-1 text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-pink-500">
                        <button type="submit" class="px-3 py-2 bg-pink-600 hover:bg-pink-700 text-white rounded-lg font-bold text-xs shadow-xs transition">
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </form>

                </div>

            </div>
        @empty
            <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-400">
                <i class="fa-brands fa-instagram text-4xl mb-3 text-pink-300"></i>
                <div class="font-bold text-slate-700">Belum Ada Postingan Lapangan</div>
                <div class="text-xs mt-1">Jadilah yang pertama mengunggah foto parsel atau kegiatan hari ini!</div>
            </div>
        @endforelse

        <div class="pt-2">
            {{ $posts->links() }}
        </div>
    </div>

</div>
@endsection

@push('scripts')
<style>
@keyframes igHeartPop {
    0% {
        transform: translate(-50%, -50%) scale(0);
        opacity: 0;
    }
    15% {
        transform: translate(-50%, -50%) scale(1.35);
        opacity: 0.95;
    }
    30% {
        transform: translate(-50%, -50%) scale(0.95);
        opacity: 1;
    }
    45% {
        transform: translate(-50%, -50%) scale(1.1);
        opacity: 1;
    }
    75% {
        transform: translate(-50%, -50%) scale(1);
        opacity: 1;
    }
    100% {
        transform: translate(-50%, -50%) scale(0);
        opacity: 0;
    }
}
.ig-heart-pop {
    animation: igHeartPop 0.85s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
}
</style>

<script>
function postCardHandler(postId, initialLikes, likeUrl, initialLiked) {
    return {
        postId: postId,
        likesCount: initialLikes,
        liked: Boolean(initialLiked),
        showHeart: false,
        lastTap: 0,
        heartTimer: null,

        detectDoubleTap() {
            const now = Date.now();
            if (this.lastTap && (now - this.lastTap) < 320) {
                this.triggerHeartAnimation();
                this.lastTap = 0;
            } else {
                this.lastTap = now;
            }
        },

        triggerHeartAnimation() {
            if (this.heartTimer) clearTimeout(this.heartTimer);
            this.showHeart = false;
            setTimeout(() => {
                this.showHeart = true;
                this.heartTimer = setTimeout(() => {
                    this.showHeart = false;
                }, 850);
            }, 10);

            // Jika belum di-like, kirim like ke server (hanya 1 kali per akun)
            if (!this.liked) {
                this.sendLike();
            }
        },

        handleLikeButton() {
            if (this.liked) {
                // Sesuai aturan: akun yang sudah like tidak bisa like kedua kali
                return;
            }
            this.sendLike();
        },

        async sendLike() {
            if (this.liked) return;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const response = await fetch(likeUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token,
                    }
                });
                if (response.ok) {
                    const data = await response.json();
                    this.liked = true;
                    if (data && data.likes_count !== undefined) {
                        this.likesCount = data.likes_count;
                    }
                }
            } catch (err) {
                console.error('Like error:', err);
            }
        }
    }
}

// Auto update / bersihkan badge notifikasi saat halaman feed dibuka
document.addEventListener('DOMContentLoaded', () => {
    if (window.updateAppBadge) {
        window.updateAppBadge();
    }
});
</script>
@endpush

