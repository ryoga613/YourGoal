<?php

namespace App\Models;

use Database\Factories\PostLikeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'knowledge_post_id',
    'goal_post_id',
    'progress_report_id',
    'achievement_report_id',
])]
class PostLike extends Model
{
    /** @use HasFactory<PostLikeFactory> */
    use HasFactory;

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
}
