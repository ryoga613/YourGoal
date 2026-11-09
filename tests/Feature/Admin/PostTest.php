<?php

namespace Tests\Feature\Admin;

use App\Models\AchievementReport;
use App\Models\Comment;
use App\Models\GoalPost;
use App\Models\KnowledgePost;
use App\Models\PostLike;
use App\Models\ProgressReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    #[TestDox('管理者は投稿一覧で4種類すべての投稿を見られる')]
    public function test_admin_can_see_all_types_of_posts_in_list(): void
    {
        $goal = GoalPost::factory()->create(['title' => '目標のタイトル']);
        ProgressReport::factory()->for($goal, 'goal_post')->create(['title' => '進捗のタイトル']);
        AchievementReport::factory()->for($goal, 'goal_post')->create(['title' => '達成のタイトル']);
        KnowledgePost::factory()->create(['title' => '学びのタイトル']);

        $this->actingAs($this->admin)
            ->get(route('admin.posts.index'))
            ->assertOk()
            ->assertSee(['目標のタイトル', '進捗のタイトル', '達成のタイトル', '学びのタイトル'])
            ->assertSee($goal->user->name)
            ->assertSee('4件');
    }

    #[TestDox('投稿一覧は種類をまたいで新しい順に並ぶ')]
    public function test_list_is_ordered_by_newest_across_post_types(): void
    {
        GoalPost::factory()->create(['title' => '一番古い投稿', 'created_at' => now()->subDays(2)]);
        KnowledgePost::factory()->create(['title' => '一番新しい投稿', 'created_at' => now()]);
        GoalPost::factory()->create(['title' => '真ん中の投稿', 'created_at' => now()->subDay()]);

        $this->actingAs($this->admin)
            ->get(route('admin.posts.index'))
            ->assertSeeInOrder(['一番新しい投稿', '真ん中の投稿', '一番古い投稿']);
    }

    #[TestDox('タイトル・本文・投稿者名で投稿を検索できる')]
    public function test_admin_can_search_posts_by_title_body_or_user_name(): void
    {
        $taro = User::factory()->create(['name' => '山田太郎']);
        GoalPost::factory()->for($taro)->create(['title' => '太郎の目標']);
        // 投稿者名がランダムだと「山田」さんになることがあり、名前検索で誤ってヒットするので名前を固定する
        $other = User::factory()->create(['name' => '佐藤花子']);
        KnowledgePost::factory()->for($other)->create(['title' => '英語の学び', 'body' => '単語帳']);
        KnowledgePost::factory()->for($other)->create(['title' => '本文で検索', 'body' => '英語の勉強方法']);
        GoalPost::factory()->for($other)->create(['title' => '関係ない投稿', 'body' => '関係ない本文']);

        $this->actingAs($this->admin)
            ->get(route('admin.posts.index', ['keyword' => '英語']))
            ->assertSee(['英語の学び', '本文で検索'])
            ->assertDontSee('関係ない投稿');

        $this->actingAs($this->admin)
            ->get(route('admin.posts.index', ['keyword' => '山田']))
            ->assertSee('太郎の目標')
            ->assertDontSee('関係ない投稿');
    }

    #[TestDox('投稿の種類で絞り込める')]
    public function test_admin_can_filter_posts_by_type(): void
    {
        GoalPost::factory()->create(['title' => '目標宣言の投稿']);
        KnowledgePost::factory()->create(['title' => '学び共有の投稿']);

        $this->actingAs($this->admin)
            ->get(route('admin.posts.index', ['type' => 'knowledge']))
            ->assertSee('学び共有の投稿')
            ->assertDontSee('目標宣言の投稿')
            ->assertSee('<option value="knowledge" selected>', false);
    }

    #[TestDox('存在しない投稿の種類を指定すると入力エラーになる')]
    public function test_invalid_post_type_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.posts.index', ['type' => 'invalid']))
            ->assertSessionHasErrors('type');
    }

    #[TestDox('キーワードの % や _ は普通の文字として検索される')]
    public function test_like_wildcards_in_keyword_are_treated_as_characters(): void
    {
        KnowledgePost::factory()->create(['title' => '目標を100%達成']);
        KnowledgePost::factory()->create(['title' => '目標を100点達成']);

        $this->actingAs($this->admin)
            ->get(route('admin.posts.index', ['keyword' => '100%']))
            ->assertSee('目標を100%達成')
            ->assertDontSee('目標を100点達成');
    }

    #[TestDox('投稿一覧にコメント数といいね数が出る')]
    public function test_list_shows_comment_and_like_counts(): void
    {
        $post = KnowledgePost::factory()->create();
        Comment::factory()->forPost($post)->create();
        PostLike::factory(2)->forPost($post)->create();

        $this->actingAs($this->admin)
            ->get(route('admin.posts.index'))
            ->assertSee('<td class="nowrap">1 / 2</td>', false);
    }

    #[TestDox('投稿一覧で N+1 問題が起きない')]
    public function test_list_does_not_cause_n_plus_one_queries(): void
    {
        $countQueries = function (): int {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->actingAs($this->admin)->get(route('admin.posts.index'))->assertOk();

            return count(DB::getQueryLog());
        };

        $createPosts = function (int $count): void {
            $goals = GoalPost::factory($count)->create();
            ProgressReport::factory($count)->for($goals->first(), 'goal_post')->create();
            AchievementReport::factory($count)->for($goals->first(), 'goal_post')->create();
            KnowledgePost::factory($count)->create();
        };

        $createPosts(1);
        $before = $countQueries();

        $createPosts(4);
        $this->assertSame($before, $countQueries());
    }

    #[TestDox('投稿日時が同じでも、ページ送りで抜けや重複が出ない')]
    public function test_pagination_is_stable_when_created_at_is_the_same(): void
    {
        $createdAt = now();
        $goals = GoalPost::factory(13)->create(['created_at' => $createdAt]);
        $knowledge = KnowledgePost::factory(12)->create(['created_at' => $createdAt]);

        $keys = function (int $page) {
            return $this->actingAs($this->admin)
                ->get(route('admin.posts.index', ['page' => $page]))
                ->viewData('posts')
                ->map(fn (array $item) => $item['type']->value.':'.$item['post']->id);
        };

        $all = $keys(1)->concat($keys(2));

        $this->assertCount(25, $all->unique());
        $this->assertCount($goals->count() + $knowledge->count(), $all);
    }

    #[TestDox('管理者は投稿詳細とコメントを見られる')]
    public function test_admin_can_see_post_detail_with_comments(): void
    {
        $post = KnowledgePost::factory()->create(['title' => '詳細の投稿', 'body' => '詳細の本文']);
        $comment = Comment::factory()->forPost($post)->create(['body' => '投稿へのコメント']);

        $this->actingAs($this->admin)
            ->get(route('admin.posts.show', ['type' => 'knowledge', 'id' => $post->id]))
            ->assertOk()
            ->assertSee(['詳細の投稿', '詳細の本文', $post->user->email, '投稿へのコメント'])
            ->assertSee(route('admin.comments.show', $comment));
    }

    #[TestDox('進捗報告の詳細から、その目標に移れる')]
    public function test_progress_report_detail_links_to_its_goal(): void
    {
        $goal = GoalPost::factory()->create(['title' => '紐づく目標']);
        $report = ProgressReport::factory()->for($goal, 'goal_post')->create();

        $this->actingAs($this->admin)
            ->get(route('admin.posts.show', ['type' => 'progress', 'id' => $report->id]))
            ->assertOk()
            ->assertSee('紐づく目標')
            ->assertSee(route('admin.posts.show', ['type' => 'goal', 'id' => $goal->id]));
    }

    #[TestDox('目標の削除の確認に、一緒に消える報告の件数が出る')]
    public function test_goal_detail_shows_related_report_counts_in_delete_confirmation(): void
    {
        $goal = GoalPost::factory()->create();
        ProgressReport::factory(2)->for($goal, 'goal_post')->create();
        AchievementReport::factory()->for($goal, 'goal_post')->create();

        $this->actingAs($this->admin)
            ->get(route('admin.posts.show', ['type' => 'goal', 'id' => $goal->id]))
            ->assertSee('進捗報告2件・達成報告1件と、');
    }

    #[TestDox('存在しない投稿は404になる')]
    public function test_missing_post_returns_404(): void
    {
        $post = KnowledgePost::factory()->create();

        // IDが存在しても種類が違えば別の投稿として扱う
        $this->actingAs($this->admin)
            ->get(route('admin.posts.show', ['type' => 'goal', 'id' => $post->id + 100]))
            ->assertNotFound();

        $this->actingAs($this->admin)
            ->get('/admin/posts/invalid/'.$post->id)
            ->assertNotFound();
    }

    #[TestDox('投稿を削除するとコメントといいねも消える')]
    public function test_deleting_post_also_deletes_comments_and_likes(): void
    {
        $post = KnowledgePost::factory()->create();
        $comment = Comment::factory()->forPost($post)->create();
        Comment::factory()->replyTo($comment)->create();
        PostLike::factory()->forPost($post)->create();
        $other = KnowledgePost::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('admin.posts.destroy', ['type' => 'knowledge', 'id' => $post->id]))
            ->assertRedirect(route('admin.posts.index'))
            ->assertSessionHas('status', '投稿を削除しました。');

        $this->assertModelMissing($post);
        $this->assertDatabaseCount('comments', 0);
        $this->assertDatabaseCount('post_likes', 0);
        $this->assertModelExists($other);
    }

    #[TestDox('目標を削除すると進捗報告・達成報告も消える')]
    public function test_deleting_goal_also_deletes_its_reports(): void
    {
        $goal = GoalPost::factory()->create();
        $progress = ProgressReport::factory()->for($goal, 'goal_post')->create();
        $achievement = AchievementReport::factory()->for($goal, 'goal_post')->create();
        Comment::factory()->forPost($progress)->create();

        $this->actingAs($this->admin)
            ->delete(route('admin.posts.destroy', ['type' => 'goal', 'id' => $goal->id]));

        $this->assertModelMissing($goal);
        $this->assertModelMissing($progress);
        $this->assertModelMissing($achievement);
        $this->assertDatabaseCount('comments', 0);
    }

    #[TestDox('一般ユーザーは投稿管理を使えない')]
    public function test_general_user_can_not_manage_posts(): void
    {
        $user = User::factory()->create();
        $post = KnowledgePost::factory()->create();
        $params = ['type' => 'knowledge', 'id' => $post->id];

        $this->actingAs($user)->get(route('admin.posts.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.posts.show', $params))->assertForbidden();
        $this->actingAs($user)->delete(route('admin.posts.destroy', $params))->assertForbidden();

        $this->assertModelExists($post);
    }
}
