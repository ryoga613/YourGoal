<?php

namespace App\Policies;

use App\Enums\GoalPostStatus;
use App\Models\AchievementReport;
use App\Models\GoalPost;
use App\Models\User;

class AchievementReportPolicy
{
    // 投稿閲覧は誰でも閲覧可能
    public function view(User $user): bool
    {
        return true;
    }

    // 達成報告の作成画面を開けるのは、目標を宣言した本人で、目標が検討中か挑戦中のときだけ（達成済み・中止の目標には報告できない）
    public function create(User $user, GoalPost $goal): bool
    {
        return $user->id === $goal->user_id
            && in_array($goal->status, [GoalPostStatus::Considering, GoalPostStatus::InProgress], true);
    }

    // 達成報告の保存も、作成画面と同じ条件
    public function store(User $user, GoalPost $goal): bool
    {
        return $this->create($user, $goal);
    }

    // 投稿した本人しか、更新できないようにする
    public function update(User $user, AchievementReport $post): bool
    {
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
