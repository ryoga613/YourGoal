<?php

namespace Tests\Feature\Admin;

use App\Models\AchievementReport;
use App\Models\Comment;
use App\Models\GoalPost;
use App\Models\KnowledgePost;
use App\Models\ProgressReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    #[TestDox('管理者はダッシュボードでユーザー数・投稿数・今日のコメント数を見られる')]
    public function test_admin_can_see_counts(): void
    {
        $admin = User::factory()->admin()->create();
        $users = User::factory(3)->create();

        $goalPosts = GoalPost::factory(2)->recycle($users)->create();
        ProgressReport::factory(3)->recycle($goalPosts)->create();
        AchievementReport::factory()->for($goalPosts->first(), 'goal_post')->create();
        $knowledgePost = KnowledgePost::factory()->recycle($users)->create();
        // Factory はコメントの日時を過去にばらけさせるので、今日のコメントは日時を指定する
        Comment::factory(4)->forPost($knowledgePost)->recycle($users)->create(['created_at' => now()]);
        // 昨日のコメントは今日のコメント数に含めない
        Comment::factory()->forPost($knowledgePost)->recycle($users)->create(['created_at' => now()->subDay()]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('userCount', 3)
            ->assertViewHas('postCount', 7)
            ->assertViewHas('postCounts', ['目標宣言' => 2, '進捗報告' => 3, '達成報告' => 1, '学び共有' => 1])
            ->assertViewHas('todayCommentCount', 4)
            ->assertSee('管理者ダッシュボード')
            ->assertDontSee('気になる投稿を検索');
    }

    #[TestDox('今日のコメント数は、今日の0時から23:59:59までのコメントを数える')]
    public function test_today_comment_count_includes_only_today(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 11)->setTime(12, 0));

        $admin = User::factory()->admin()->create();
        $knowledgePost = KnowledgePost::factory()->create();

        $createCommentAt = fn (string $datetime) => Comment::factory()
            ->forPost($knowledgePost)
            ->create(['created_at' => $datetime]);

        $createCommentAt('2026-11-10 23:59:59'); // 昨日の最後 → 数えない
        $createCommentAt('2026-11-11 00:00:00'); // 今日の最初 → 数える
        $createCommentAt('2026-11-11 23:59:59'); // 今日の最後 → 数える
        $createCommentAt('2026-11-12 00:00:00'); // 明日の最初 → 数えない

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertViewHas('todayCommentCount', 2);
    }
}
