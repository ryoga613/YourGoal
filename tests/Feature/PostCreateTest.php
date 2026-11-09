<?php

namespace Tests\Feature;

use App\Enums\GoalPostStatus;
use App\Models\GoalPost;
use App\Models\KnowledgePost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 目標宣言・学び共有の作成
 */
class PostCreateTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->user = User::factory()->create();
    }

    // ---- 投稿の種類を選ぶ画面 ----

    #[TestDox('投稿の種類を選ぶ画面には、自分の検討中・挑戦中の目標だけが出る')]
    public function test_post_type_page_lists_only_own_unfinished_goals(): void
    {
        GoalPost::factory()->for($this->user)->create(['title' => '挑戦中の目標', 'status' => GoalPostStatus::InProgress]);
        GoalPost::factory()->for($this->user)->create(['title' => '検討中の目標', 'status' => GoalPostStatus::Considering]);
        GoalPost::factory()->for($this->user)->create(['title' => '達成した目標', 'status' => GoalPostStatus::Achieved]);
        GoalPost::factory()->for($this->user)->create(['title' => '中止した目標', 'status' => GoalPostStatus::Cancelled]);
        GoalPost::factory()->create(['title' => '他人の目標', 'status' => GoalPostStatus::InProgress]);

        $this->actingAs($this->user)
            ->get(route('posts.create'))
            ->assertOk()
            ->assertSee('挑戦中の目標')
            ->assertSee('検討中の目標')
            ->assertDontSee('達成した目標')
            ->assertDontSee('中止した目標')
            ->assertDontSee('他人の目標');
    }

    // ---- 目標宣言 ----

    #[TestDox('目標宣言の画面を開ける')]
    public function test_user_can_open_goal_form(): void
    {
        $this->actingAs($this->user)
            ->get(route('goals.create'))
            ->assertOk()
            ->assertSee(route('goals.store'), false);
    }

    #[TestDox('目標を宣言でき、挑戦中から始まる')]
    public function test_user_can_declare_goal_and_it_starts_in_progress(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('goals.store'), [
                'title' => '毎日30分走る',
                'body' => '体力をつけたい',
                'image' => UploadedFile::fake()->image('goal.png'),
            ]);

        $goal = GoalPost::sole();
        $response->assertRedirect(route('posts.show', ['type' => 'goal', 'id' => $goal->id]));

        $this->assertSame($this->user->id, $goal->user_id);
        $this->assertSame('毎日30分走る', $goal->title);
        $this->assertSame('体力をつけたい', $goal->body);
        $this->assertSame(GoalPostStatus::InProgress, $goal->status);
        $this->assertStringStartsWith('goal_images/', $goal->image_path);
        Storage::disk('public')->assertExists($goal->image_path);
    }

    #[TestDox('目標のタイトルは必須')]
    public function test_goal_title_is_required(): void
    {
        $this->actingAs($this->user)
            ->post(route('goals.store'), ['title' => '', 'body' => '本文だけ'])
            ->assertSessionHasErrors(['title' => 'タイトルを入力してください。']);

        $this->assertSame(0, GoalPost::count());
    }

    #[TestDox('目標の画像は画像ファイルだけ')]
    public function test_goal_image_must_be_an_image(): void
    {
        $this->actingAs($this->user)
            ->post(route('goals.store'), ['title' => '目標', 'image' => UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')])
            ->assertSessionHasErrors('image');

        $this->assertSame(0, GoalPost::count());
    }

    // ---- 学び共有 ----

    #[TestDox('学び共有の画面を開ける')]
    public function test_user_can_open_knowledge_form(): void
    {
        $this->actingAs($this->user)
            ->get(route('knowledge-posts.create'))
            ->assertOk()
            ->assertSee(route('knowledge-posts.store'), false);
    }

    #[TestDox('学びを共有できる')]
    public function test_user_can_share_knowledge(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('knowledge-posts.store'), ['title' => '人に説明すると覚える', 'body' => 'アウトプットが大事']);

        $knowledge = KnowledgePost::sole();
        $response->assertRedirect(route('posts.show', ['type' => 'knowledge', 'id' => $knowledge->id]));

        $this->assertSame($this->user->id, $knowledge->user_id);
        $this->assertSame('人に説明すると覚える', $knowledge->title);
        $this->assertSame('アウトプットが大事', $knowledge->body);
        $this->assertNull($knowledge->image_path);
    }

    #[TestDox('学び共有のタイトルは必須')]
    public function test_knowledge_title_is_required(): void
    {
        $this->actingAs($this->user)
            ->post(route('knowledge-posts.store'), ['title' => ''])
            ->assertSessionHasErrors(['title' => 'タイトルを入力してください。']);

        $this->assertSame(0, KnowledgePost::count());
    }

    // ---- ゲスト・管理者 ----

    #[TestDox('ゲストはログイン画面に送られる')]
    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('posts.create'))->assertRedirect(route('login'));
        $this->post(route('goals.store'), ['title' => '目標'])->assertRedirect(route('login'));
        $this->post(route('knowledge-posts.store'), ['title' => '学び'])->assertRedirect(route('login'));

        $this->assertSame(0, GoalPost::count() + KnowledgePost::count());
    }

    #[TestDox('管理者は投稿を作れない')]
    public function test_admin_cannot_create_posts(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('goals.store'), ['title' => '目標'])->assertForbidden();
        $this->actingAs($admin)->post(route('knowledge-posts.store'), ['title' => '学び'])->assertForbidden();

        $this->assertSame(0, GoalPost::count() + KnowledgePost::count());
    }
}
