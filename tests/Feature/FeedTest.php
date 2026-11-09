<?php

namespace Tests\Feature;

use App\Models\GoalPost;
use App\Models\KnowledgePost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

class FeedTest extends TestCase
{
    use RefreshDatabase;

    #[TestDox('ホームでは各投稿が1回だけ、種類ごとの詳細へのリンク付きで表示される')]
    public function test_each_post_is_shown_once_with_link_to_its_type(): void
    {
        $goal = GoalPost::factory()->create(['title' => 'ホームの目標']);
        $knowledge = KnowledgePost::factory()->create(['title' => 'ホームの学び']);

        $response = $this->actingAs(User::factory()->create())
            ->get(route('home', ['sort' => 'latest']))
            ->assertOk()
            ->assertSee(route('posts.show', ['type' => 'goal', 'id' => $goal->id]), false)
            ->assertSee(route('posts.show', ['type' => 'knowledge', 'id' => $knowledge->id]), false);

        // 同じ投稿が2回表示されていないこと（タイトルはシェアボタンの data-share-title にも入るので、カードの見出しで数える）
        $this->assertSame(1, substr_count($response->getContent(), '<h2 class="post-title">ホームの目標</h2>'));
    }
}
