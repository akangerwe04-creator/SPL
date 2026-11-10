<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'caption',
        'image_path',
        'compressed_size_kb',
        'likes_count',
    ];

    protected $casts = [
        'compressed_size_kb' => 'integer',
        'likes_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->latest();
    }

    public function likes()
    {
        return $this->hasMany(PostLike::class);
    }

    public function isLikedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        if (!\Illuminate\Support\Facades\Schema::hasTable('post_likes')) {
            return false;
        }

        if ($this->relationLoaded('likes')) {
            return $this->likes->contains('user_id', $user->id);
        }

        return $this->likes()->where('user_id', $user->id)->exists();
    }

    public function canBeDeletedBy(User $user): bool
    {
        return $user->isOwner() || $this->user_id === $user->id;
    }
}

