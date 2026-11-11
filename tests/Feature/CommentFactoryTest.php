<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\GoalPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

class CommentFactoryTest extends TestCase
{
    use RefreshDatabase;

    #[TestDox('テストデータのコメントは、コメント先の投稿より後で今より前の日時になる')]
    public function test_comment_is_created_after_its_post(): void
    {
        $post = GoalPost::factory()->create(['created_at' => now()->subDays(10)]);

        foreach (Comment::factory(20)->forPost($post)->create() as $comment) {
            $this->assertTrue($comment->created_at->between($post->created_at, now()));
            $this->assertEquals($comment->created_at, $comment->updated_at);
        }
    }

    #[TestDox('テストデータの返信は、親コメントより後の日時になる')]
    public function test_reply_is_created_after_its_parent(): void
    {
        $post = GoalPost::factory()->create(['created_at' => now()->subDays(10)]);
        $parent = Comment::factory()->forPost($post)->create(['created_at' => now()->subDays(3)]);

        foreach (Comment::factory(20)->replyTo($parent)->create() as $reply) {
            $this->assertTrue($reply->created_at->between($parent->created_at, now()));
        }
    }

    #[TestDox('テストデータのコメントの日時がばらけて、同じ日時ばかりにならない')]
    public function test_comment_dates_are_spread(): void
    {
        $post = GoalPost::factory()->create(['created_at' => now()->subDays(30)]);

        $dates = Comment::factory(20)->forPost($post)->create()->pluck('created_at');

        $this->assertGreaterThan(1, $dates->unique()->count());
    }
}
