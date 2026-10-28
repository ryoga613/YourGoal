<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'knowledge_post_id',
    'goal_post_id',
    'progress_report_id',
    'achievement_report_id',
    'parent_id',
    'body',
])]
class Comment extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function goal_post(): BelongsTo
    {
        return $this->belongsTo(GoalPost::class);
    }

    public function progress_report(): BelongsTo
    {
        return $this->belongsTo(ProgressReport::class);
    }

    public function achievement_report(): BelongsTo
    {
        return $this->belongsTo(AchievementReport::class);
    }

    public function knowledge_post(): BelongsTo
    {
        return $this->belongsTo(KnowledgePost::class);
    }

    public function comment_likes(): HasMany
    {
        return $this->hasMany(CommentLike::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }
}
