<?php

namespace Tests\Feature\Admin;

use App\Models\AchievementReport;
use App\Models\Comment;
use App\Models\CommentLike;
use App\Models\GoalPost;
use App\Models\KnowledgePost;
use App\Models\PostLike;
use App\Models\ProgressReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create(['name' => '管理者アカウント']);
    }

    public function test_admin_can_see_user_list(): void
    {
        $user = User::factory()->create(['name' => '一覧に出るユーザー']);

        $this->actingAs($this->admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('一覧に出るユーザー')
            ->assertSee($user->email)
            ->assertSee('1件');
    }

    public function test_admin_is_not_shown_in_list(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.users.index'))
            ->assertDontSee('管理者アカウント');

        // キーワードで検索しても管理者は出ない
        $this->actingAs($this->admin)
            ->get(route('admin.users.index', ['keyword' => '管理者']))
            ->assertDontSee('管理者アカウント');
    }

    public function test_list_is_ordered_by_newest(): void
    {
        User::factory()->create(['name' => '古いユーザー', 'created_at' => now()->subDays(2)]);
        User::factory()->create(['name' => '新しいユーザー', 'created_at' => now()]);
        User::factory()->create(['name' => '真ん中のユーザー', 'created_at' => now()->subDay()]);

        $this->actingAs($this->admin)
            ->get(route('admin.users.index'))
            ->assertSeeInOrder(['新しいユーザー', '真ん中のユーザー', '古いユーザー']);
    }

    public function test_admin_can_search_users_by_name_or_email(): void
    {
        User::factory()->create(['name' => '山田太郎', 'email' => 'taro@example.com']);
        User::factory()->create(['name' => '佐藤花子', 'email' => 'hanako@sample.jp']);
        User::factory()->create(['name' => '関係ないユーザー', 'email' => 'other@example.org']);

        $this->actingAs($this->admin)
            ->get(route('admin.users.index', ['keyword' => '山田']))
            ->assertSee('山田太郎')
            ->assertDontSee('佐藤花子')
            ->assertDontSee('関係ないユーザー');

        $this->actingAs($this->admin)
            ->get(route('admin.users.index', ['keyword' => 'sample.jp']))
            ->assertSee('佐藤花子')
            ->assertDontSee('山田太郎')
            ->assertDontSee('関係ないユーザー');
    }

    public function test_keyword_longer_than_255_characters_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.users.index', ['keyword' => str_repeat('あ', 256)]))
            ->assertSessionHasErrors('keyword');
    }

    public function test_like_wildcards_in_keyword_are_treated_as_characters(): void
    {
        User::factory()->create(['name' => '100%ユーザー']);
        User::factory()->create(['name' => '100点ユーザー']);
        User::factory()->create(['name' => 'snake_user']);
        User::factory()->create(['name' => 'snakeXuser']);

        $this->actingAs($this->admin)
            ->get(route('admin.users.index', ['keyword' => '100%']))
            ->assertSee('100%ユーザー')
            ->assertDontSee('100点ユーザー');

        $this->actingAs($this->admin)
            ->get(route('admin.users.index', ['keyword' => 'snake_']))
            ->assertSee('snake_user')
            ->assertDontSee('snakeXuser');
    }

    public function test_list_shows_post_and_comment_counts(): void
    {
        $user = User::factory()->create();
        $goal = GoalPost::factory()->for($user)->create();
        ProgressReport::factory()->for($goal, 'goal_post')->create();
        AchievementReport::factory()->for($goal, 'goal_post')->create();
        KnowledgePost::factory()->for($user)->create();
        Comment::factory(2)->for($user)->create();

        // 投稿は4種類の合計（目標宣言・進捗報告・達成報告・学び共有 各1件）
        $this->actingAs($this->admin)
            ->get(route('admin.users.index'))
            ->assertSee('<td class="nowrap">4 / 2</td>', false);
    }

    public function test_keyword_is_kept_in_pagination_links(): void
    {
        User::factory(21)->sequence(fn ($sequence) => ['name' => '検索ユーザー'.$sequence->index])->create();

        $this->actingAs($this->admin)
            ->get(route('admin.users.index', ['keyword' => '検索']))
            ->assertSee('keyword='.urlencode('検索'), false);
    }

    public function test_list_does_not_cause_n_plus_one_queries(): void
    {
        $countQueries = function (): int {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->actingAs($this->admin)->get(route('admin.users.index'))->assertOk();

            return count(DB::getQueryLog());
        };

        $createUsers = function (int $count): void {
            User::factory($count)->create()->each(function (User $user) {
                KnowledgePost::factory()->for($user)->create();
                Comment::factory()->for($user)->create();
            });
        };

        $createUsers(1);
        $before = $countQueries();

        $createUsers(4);
        $this->assertSame($before, $countQueries());
    }

    public function test_admin_can_see_user_detail_with_posts(): void
    {
        $user = User::factory()->create(['name' => '詳細のユーザー', 'bio' => '自己紹介文です']);
        $goal = GoalPost::factory()->for($user)->create(['title' => 'ユーザーの目標']);
        $knowledge = KnowledgePost::factory()->for($user)->create(['title' => 'ユーザーの学び']);
        KnowledgePost::factory()->create(['title' => '他人の投稿']);

        $this->actingAs($this->admin)
            ->get(route('admin.users.show', $user))
            ->assertOk()
            ->assertSee(['詳細のユーザー', $user->email, '自己紹介文です', 'ユーザーの目標', 'ユーザーの学び'])
            ->assertDontSee('他人の投稿')
            ->assertSee(route('admin.posts.show', ['type' => 'goal', 'id' => $goal->id]))
            ->assertSee(route('admin.posts.show', ['type' => 'knowledge', 'id' => $knowledge->id]));
    }

    public function test_delete_confirmation_shows_post_and_comment_counts(): void
    {
        $user = User::factory()->create();
        KnowledgePost::factory(2)->for($user)->create();
        Comment::factory(3)->for($user)->create();

        $this->actingAs($this->admin)
            ->get(route('admin.users.show', $user))
            ->assertSee('投稿2件・コメント3件');
    }

    public function test_deleting_user_also_deletes_posts_comments_and_likes(): void
    {
        $user = User::factory()->create();
        $post = KnowledgePost::factory()->for($user)->create();
        $comment = Comment::factory()->for($user)->create();
        $postLike = PostLike::factory()->for($user)->create();
        $commentLike = CommentLike::factory()->for($user)->create();
        $other = User::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $user))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('status', 'ユーザーを削除しました。');

        $this->assertModelMissing($user);
        $this->assertModelMissing($post);
        $this->assertModelMissing($comment);
        $this->assertModelMissing($postLike);
        $this->assertModelMissing($commentLike);
        $this->assertModelExists($other);
    }

    public function test_admin_user_can_not_be_deleted(): void
    {
        $otherAdmin = User::factory()->admin()->create();

        $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $this->admin))
            ->assertForbidden();

        $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $otherAdmin))
            ->assertForbidden();

        $this->assertModelExists($this->admin);
        $this->assertModelExists($otherAdmin);
    }

    public function test_general_user_can_not_manage_users(): void
    {
        $user = User::factory()->create();
        $target = User::factory()->create();

        $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.users.show', $target))->assertForbidden();
        $this->actingAs($user)->delete(route('admin.users.destroy', $target))->assertForbidden();

        $this->assertModelExists($target);
    }
}
