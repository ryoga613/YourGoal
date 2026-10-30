<?php

namespace Tests\Feature\Admin;

use App\Models\AchievementReport;
use App\Models\Comment;
use App\Models\GoalPost;
use App\Models\KnowledgePost;
use App\Models\ProgressReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_see_counts(): void
    {
        $admin = User::factory()->admin()->create();
        $users = User::factory(3)->create();

        $goalPosts = GoalPost::factory(2)->recycle($users)->create();
        ProgressReport::factory(3)->recycle($goalPosts)->create();
        AchievementReport::factory()->for($goalPosts->first(), 'goal_post')->create();
        $knowledgePost = KnowledgePost::factory()->recycle($users)->create();
        Comment::factory(4)->forPost($knowledgePost)->recycle($users)->create();
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
            ->assertDontSee('気になる投稿やジャンルを検索');
    }
}
