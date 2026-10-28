<?php

namespace Database\Seeders;

use App\Enums\GoalPostStatus;
use App\Models\AchievementReport;
use App\Models\Comment;
use App\Models\CommentLike;
use App\Models\GoalPost;
use App\Models\KnowledgePost;
use App\Models\PostLike;
use App\Models\ProgressReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    /**
     * テスト用のダミーデータをまとめて投入する。
     */
    public function run(): void
    {
        $users = User::factory(10)->create();

        // 目標投稿と、それに紐づく進捗・達成レポート（投稿者は目標と同じユーザー）
        $users->each(function (User $user) {
            GoalPost::factory(3)->for($user)->create()->each(function (GoalPost $goalPost) {
                if ($goalPost->status === GoalPostStatus::Considering) {
                    return;
                }

                ProgressReport::factory(2)->for($goalPost, 'goal_post')->create();

                if ($goalPost->status === GoalPostStatus::Achieved) {
                    AchievementReport::factory()->for($goalPost, 'goal_post')->create();
                }
            });

            KnowledgePost::factory(2)->for($user)->create();
        });

        $posts = [
            'forGoalPost' => GoalPost::all(),
            'forKnowledgePost' => KnowledgePost::all(),
            'forProgressReport' => ProgressReport::all(),
            'forAchievementReport' => AchievementReport::all(),
        ];

        foreach ($posts as $state => $targets) {
            $targets->each(function (Model $target) use ($state, $users) {
                // コメントと返信
                Comment::factory(2)
                    ->$state($target)
                    ->recycle($users)
                    ->create()
                    ->each(fn (Comment $comment) => Comment::factory()
                        ->replyTo($comment)
                        ->recycle($users)
                        ->create());

                // いいね（同じユーザーが同じ投稿に複数回いいねしないよう、重複なしで選ぶ）
                $this->randomUsers($users)->each(fn (User $user) => PostLike::factory()
                    ->$state($target)
                    ->for($user)
                    ->create());
            });
        }

        Comment::all()->each(function (Comment $comment) use ($users) {
            $this->randomUsers($users)->each(fn (User $user) => CommentLike::factory()
                ->for($comment)
                ->for($user)
                ->create());
        });
    }

    /**
     * 0〜3人のユーザーを重複なしでランダムに選ぶ。
     *
     * @param  Collection<int, User>  $users
     * @return Collection<int, User>
     */
    private function randomUsers(Collection $users): Collection
    {
        return $users->random(fake()->numberBetween(0, 3));
    }
}
