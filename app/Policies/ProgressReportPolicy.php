<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\GoalPost;
use App\Models\ProgressReport;
use App\Models\User;

class ProgressReportPolicy
{
    /**
     * 投稿の閲覧は誰でも可能
     */
    public function view(User $user): bool
    {
        return true;
    }

    // 進捗報告の作成画面を開けるのは、目標を宣言した本人だけ
    public function create(User $user, GoalPost $goal): bool
    {
        return $user->id === $goal->user_id;
    }

    // 投稿の作成はログインユーザーなら誰でも可能
    public function store(User $user): bool
    {
        return true;
    }

    public function update(User $user, ProgressReport $post): bool
    {
        return $user->id == $post->user_id;
    }

    /**
     * 投稿を削除できるのは投稿した本人 or 管理者だけ
     */
    public function delete(User $user, ProgressReport $post): bool
    {
        return $user->id == $post->user_id || $user->role === UserRole::Admin;
    }
}
