<?php

namespace Tests\Feature;

use App\Enums\GoalPostStatus;
use App\Enums\PostType;
use App\Models\GoalPost;
use App\Models\KnowledgePost;
use App\Models\ProgressReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 投稿詳細（/posts/{type}/{id}）の表示：編集・削除ボタン、目標の報告欄、シェアボタン
 */
class PostShowTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public static function postTypes(): array
    {
        return array_map(fn (PostType $type) => [$type], array_combine(
            array_map(fn (PostType $type) => $type->value, PostType::cases()),
            PostType::cases(),
        ));
    }

    private function showUrl(PostType $type, int $id): string
    {
        return route('posts.show', ['type' => $type->value, 'id' => $id]);
    }

    // ---- 表示・編集／削除ボタン ----

    #[TestDox('本人には編集・削除ボタンが出る')]
    #[DataProvider('postTypes')]
    public function test_owner_sees_edit_and_delete_buttons(PostType $type): void
    {
        $post = $type->modelClass()::factory()->for($this->user)->create();
        $params = ['type' => $type->value, 'id' => $post->id];

        $this->actingAs($this->user)
            ->get($this->showUrl($type, $post->id))
            ->assertOk()
            ->assertSee(route('posts.edit', $params), false)
            ->assertSee('action="'.route('posts.destroy', $params).'"', false);
    }

    #[TestDox('他人には編集・削除ボタンが出ない')]
    #[DataProvider('postTypes')]
    public function test_other_user_does_not_see_edit_and_delete_buttons(PostType $type): void
    {
        $post = $type->modelClass()::factory()->create();
        $params = ['type' => $type->value, 'id' => $post->id];

        $this->actingAs($this->user)
            ->get($this->showUrl($type, $post->id))
            ->assertOk()
            ->assertDontSee(route('posts.edit', $params), false)
            ->assertDontSee('action="'.route('posts.destroy', $params).'"', false);
    }

    #[TestDox('ステータスのタグは目標にだけ出る')]
    public function test_status_tag_is_shown_only_for_goal(): void
    {
        $goal = GoalPost::factory()->create(['status' => GoalPostStatus::Considering]);
        $knowledge = KnowledgePost::factory()->create();

        $this->actingAs($this->user)->get($this->showUrl(PostType::Goal, $goal->id))
            ->assertSee('<span class="tag">検討中</span>', false);

        $this->actingAs($this->user)->get($this->showUrl(PostType::Knowledge, $knowledge->id))
            ->assertOk()
            ->assertDontSee('<span class="tag">検討中</span>', false);
    }

    // ---- 目標の報告欄 ----

    #[TestDox('目標の詳細に、進捗報告が古い順にリンク付きで並ぶ')]
    public function test_goal_page_lists_progress_reports_in_order_with_links(): void
    {
        $goal = GoalPost::factory()->create();
        $first = ProgressReport::factory()->create(['goal_post_id' => $goal->id, 'title' => '最初の進捗', 'created_at' => now()->subDays(2)]);
        $second = ProgressReport::factory()->create(['goal_post_id' => $goal->id, 'title' => '次の進捗', 'created_at' => now()->subDay()]);
        ProgressReport::factory()->create(['title' => '別の目標の進捗']);

        $this->actingAs($this->user)
            ->get($this->showUrl(PostType::Goal, $goal->id))
            ->assertOk()
            ->assertSeeInOrder(['進捗の記録', '最初の進捗', '次の進捗'])
            ->assertDontSee('別の目標の進捗')
            ->assertSee($this->showUrl(PostType::Progress, $first->id), false)
            ->assertSee($this->showUrl(PostType::Progress, $second->id), false);
    }

    #[TestDox('進捗報告がないときは案内が出る')]
    public function test_goal_page_shows_empty_message_without_progress(): void
    {
        $goal = GoalPost::factory()->create();

        $this->actingAs($this->user)
            ->get($this->showUrl(PostType::Goal, $goal->id))
            ->assertSee('まだ進捗報告はありません。');
    }

    #[TestDox('挑戦中の目標の本人には、報告ボタンとステータス変更フォームが出る')]
    public function test_owner_of_in_progress_goal_sees_report_buttons_and_status_form(): void
    {
        $goal = GoalPost::factory()->for($this->user)->create(['status' => GoalPostStatus::InProgress]);

        $this->actingAs($this->user)
            ->get($this->showUrl(PostType::Goal, $goal->id))
            ->assertSee(route('progress-reports.create', $goal), false)
            ->assertSee(route('achievement-reports.create', $goal), false)
            ->assertSee(route('goals.status.update', $goal), false);
    }

    #[TestDox('達成済みの目標の本人には、進捗報告のボタンだけが出る')]
    public function test_owner_of_achieved_goal_sees_only_progress_button(): void
    {
        $goal = GoalPost::factory()->for($this->user)->create(['status' => GoalPostStatus::Achieved]);

        $this->actingAs($this->user)
            ->get($this->showUrl(PostType::Goal, $goal->id))
            ->assertSee(route('progress-reports.create', $goal), false)
            ->assertDontSee(route('achievement-reports.create', $goal), false)
            ->assertDontSee(route('goals.status.update', $goal), false);
    }

    #[TestDox('中止の目標の本人には、達成報告のボタンが出ない')]
    public function test_owner_of_cancelled_goal_cannot_see_achievement_button(): void
    {
        $goal = GoalPost::factory()->for($this->user)->create(['status' => GoalPostStatus::Cancelled]);

        $this->actingAs($this->user)
            ->get($this->showUrl(PostType::Goal, $goal->id))
            ->assertDontSee(route('achievement-reports.create', $goal), false)
            // 中止の目標は、挑戦中などに戻せる
            ->assertSee(route('goals.status.update', $goal), false);
    }

    #[TestDox('他人には報告ボタンもステータス変更フォームも出ない')]
    public function test_other_user_does_not_see_report_buttons_or_status_form(): void
    {
        $goal = GoalPost::factory()->create(['status' => GoalPostStatus::InProgress]);

        $this->actingAs($this->user)
            ->get($this->showUrl(PostType::Goal, $goal->id))
            ->assertOk()
            ->assertSee('進捗の記録')
            ->assertDontSee(route('progress-reports.create', $goal), false)
            ->assertDontSee(route('achievement-reports.create', $goal), false)
            ->assertDontSee(route('goals.status.update', $goal), false);
    }

    #[TestDox('目標以外の投稿には目標用の欄が出ない')]
    public function test_non_goal_pages_have_no_goal_section(): void
    {
        $knowledge = KnowledgePost::factory()->create();

        $this->actingAs($this->user)
            ->get($this->showUrl(PostType::Knowledge, $knowledge->id))
            ->assertOk()
            ->assertDontSee('id="goal-actions"', false)
            ->assertDontSee('進捗の記録');
    }

    // ---- シェアボタン ----

    #[TestDox('投稿詳細のシェアボタンに URL とタイトルが入る')]
    #[DataProvider('postTypes')]
    public function test_detail_page_has_share_button_with_url_and_title(PostType $type): void
    {
        $post = $type->modelClass()::factory()->create(['title' => 'シェアする"投稿"']);
        $url = $this->showUrl($type, $post->id);

        $this->actingAs($this->user)
            ->get($url)
            ->assertOk()
            ->assertSee('data-share-url="'.$url.'"', false)
            // タイトルはエスケープされて入る
            ->assertSee('data-share-title="シェアする&quot;投稿&quot;"', false);
    }

    #[TestDox('タイトルのない進捗報告は「進捗報告」としてシェアされる')]
    public function test_progress_report_without_title_shares_type_label(): void
    {
        $report = ProgressReport::factory()->create(['title' => null]);

        $this->actingAs($this->user)
            ->get($this->showUrl(PostType::Progress, $report->id))
            ->assertSee('data-share-title="進捗報告"', false);
    }

    #[TestDox('ホームの投稿カードにシェアボタンがある')]
    public function test_home_cards_have_share_buttons(): void
    {
        $knowledge = KnowledgePost::factory()->create(['title' => 'ホームの学び']);

        $this->actingAs($this->user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('data-share-url="'.$this->showUrl(PostType::Knowledge, $knowledge->id).'"', false)
            ->assertSee('data-share-title="ホームの学び"', false);
    }

    #[TestDox('ホームの見本の投稿にはシェアボタンが出ない')]
    public function test_home_demo_posts_have_no_share_button(): void
    {
        // 投稿が1件もないときは見本の投稿が出る。見本には id が無いのでシェアボタンを出さない
        $this->actingAs($this->user)
            ->get(route('home'))
            ->assertOk()
            // .share-btn などはレイアウトの JavaScript にも書かれているので、ボタンの属性（値の付いた形）で確かめる
            ->assertDontSee('data-share-url="', false);
    }

    // ---- ゲスト ----

    #[TestDox('ゲストはログイン画面に送られる')]
    public function test_guest_is_redirected_to_login(): void
    {
        $goal = GoalPost::factory()->create();

        $this->get($this->showUrl(PostType::Goal, $goal->id))->assertRedirect(route('login'));
    }

    #[TestDox('存在しない投稿は404になる')]
    public function test_missing_post_returns_404(): void
    {
        $this->actingAs($this->user)
            ->get($this->showUrl(PostType::Goal, 999))
            ->assertNotFound();
    }
}
