<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\KnowledgePost;
use App\Models\User;

class KnowledgePostPolicy
{
    // 知識投稿の閲覧はログインユーザーなら誰でも可能
    public function view(User $user): bool
    {
        return true;
    }

    // 知識投稿の作成はログインユーザーなら誰でも可能
    public function create(User $user): bool
    {
        return true;
    }

    // 知識投稿の作成はログインユーザーなら誰でも可能
    public function store(User $user): bool
    {
        return true;
    }

    public function update(User $user, KnowledgePost $post): bool
    {
        return $user->id == $post->user_id;
    }

    /**
     * 投稿を削除できるのは投稿した本人 or 管理者だけ
     */
    public function delete(User $user, KnowledgePost $post): bool
    {
        return $user->id == $post->user_id || $user->role === UserRole::Admin;
    }
}
