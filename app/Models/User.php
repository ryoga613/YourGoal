<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'profile_image_path', 'bio',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * 新規作成時は一般ユーザーとする（role は Fillable に含めず、登録時に管理者を指定できないようにする）
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'role' => 'user',
    ];

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
            'role' => UserRole::class,
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isGeneral(): bool
    {
        return $this->role === UserRole::User;
    }

    public function goal_posts(): HasMany
    {
        return $this->hasMany(GoalPost::class);
    }

    public function progress_reports(): HasMany
    {
        return $this->hasMany(ProgressReport::class);
    }

    public function achievement_reports(): HasMany
    {
        return $this->hasMany(AchievementReport::class);
    }

    public function knowledge_posts(): HasMany
    {
        return $this->hasMany(KnowledgePost::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function post_likes(): HasMany
    {
        return $this->hasMany(PostLike::class);

    }

    public function comment_likes(): HasMany
    {
        return $this->hasMany(CommentLike::class);
    }
}
