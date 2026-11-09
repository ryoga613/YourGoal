<?php

namespace App\Policies;

use App\Models\PostLike;
use App\Models\User;

class PostLikePolicy
{
    // 投稿いいねは誰でも作成可能
    public function create(User $user): bool
    {
        return true;
    }

    // 投稿いいねは本人のみ削除可能
    public function delete(User $user, PostLike $postLike): bool
    {
        return $user->id === $postLike->user_id;
    }
}
