<?php

namespace App\Models;

use Database\Factories\AchievementReportFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['goal_post_id', 'user_id', 'title', 'body', 'image_path'])]
class AchievementReport extends Model
{
    /** @use HasFactory<AchievementReportFactory> */
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function goal_post(): BelongsTo
    {
        return $this->belongsTo(GoalPost::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function post_likes(): HasMany
    {
        return $this->hasMany(PostLike::class, 'achievement_report_id');
    }
}
