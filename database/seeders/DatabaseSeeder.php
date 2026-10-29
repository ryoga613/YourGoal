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
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ログイン確認用のユーザー（パスワードはどちらも "password"）
        $testUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        $users = User::factory(10)->create()->push($testUser);

        $goalPosts = GoalPost::factory(30)->recycle($users)->create();
        $knowledgePosts = KnowledgePost::factory(15)->recycle($users)->create();

        // 進捗報告・達成報告の投稿者は、Factory側で目標の投稿者に揃えられる
        $progressReports = ProgressReport::factory(40)->recycle($goalPosts)->create();

        // 達成報告は達成済みの目標にだけ作る
        $achievementReports = $goalPosts
            ->where('status', GoalPostStatus::Achieved)
            ->map(fn (GoalPost $goalPost) => AchievementReport::factory()->for($goalPost, 'goal_post')->create());

        $posts = $knowledgePosts
            ->concat($goalPosts)
            ->concat($progressReports)
            ->concat($achievementReports);

        // 各投稿に0〜3件のコメントをつける
        $comments = $posts->flatMap(
            fn ($post) => Comment::factory(fake()->numberBetween(0, 3))->forPost($post)->recycle($users)->create()
        );

        // 一部のコメントに返信をつける
        $comments->random(min(20, $comments->count()))->each(
            fn (Comment $comment) => Comment::factory()->replyTo($comment)->recycle($users)->create()
        );

        // いいねは同じユーザーが同じ投稿・コメントに重複しないよう、ユーザーを重複なしで選ぶ
        foreach ($posts as $post) {
            foreach ($users->random(fake()->numberBetween(0, 5)) as $user) {
                PostLike::factory()->forPost($post)->for($user)->create();
            }
        }

        foreach (Comment::all() as $comment) {
            foreach ($users->random(fake()->numberBetween(0, 3)) as $user) {
                CommentLike::factory()->for($user)->for($comment)->create();
            }
        }
    }
}
