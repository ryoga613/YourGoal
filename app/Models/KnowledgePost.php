<?php

namespace App\Models;

use Database\Factories\KnowledgePostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'title', 'body', 'image_path'])]
class KnowledgePost extends Model
{
    /** @use HasFactory<KnowledgePostFactory> */
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function post_likes(): HasMany
    {
        return $this->hasMany(PostLike::class, 'knowledge_post_id');
    }
}
