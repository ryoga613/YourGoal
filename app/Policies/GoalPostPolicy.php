<?php

namespace App\Policies;

use App\Models\GoalPost;
use App\Models\User;

class GoalPostPolicy
{
    // 目標宣言は誰でも閲覧可能
    public function view(User $user): bool
    {
        return true;
    }

    // 目標宣言の作成はログインユーザーなら誰でも可能
    public function create(User $user): bool
    {
        return true;
    }

    // 投稿の作成はログインユーザーなら誰でも可能
    public function store(User $user, GoalPost $goal): bool
    {
        return $user->id === $goal->user_id;
    }

    // 目標宣言の更新は投稿した本人だけができるようにする
    public function update(User $user, GoalPost $post): bool
    {
        return $user->id == $post->user_id;
    }

    // 投稿を削除できるのは投稿した本人だけ（管理者の削除は管理画面から行う）
    public function delete(User $user, GoalPost $post): bool
    {
        return $user->id == $post->user_id;
    }
}
