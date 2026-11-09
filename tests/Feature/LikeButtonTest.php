<?php

namespace Tests\Feature;

use App\Models\GoalPost;
use App\Models\KnowledgePost;
use App\Models\PostLike;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * ホーム・投稿詳細のいいねボタンの表示
 */
class LikeButtonTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    #[TestDox('投稿詳細：いいね前は「いいねする」フォームが出る')]
    public function test_post_page_shows_like_form_when_not_liked(): void
    {
        $post = KnowledgePost::factory()->create();
        PostLike::factory()->forPost($post)->create(); // 他の人のいいね

        $this->actingAs($this->user)
            ->get(route('posts.show', ['type' => 'knowledge', 'id' => $post->id]))
            ->assertOk()
            ->assertSee('action="'.route('likes.store', ['type' => 'knowledge', 'id' => $post->id]).'"', false)
            ->assertSee('♡ 1 いいね')
            ->assertDontSee('name="_method" value="DELETE"', false);
    }

    #[TestDox('投稿詳細：いいね済みなら「いいねを外す」フォームが出る')]
    public function test_post_page_shows_unlike_form_when_liked(): void
    {
        $post = KnowledgePost::factory()->create();
        PostLike::factory()->forPost($post)->for($this->user)->create();

        $this->actingAs($this->user)
            ->get(route('posts.show', ['type' => 'knowledge', 'id' => $post->id]))
            ->assertSee('action="'.route('likes.destroy', ['type' => 'knowledge', 'id' => $post->id]).'"', false)
            ->assertSee('name="_method" value="DELETE"', false)
            ->assertSee('♥ 1 いいね');
    }

    #[TestDox('ホームでは投稿ごとに、いいね済みかどうかが表示される')]
    public function test_home_shows_liked_state_per_post(): void
    {
        $liked = GoalPost::factory()->create();
        $notLiked = KnowledgePost::factory()->create();
        PostLike::factory()->forPost($liked)->for($this->user)->create();
        PostLike::factory()->forPost($notLiked)->create(); // 他の人のいいねは「いいね済み」にしない

        $response = $this->actingAs($this->user)
            ->get(route('home', ['sort' => 'latest']))
            ->assertOk();

        $html = $response->getContent();
        $this->assertSame(1, substr_count($html, '♥ 1 いいね'));
        $this->assertSame(1, substr_count($html, '♡ 1 いいね'));
        $this->assertSame(1, substr_count($html, 'name="_method" value="DELETE"'));
    }
}
