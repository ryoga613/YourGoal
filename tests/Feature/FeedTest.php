<?php

namespace Tests\Feature;

use App\Models\GoalPost;
use App\Models\KnowledgePost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

    #[TestDox('新着順の2ページ目には、種類をまたいで21件目以降の投稿が表示される')]
    public function test_second_page_shows_older_posts_across_types(): void
    {
        // 25件を1分ずつずらして作る（種類は交互に入れ替える。進捗・達成報告は親の目標も作られるので使わない）
        $models = [GoalPost::class, KnowledgePost::class];
        for ($i = 1; $i <= 25; $i++) {
            $models[$i % 2]::factory()->create([
                'title' => "投稿{$i}",
                'created_at' => now()->subMinutes($i),
            ]);
        }

        $response = $this->actingAs(User::factory()->create())
            ->get(route('home', ['sort' => 'latest', 'page' => 2]))
            ->assertOk()
            ->assertSeeInOrder(array_map(fn ($i) => "<h2 class=\"post-title\">投稿{$i}</h2>", range(21, 25)), false);

        $this->assertSame(5, substr_count($response->getContent(), '<h2 class="post-title">'));
        $this->assertStringNotContainsString('<h2 class="post-title">投稿20</h2>', $response->getContent());
    }

    #[TestDox('ホームのクエリ数はページ番号によって増えない')]
    public function test_query_count_does_not_grow_with_page(): void
    {
        $user = User::factory()->create();
        GoalPost::factory()->count(30)->create();
        KnowledgePost::factory()->count(30)->create();

        $countQueries = function (int $page) use ($user) {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->actingAs($user)->get(route('home', ['page' => $page]))->assertOk();
            DB::disableQueryLog();

            return count(DB::getQueryLog());
        };

        $this->assertSame($countQueries(1), $countQueries(3));
    }
}
