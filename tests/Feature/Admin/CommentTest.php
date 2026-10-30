<?php

namespace Tests\Feature\Admin;

use App\Models\Comment;
use App\Models\CommentLike;
use App\Models\GoalPost;
use App\Models\KnowledgePost;
use App\Models\ProgressReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_can_see_comment_list(): void
    {
        $comment = Comment::factory()->forPost(GoalPost::factory()->create())->create(['body' => '一覧に表示されるコメント']);

        $this->actingAs($this->admin)
            ->get(route('admin.comments.index'))
            ->assertOk()
            ->assertSee('一覧に表示されるコメント')
            ->assertSee($comment->user->name);
    }

    public function test_admin_can_search_comments_by_body_or_user_name(): void
    {
        $taro = User::factory()->create(['name' => '山田太郎']);
        Comment::factory()->for($taro)->create(['body' => '最初のコメント']);
        Comment::factory()->create(['body' => '英語の勉強がんばります']);
        Comment::factory()->create(['body' => '関係ないコメント']);

        $this->actingAs($this->admin)
            ->get(route('admin.comments.index', ['keyword' => '英語']))
            ->assertSee('英語の勉強がんばります')
            ->assertDontSee('関係ないコメント');

        $this->actingAs($this->admin)
            ->get(route('admin.comments.index', ['keyword' => '山田']))
            ->assertSee('最初のコメント')
            ->assertDontSee('関係ないコメント');
    }

    public function test_admin_can_filter_comments_by_post_type(): void
    {
        Comment::factory()->forPost(GoalPost::factory()->create())->create(['body' => '目標へのコメント']);
        Comment::factory()->forPost(KnowledgePost::factory()->create())->create(['body' => '学びへのコメント']);

        $this->actingAs($this->admin)
            ->get(route('admin.comments.index', ['type' => 'goal']))
            ->assertSee('目標へのコメント')
            ->assertDontSee('学びへのコメント');
    }

    public function test_invalid_post_type_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.comments.index', ['type' => 'invalid']))
            ->assertSessionHasErrors('type');
    }

    public function test_admin_can_see_comment_detail_with_replies(): void
    {
        $post = GoalPost::factory()->create(['title' => 'コメント先の目標']);
        $comment = Comment::factory()->forPost($post)->create(['body' => '親コメント']);
        Comment::factory()->replyTo($comment)->create(['body' => '返信コメント']);

        $this->actingAs($this->admin)
            ->get(route('admin.comments.show', $comment))
            ->assertOk()
            ->assertSee('親コメント')
            ->assertSee('返信コメント')
            ->assertSee('コメント先の目標')
            ->assertSee(route('admin.posts.show', ['type' => 'goal', 'id' => $post->id]));
    }

    public function test_deleting_comment_also_deletes_replies_and_likes(): void
    {
        $comment = Comment::factory()->create();
        $reply = Comment::factory()->replyTo($comment)->create();
        CommentLike::factory()->for($comment)->create();
        CommentLike::factory()->for($reply)->create();
        $other = Comment::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('admin.comments.destroy', $comment))
            ->assertRedirect(route('admin.comments.index'))
            ->assertSessionHas('status', 'コメントを削除しました。');

        $this->assertModelMissing($comment);
        $this->assertModelMissing($reply);
        $this->assertDatabaseCount('comment_likes', 0);
        $this->assertModelExists($other);
    }

    public function test_list_shows_reply_and_like_counts(): void
    {
        $comment = Comment::factory()->create(['created_at' => now()->subDay()]);
        Comment::factory()->replyTo($comment)->create();
        CommentLike::factory(2)->for($comment)->create();

        $this->actingAs($this->admin)
            ->get(route('admin.comments.index'))
            ->assertSee('<td class="nowrap">1 / 2</td>', false);
    }

    public function test_list_does_not_cause_n_plus_one_queries(): void
    {
        $countQueries = function (): int {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->actingAs($this->admin)->get(route('admin.comments.index'))->assertOk();

            return count(DB::getQueryLog());
        };

        $createComments = function (int $count): void {
            foreach ([GoalPost::class, ProgressReport::class, KnowledgePost::class] as $postModel) {
                $comments = Comment::factory($count)->forPost($postModel::factory()->create())->create();
                Comment::factory()->replyTo($comments->first())->create();
            }
        };

        $createComments(1);
        $before = $countQueries();

        $createComments(4);
        $this->assertSame($before, $countQueries());
    }

    public function test_selected_post_type_is_kept_after_search(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.comments.index', ['type' => 'goal']))
            ->assertSee('<option value="goal" selected>', false);
    }

    public function test_pagination_is_stable_when_created_at_is_the_same(): void
    {
        $comments = Comment::factory(25)->create(['created_at' => now()]);

        $page1 = $this->actingAs($this->admin)->get(route('admin.comments.index'))->viewData('comments');
        $page2 = $this->actingAs($this->admin)->get(route('admin.comments.index', ['page' => 2]))->viewData('comments');

        $ids = $page1->pluck('id')->concat($page2->pluck('id'));
        $this->assertSame($comments->pluck('id')->sortDesc()->values()->all(), $ids->all());
    }

    public function test_like_wildcards_in_keyword_are_treated_as_characters(): void
    {
        Comment::factory()->create(['body' => '目標を100%達成']);
        Comment::factory()->create(['body' => '目標を100点達成']);
        Comment::factory()->create(['body' => 'snake_case']);
        Comment::factory()->create(['body' => 'snakeXcase']);

        $this->actingAs($this->admin)
            ->get(route('admin.comments.index', ['keyword' => '100%']))
            ->assertSee('目標を100%達成')
            ->assertDontSee('目標を100点達成');

        $this->actingAs($this->admin)
            ->get(route('admin.comments.index', ['keyword' => 'snake_']))
            ->assertSee('snake_case')
            ->assertDontSee('snakeXcase');
    }

    public function test_delete_confirmation_counts_nested_replies(): void
    {
        $comment = Comment::factory()->create();
        $reply = Comment::factory()->replyTo($comment)->create();
        $nestedReply = Comment::factory()->replyTo($reply)->create();
        Comment::factory()->replyTo($comment)->create();

        $this->actingAs($this->admin)
            ->get(route('admin.comments.show', $comment))
            ->assertSee('返信3件（返信への返信を含む）');

        $this->actingAs($this->admin)->delete(route('admin.comments.destroy', $comment));

        $this->assertModelMissing($nestedReply);
        $this->assertDatabaseCount('comments', 0);
    }

    public function test_general_user_can_not_manage_comments(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create();

        $this->actingAs($user)->get(route('admin.comments.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.comments.show', $comment))->assertForbidden();
        $this->actingAs($user)->delete(route('admin.comments.destroy', $comment))->assertForbidden();

        $this->assertModelExists($comment);
    }
}
