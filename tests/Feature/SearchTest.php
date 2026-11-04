<?php

namespace Tests\Feature;

use App\Models\AchievementReport;
use App\Models\GoalPost;
use App\Models\KnowledgePost;
use App\Models\ProgressReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('posts.search'))->assertRedirect(route('login'));
    }

    public function test_without_conditions_all_types_are_listed(): void
    {
        $goal = GoalPost::factory()->create(['title' => '目標のタイトル']);
        ProgressReport::factory()->for($goal, 'goal_post')->create(['title' => '進捗のタイトル']);
        AchievementReport::factory()->for($goal, 'goal_post')->create(['title' => '達成のタイトル']);
        KnowledgePost::factory()->create(['title' => '学びのタイトル']);

        $this->actingAs($this->user)
            ->get(route('posts.search'))
            ->assertOk()
            ->assertSee(['目標のタイトル', '進捗のタイトル', '達成のタイトル', '学びのタイトル'])
            ->assertSee('4件');
    }

    public function test_keyword_matches_title(): void
    {
        KnowledgePost::factory()->create(['title' => 'Laravelの勉強法', 'body' => '本文']);
        KnowledgePost::factory()->create(['title' => '関係ない投稿', 'body' => '本文']);

        $this->actingAs($this->user)
            ->get(route('posts.search', ['keyword' => 'Laravel']))
            ->assertOk()
            ->assertSee('Laravelの勉強法')
            ->assertDontSee('関係ない投稿')
            ->assertSee('1件');
    }

    public function test_keyword_matches_body(): void
    {
        GoalPost::factory()->create(['title' => '本文でヒットする投稿', 'body' => '毎朝ランニングする']);
        GoalPost::factory()->create(['title' => '関係ない投稿', 'body' => '読書する']);

        $this->actingAs($this->user)
            ->get(route('posts.search', ['keyword' => 'ランニング']))
            ->assertSee('本文でヒットする投稿')
            ->assertDontSee('関係ない投稿');
    }

    public function test_keyword_matches_user_name(): void
    {
        $author = User::factory()->create(['name' => 'やまだたろう']);
        KnowledgePost::factory()->for($author)->create(['title' => '山田さんの投稿']);
        KnowledgePost::factory()->create(['title' => '他の人の投稿']);

        $this->actingAs($this->user)
            ->get(route('posts.search', ['keyword' => 'やまだ']))
            ->assertSee('山田さんの投稿')
            ->assertDontSee('他の人の投稿');
    }

    public function test_like_wildcards_in_keyword_are_searched_as_plain_text(): void
    {
        KnowledgePost::factory()->create(['title' => '達成率100%を目指す']);
        KnowledgePost::factory()->create(['title' => '普通の投稿']);

        // % がワイルドカード扱いされると全件ヒットしてしまう
        $this->actingAs($this->user)
            ->get(route('posts.search', ['keyword' => '%']))
            ->assertSee('達成率100%を目指す')
            ->assertDontSee('普通の投稿');
    }

    public function test_type_filters_post_type(): void
    {
        GoalPost::factory()->create(['title' => '目標のタイトル']);
        KnowledgePost::factory()->create(['title' => '学びのタイトル']);

        $this->actingAs($this->user)
            ->get(route('posts.search', ['type' => 'knowledge']))
            ->assertSee('学びのタイトル')
            ->assertDontSee('目標のタイトル')
            ->assertSee('1件');
    }

    public function test_keyword_and_type_are_combined_with_and(): void
    {
        GoalPost::factory()->create(['title' => '英語の目標']);
        KnowledgePost::factory()->create(['title' => '英語の学び']);
        KnowledgePost::factory()->create(['title' => '数学の学び']);

        $this->actingAs($this->user)
            ->get(route('posts.search', ['keyword' => '英語', 'type' => 'knowledge']))
            ->assertSee('英語の学び')
            ->assertDontSee('英語の目標')
            ->assertDontSee('数学の学び');
    }

    public function test_invalid_type_is_rejected(): void
    {
        $this->actingAs($this->user)
            ->get(route('posts.search', ['type' => 'invalid']))
            ->assertSessionHasErrors('type');
    }

    public function test_results_link_to_post_detail(): void
    {
        $post = KnowledgePost::factory()->create();

        $this->actingAs($this->user)
            ->get(route('posts.search'))
            ->assertSee(route('posts.show', ['type' => 'knowledge', 'id' => $post->id]), false);
    }

    public function test_results_are_paginated_newest_first_across_types(): void
    {
        // 25件のうち、一番新しいのは学び共有、一番古いのは目標宣言
        GoalPost::factory()->create(['title' => '一番古い投稿', 'created_at' => now()->subDays(30)]);
        KnowledgePost::factory()->count(23)->create(['created_at' => now()->subDays(10)]);
        KnowledgePost::factory()->create(['title' => '一番新しい投稿', 'created_at' => now()]);

        $this->actingAs($this->user)
            ->get(route('posts.search'))
            ->assertSee('25件')
            ->assertSee('一番新しい投稿')
            ->assertDontSee('一番古い投稿');

        $this->actingAs($this->user)
            ->get(route('posts.search', ['page' => 2]))
            ->assertSee('一番古い投稿')
            ->assertDontSee('一番新しい投稿');
    }
}
