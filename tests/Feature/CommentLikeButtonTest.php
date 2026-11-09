<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\CommentLike;
use App\Models\KnowledgePost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 投稿詳細のコメントのいいねボタンの表示
 */
class CommentLikeButtonTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private KnowledgePost $post;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->post = KnowledgePost::factory()->create();
    }

    private function showPost()
    {
        return $this->actingAs($this->user)
            ->get(route('posts.show', ['type' => 'knowledge', 'id' => $this->post->id]))
            ->assertOk();
    }

    #[TestDox('コメント：いいね前は「いいねする」フォームといいね数が出る')]
    public function test_comment_shows_like_form_and_count_when_not_liked(): void
    {
        $comment = Comment::factory()->forPost($this->post)->create();
        CommentLike::factory()->for($comment)->create(); // 他の人のいいね

        $this->showPost()
            ->assertSee('action="'.route('comment-likes.store', $comment).'"', false)
            ->assertSee('♡ 1')
            ->assertDontSee('♥ 1');
    }

    #[TestDox('コメント：いいね済みなら「いいねを外す」フォームが出る')]
    public function test_comment_shows_unlike_form_when_liked(): void
    {
        $comment = Comment::factory()->forPost($this->post)->create();
        CommentLike::factory()->for($comment)->for($this->user)->create();

        $html = $this->showPost()->assertSee('♥ 1')->getContent();

        // いいね解除のフォームは DELETE で送る（URLは登録と同じなので、フォームの中身で判定する）
        $this->assertMatchesRegularExpression(
            '#action="'.preg_quote(route('comment-likes.destroy', $comment), '#').'">\s*<input type="hidden" name="_token"[^>]*>\s*<input type="hidden" name="_method" value="DELETE">#',
            $html,
        );
    }

    #[TestDox('返信にもいいねボタンがある')]
    public function test_replies_also_have_like_button(): void
    {
        $comment = Comment::factory()->forPost($this->post)->create();
        $reply = Comment::factory()->replyTo($comment)->create();

        $this->showPost()
            ->assertSee('action="'.route('comment-likes.store', $comment).'"', false)
            ->assertSee('action="'.route('comment-likes.store', $reply).'"', false);
    }

    #[TestDox('コメントにいいねすると投稿詳細に戻り、いいね済みになる')]
    public function test_liking_a_comment_returns_to_post_page_and_shows_liked(): void
    {
        $comment = Comment::factory()->forPost($this->post)->create();
        $postUrl = route('posts.show', ['type' => 'knowledge', 'id' => $this->post->id]);

        $this->actingAs($this->user)
            ->from($postUrl)
            ->post(route('comment-likes.store', $comment))
            ->assertRedirect($postUrl);

        $this->showPost()->assertSee('♥ 1');
    }
}
