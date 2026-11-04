<?php

namespace Tests\Feature;

use App\Enums\PostType;
use App\Models\Comment;
use App\Models\KnowledgePost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CommentTest extends TestCase
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

    // ---- 投稿へのコメント ----

    #[DataProvider('postTypes')]
    public function test_user_can_comment_on_each_post_type(PostType $type): void
    {
        $post = $type->modelClass()::factory()->create();

        $response = $this->actingAs($this->user)
            ->post(route('comments.store', ['type' => $type->value, 'id' => $post->id]), ['body' => '応援しています！']);

        $comment = Comment::sole();
        $this->assertSame($this->user->id, $comment->user_id);
        $this->assertSame($post->id, $comment->{$type->foreignKey()});
        $this->assertNull($comment->parent_id);
        $this->assertSame('応援しています！', $comment->body);

        $response->assertRedirect(route('posts.show', ['type' => $type->value, 'id' => $post->id]).'#comment-'.$comment->id)
            ->assertSessionHas('status', 'コメントを投稿しました。');
    }

    public function test_guest_cannot_comment(): void
    {
        $post = KnowledgePost::factory()->create();

        $this->post(route('comments.store', ['type' => 'knowledge', 'id' => $post->id]), ['body' => 'コメント'])
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('comments', 0);
    }

    public function test_admin_cannot_comment(): void
    {
        $post = KnowledgePost::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->post(route('comments.store', ['type' => 'knowledge', 'id' => $post->id]), ['body' => 'コメント']);

        $this->assertDatabaseCount('comments', 0);
    }

    public function test_commenting_on_missing_post_returns_404(): void
    {
        $this->actingAs($this->user)
            ->post(route('comments.store', ['type' => 'knowledge', 'id' => 999]), ['body' => 'コメント'])
            ->assertNotFound();
    }

    public function test_comment_body_is_required_and_limited_to_1000_characters(): void
    {
        $post = KnowledgePost::factory()->create();
        $url = route('comments.store', ['type' => 'knowledge', 'id' => $post->id]);

        $this->actingAs($this->user)->post($url, ['body' => ''])
            ->assertSessionHasErrors(['body' => 'コメントを入力してください。']);

        $this->actingAs($this->user)->post($url, ['body' => str_repeat('あ', 1001)])
            ->assertSessionHasErrors(['body' => 'コメントは1000文字以内で入力してください。']);

        $this->actingAs($this->user)->post($url, ['body' => str_repeat('あ', 1000)])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('comments', 1);
    }

    // ---- 返信 ----

    public function test_reply_is_attached_to_parent_and_same_post(): void
    {
        $post = KnowledgePost::factory()->create();
        $parent = Comment::factory()->forPost($post)->create();

        $this->actingAs($this->user)
            ->post(route('comments.reply', $parent), ['body' => '返信です'])
            ->assertSessionHas('status', '返信を投稿しました。');

        $reply = Comment::where('parent_id', $parent->id)->sole();
        $this->assertSame($post->id, $reply->knowledge_post_id);
        $this->assertSame($this->user->id, $reply->user_id);
        $this->assertSame(PostType::Knowledge, $reply->postType());
    }

    public function test_reply_validation_error_goes_to_reply_error_bag(): void
    {
        $parent = Comment::factory()->forPost(KnowledgePost::factory()->create())->create();

        $this->actingAs($this->user)
            ->post(route('comments.reply', $parent), ['body' => '', 'reply_to' => $parent->id])
            ->assertSessionHasErrorsIn('reply', 'body');

        $this->assertDatabaseCount('comments', 1);
    }

    // ---- 編集 ----

    public function test_user_can_update_own_comment(): void
    {
        $comment = Comment::factory()->for($this->user)->create(['body' => '編集前']);

        $this->actingAs($this->user)
            ->patch(route('comments.update', $comment), ['body' => '編集後'])
            ->assertSessionHas('status', 'コメントを更新しました。');

        $this->assertSame('編集後', $comment->fresh()->body);
    }

    public function test_user_cannot_update_others_comment(): void
    {
        $comment = Comment::factory()->create(['body' => '他人のコメント']);

        $this->actingAs($this->user)
            ->patch(route('comments.update', $comment), ['body' => '書き換え'])
            ->assertForbidden();

        $this->assertSame('他人のコメント', $comment->fresh()->body);
    }

    public function test_update_validation_error_goes_to_update_error_bag(): void
    {
        $comment = Comment::factory()->for($this->user)->create(['body' => '編集前']);

        $this->actingAs($this->user)
            ->patch(route('comments.update', $comment), ['body' => ''])
            ->assertSessionHasErrorsIn('commentUpdate', 'body');

        $this->assertSame('編集前', $comment->fresh()->body);
    }

    // ---- 削除 ----

    public function test_user_can_delete_own_comment_with_its_replies(): void
    {
        $post = KnowledgePost::factory()->create();
        $comment = Comment::factory()->for($this->user)->forPost($post)->create();
        $reply = Comment::factory()->replyTo($comment)->create();
        Comment::factory()->replyTo($reply)->create();
        $other = Comment::factory()->forPost($post)->create();

        $this->actingAs($this->user)
            ->delete(route('comments.destroy', $comment))
            ->assertRedirect(route('posts.show', ['type' => 'knowledge', 'id' => $post->id]).'#comments')
            ->assertSessionHas('status', 'コメントを削除しました。');

        // 返信・返信への返信も消え、他のコメントは残る
        $this->assertSame([$other->id], Comment::pluck('id')->all());
    }

    public function test_user_cannot_delete_others_comment(): void
    {
        $comment = Comment::factory()->create();

        $this->actingAs($this->user)
            ->delete(route('comments.destroy', $comment))
            ->assertForbidden();

        $this->assertModelExists($comment);
    }

    // ---- 詳細画面の表示 ----

    public function test_post_page_shows_comments_and_nested_replies_in_order(): void
    {
        $post = KnowledgePost::factory()->create();
        $first = Comment::factory()->forPost($post)->create(['body' => '最初のコメント', 'created_at' => now()->subHours(3)]);
        Comment::factory()->forPost($post)->create(['body' => '次のコメント', 'created_at' => now()->subHours(2)]);
        Comment::factory()->replyTo($first)->create(['body' => '最初のコメントへの返信', 'created_at' => now()->subHour()]);

        $this->actingAs($this->user)
            ->get(route('posts.show', ['type' => 'knowledge', 'id' => $post->id]))
            ->assertOk()
            // 返信は、あとから書かれていても親コメントの直後に表示される
            ->assertSeeInOrder(['最初のコメント', '最初のコメントへの返信', '次のコメント'])
            ->assertSee('3 コメント');
    }

    public function test_edit_and_delete_buttons_are_shown_only_on_own_comments(): void
    {
        $post = KnowledgePost::factory()->create();
        $mine = Comment::factory()->for($this->user)->forPost($post)->create();
        $others = Comment::factory()->forPost($post)->create();

        $this->actingAs($this->user)
            ->get(route('posts.show', ['type' => 'knowledge', 'id' => $post->id]))
            // 編集・削除の URL（/comments/{id}）は返信の URL（/comments/{id}/replies）の一部なので、編集フォームの隠し項目で判定する
            ->assertSee('name="editing" value="'.$mine->id.'"', false)
            ->assertDontSee('name="editing" value="'.$others->id.'"', false)
            ->assertSee(route('comments.reply', $others), false);
    }
}
