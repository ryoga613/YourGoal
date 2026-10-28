<?php

namespace App\Models;

use App\Enums\GoalPostStatus;
use Database\Factories\GoalPostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'title', 'body', 'status', 'image_path'])]

class GoalPost extends Model
{
    /** @use HasFactory<GoalPostFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['status' => GoalPostStatus::class];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function progress_reports(): HasMany
    {
        return $this->hasMany(ProgressReport::class);
    }

    public function achievement_reports(): HasMany
    {
        return $this->hasMany(AchievementReport::class);
    }

    public function post_likes(): HasMany
    {
        return $this->hasMany(PostLike::class, 'goal_post_id');
    }
}
