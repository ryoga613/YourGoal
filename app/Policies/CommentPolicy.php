<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    /**
     * コメントは誰でも閲覧可能
     */
    public function view(User $user): bool
    {
        return true;
    }

    /**
     * コメントの作成はログインユーザーなら誰でも可能
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * コメントを編集できるのは書いた本人だけ
     */
    public function update(User $user, Comment $comment): bool
    {
        return $user->id === $comment->user_id;
    }

    /**
     * コメントを削除できるのは書いた本人だけ（管理者の削除は管理画面から行う）
     */
    public function delete(User $user, Comment $comment): bool
    {
        return $user->id === $comment->user_id;
    }
}
