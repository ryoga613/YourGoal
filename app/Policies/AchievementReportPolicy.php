<?php

namespace App\Policies;

use App\Models\AchievementReport;
use App\Models\User;

class AchievementReportPolicy
{
    /**
     * Create a new policy instance.
     */
    public function update(User $user, AchievementReport $post): bool
    {
        // dd($user, $post);
        return $user->id == $post->user_id;
    }

    /**
     * 投稿を削除できるのは投稿した本人だけ（管理者の削除は管理画面から行う）
     */
    public function delete(User $user, AchievementReport $post): bool
    {
        return $user->id == $post->user_id;
    }
}
