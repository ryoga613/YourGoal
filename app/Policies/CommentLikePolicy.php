<?php

namespace App\Policies;

use App\Models\CommentLike;
use App\Models\User;

class CommentLikePolicy
{
    // コメントいいねは誰でも作成可能
    public function create(User $user): bool
    {
        return true;
    }

    // コメントいいねは本人のみ削除可能
    public function delete(User $user, CommentLike $commentLike): bool
    {
        return $user->id === $commentLike->user_id;
    }
}
