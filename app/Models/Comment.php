<?php

namespace App\Models;

use App\Enums\PostType;
use Database\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
    /** @use HasFactory<CommentFactory> */
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

    /**
     * コメント先の投稿の種類（4種類の外部キーのうち、値が入っているもの）
     */
    public function postType(): ?PostType
    {
        foreach (PostType::cases() as $type) {
            if ($this->{$type->foreignKey()} !== null) {
                return $type;
            }
        }

        return null;
    }

    /**
     * コメント先の投稿
     */
    public function post(): GoalPost|ProgressReport|AchievementReport|KnowledgePost|null
    {
        $type = $this->postType();

        return $type ? $this->{$type->relation()} : null;
    }

    /**
     * 返信の件数（返信への返信など、下の階層もすべて含む）
     */
    public function descendantCount(): int
    {
        $count = 0;
        $parentIds = [$this->id];

        // 1階層ずつ下の返信をたどって数える
        while ($parentIds !== []) {
            $parentIds = static::whereIn('parent_id', $parentIds)->pluck('id')->all();
            $count += count($parentIds);
        }

        return $count;
    }

    /**
     * 投稿の種類で絞り込む
     */
    #[Scope]
    protected function forPostType(Builder $query, PostType $type): void
    {
        $query->whereNotNull($type->foreignKey());
    }
}
