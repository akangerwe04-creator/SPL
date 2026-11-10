<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'address',
        'role',
        'avatar',
        'is_active',
        'password',
        'plain_password',
        'last_seen_feed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_seen_feed_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isOwnerOrAdmin(): bool
    {
        return in_array($this->role, ['owner', 'admin']);
    }

    public function isAgent(): bool
    {
        return $this->role === 'agent';
    }

    public function customers()
    {
        return $this->hasMany(Customer::class, 'agent_id');
    }

    public function deposits()
    {
        return $this->hasMany(AgentDeposit::class, 'agent_id');
    }

    public function agentDeposits()
    {
        return $this->deposits();
    }

    public function posts()
    {
        return $this->hasMany(Post::class, 'user_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'user_id');
    }

    public function activeDeposit()
    {
        return $this->hasOne(AgentDeposit::class, 'agent_id')->where('status', 'pending')->latest();
    }

    public function postLikes()
    {
        return $this->hasMany(PostLike::class, 'user_id');
    }

    /**
     * Hitung jumlah aktivitas feed (post, komentar, like) yang belum dilihat oleh user ini
     */
    public function getUnseenFeedActivitiesCount(): int
    {
        $lastSeen = $this->last_seen_feed_at;
        if (!$lastSeen) {
            $lastSeen = now()->subDays(7);
        }

        $newPosts = Post::where('created_at', '>', $lastSeen)
            ->where('user_id', '!=', $this->id)
            ->count();

        $newComments = Comment::where('created_at', '>', $lastSeen)
            ->where('user_id', '!=', $this->id)
            ->count();

        $newLikes = PostLike::where('created_at', '>', $lastSeen)
            ->where('user_id', '!=', $this->id)
            ->count();

        return $newPosts + $newComments + $newLikes;
    }

    /**
     * Memastikan akun Owner utama dengan username 'owner' dan password 'ownerspl'
     * selalu tersedia, aktif, dan terproteksi permanen di dalam sistem.
     */
    public static function ensurePermanentOwner(): self
    {
        // 1. Cari jika sudah ada akun dengan username 'owner'
        $owner = self::where('username', 'owner')->first();

        // 2. Jika belum ada 'owner', cari akun role 'owner', atau legacy 'susi', atau email 'owner@spl.com'
        if (!$owner) {
            $owner = self::where('role', 'owner')
                ->orWhere('username', 'susi')
                ->orWhere('email', 'owner@spl.com')
                ->orWhere('email', 'susi@spl.com')
                ->first();
        }

        if ($owner) {
            $owner->update([
                'name' => $owner->name ?: 'Owner',
                'username' => 'owner',
                'email' => ($owner->email && $owner->email !== 'susi@spl.com') ? $owner->email : 'owner@spl.com',
                'role' => 'owner',
                'password' => \Illuminate\Support\Facades\Hash::make('ownerspl'),
                'is_active' => true,
            ]);

            // Bersihkan duplikat legacy 'susi' jika ada akun lain yang tertinggal
            self::where('username', 'susi')->where('id', '!=', $owner->id)->delete();

            return $owner;
        }

        return self::create([
            'name' => 'Owner',
            'username' => 'owner',
            'email' => 'owner@spl.com',
            'phone' => '0812-3456-7890',
            'role' => 'owner',
            'password' => \Illuminate\Support\Facades\Hash::make('ownerspl'),
            'is_active' => true,
        ]);
    }
}

