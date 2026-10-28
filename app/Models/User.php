<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Http\Models\GoalPost;
use Http\Models\ProgressReport;
use Http\Models\AchievementReport;
use Http\Models\KnowledgePost;
use Http\Models\Comment;
use Http\Models\PostLike;

use Http\Models\CommentLike;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function goal_posts() :HasMany
    {
        return $this->hasMany(GoalPost::class);
    }

    public function progress_reports() :HasMany
    {
        return $this->hasMany(ProgressReport::class);
    }

    public function achievement_reports() :HasMany
    {
        return $this->hasMany(AchievementReport::class);
    }

    public function knowledge_posts() :HasMany
    {
        return $this->hasMany(KnowledgePost::class);
    }

    public function comments() :HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function post_likes() :HasMany
    {
        return $this->hasMany(PostLike::class);

    }

    public function comment_likes() :HasMany
    {
        return $this->hasMany(CommentLike::class);
    }
    
}

// 
