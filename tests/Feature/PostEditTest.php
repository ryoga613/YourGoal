<?php

namespace Tests\Feature;

use App\Enums\GoalPostStatus;
use App\Enums\PostType;
use App\Models\AchievementReport;
use App\Models\Comment;
use App\Models\GoalPost;
use App\Models\PostLike;
use App\Models\ProgressReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 自分の投稿の編集・更新・削除（4種類共通の /posts/{type}/{id}）
 */
class PostEditTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->user = User::factory()->create();
    }

    public static function postTypes(): array
    {
        return array_map(fn (PostType $type) => [$type], array_combine(
            array_map(fn (PostType $type) => $type->value, PostType::cases()),
            PostType::cases(),
        ));
    }

    private function params(PostType $type, int $id): array
    {
        return ['type' => $type->value, 'id' => $id];
    }

    /**
     * ログイン中のユーザーが投稿した、指定の種類の投稿
     */
    private function ownPost(PostType $type, array $attributes = [])
    {
        return $type->modelClass()::factory()->for($this->user)->create($attributes);
    }

    // ---- 編集画面 ----

    #[TestDox('本人は今の値が入った編集画面を開ける')]
    #[DataProvider('postTypes')]
    public function test_owner_sees_edit_form_with_current_values(PostType $type): void
    {
        $post = $this->ownPost($type, ['title' => '今のタイトル', 'body' => '今の本文']);

        $this->actingAs($this->user)
            ->get(route('posts.edit', $this->params($type, $post->id)))
            ->assertOk()
            ->assertSee('今のタイトル')
            ->assertSee('今の本文')
            ->assertSee(route('posts.update', $this->params($type, $post->id)), false);
    }

    #[TestDox('他人は編集画面を開けない')]
    #[DataProvider('postTypes')]
    public function test_other_user_cannot_open_edit_form(PostType $type): void
    {
        $post = $type->modelClass()::factory()->create();

        $this->actingAs($this->user)
            ->get(route('posts.edit', $this->params($type, $post->id)))
            ->assertForbidden();
    }

    #[TestDox('存在しない投稿の編集画面は404になる')]
    public function test_edit_form_returns_404_for_missing_post(): void
    {
        $this->actingAs($this->user)
            ->get(route('posts.edit', ['type' => 'goal', 'id' => 999]))
            ->assertNotFound();
    }

    // ---- 更新 ----

    #[TestDox('本人はタイトル・本文を更新し、画像を差し替えられる')]
    #[DataProvider('postTypes')]
    public function test_owner_can_update_title_body_and_replace_image(PostType $type): void
    {
        Storage::disk('public')->put('old/old.png', 'old');
        $post = $this->ownPost($type, ['image_path' => 'old/old.png']);

        $this->actingAs($this->user)
            ->patch(route('posts.update', $this->params($type, $post->id)), [
                'title' => '新しいタイトル',
                'body' => '新しい本文',
                'image' => UploadedFile::fake()->image('new.png'),
            ])
            ->assertRedirect(route('posts.show', $this->params($type, $post->id)))
            ->assertSessionHas('status', '投稿を更新しました。');

        $post->refresh();
        $this->assertSame('新しいタイトル', $post->title);
        $this->assertSame('新しい本文', $post->body);
        $this->assertStringStartsWith($type->value.'_images/', $post->image_path);
        Storage::disk('public')->assertExists($post->image_path);
        // 差し替えた古い画像ファイルは消える
        Storage::disk('public')->assertMissing('old/old.png');
    }

    #[TestDox('他人は更新できない（入力が不正でも先に403）')]
    #[DataProvider('postTypes')]
    public function test_other_user_cannot_update(PostType $type): void
    {
        $post = $type->modelClass()::factory()->create(['title' => '元のタイトル']);

        // 入力が不正でも、先に権限で弾かれる
        $this->actingAs($this->user)
            ->patch(route('posts.update', $this->params($type, $post->id)), ['title' => ''])
            ->assertForbidden();

        $this->assertSame('元のタイトル', $post->fresh()->title);
    }

    #[TestDox('新しい画像を選ばなければ今の画像が残る')]
    public function test_image_is_kept_when_no_new_image_is_selected(): void
    {
        $post = $this->ownPost(PostType::Goal, ['image_path' => 'goal_images/keep.png']);

        $this->actingAs($this->user)
            ->patch(route('posts.update', $this->params(PostType::Goal, $post->id)), ['title' => '新しいタイトル', 'body' => '']);

        $post->refresh();
        $this->assertSame('goal_images/keep.png', $post->image_path);
        $this->assertNull($post->body);
    }

    #[TestDox('進捗報告以外はタイトルが必須')]
    public function test_title_is_required_except_for_progress_report(): void
    {
        $post = $this->ownPost(PostType::Goal, ['title' => '元のタイトル']);

        $this->actingAs($this->user)
            ->patch(route('posts.update', $this->params(PostType::Goal, $post->id)), ['title' => '', 'body' => '本文'])
            ->assertSessionHasErrors(['title' => 'タイトルを入力してください。']);

        $this->assertSame('元のタイトル', $post->fresh()->title);
    }

    #[TestDox('進捗報告はタイトル・本文・画像のどれかが必要')]
    public function test_progress_report_needs_title_body_or_image(): void
    {
        $post = $this->ownPost(PostType::Progress, ['image_path' => null]);

        $this->actingAs($this->user)
            ->patch(route('posts.update', $this->params(PostType::Progress, $post->id)), ['title' => '', 'body' => ''])
            ->assertSessionHasErrors(['title' => 'タイトル・本文・画像のいずれかを入力してください。']);
    }

    #[TestDox('画像がある進捗報告はタイトルと本文を空にできる')]
    public function test_progress_report_with_existing_image_can_clear_title_and_body(): void
    {
        $post = $this->ownPost(PostType::Progress, ['image_path' => 'progress_images/a.png']);

        $this->actingAs($this->user)
            ->patch(route('posts.update', $this->params(PostType::Progress, $post->id)), ['title' => '', 'body' => ''])
            ->assertSessionHasNoErrors();

        $post->refresh();
        $this->assertNull($post->title);
        $this->assertNull($post->body);
        $this->assertSame('progress_images/a.png', $post->image_path);
    }

    // ---- 削除 ----

    #[TestDox('本人が投稿を削除すると、コメント・いいね・画像も消える')]
    #[DataProvider('postTypes')]
    public function test_owner_can_delete_post_with_its_comments_likes_and_image(PostType $type): void
    {
        Storage::disk('public')->put('x/delete.png', 'img');
        $post = $this->ownPost($type, ['image_path' => 'x/delete.png']);
        Comment::factory()->forPost($post)->create();
        PostLike::create([$type->foreignKey() => $post->id, 'user_id' => User::factory()->create()->id]);

        $this->actingAs($this->user)
            ->delete(route('posts.destroy', $this->params($type, $post->id)))
            ->assertRedirect(route('users.show', $this->user))
            ->assertSessionHas('status', '投稿を削除しました。');

        $this->assertModelMissing($post);
        $this->assertSame(0, Comment::count());
        $this->assertSame(0, PostLike::count());
        Storage::disk('public')->assertMissing('x/delete.png');
    }

    #[TestDox('他人は削除できない')]
    #[DataProvider('postTypes')]
    public function test_other_user_cannot_delete(PostType $type): void
    {
        $post = $type->modelClass()::factory()->create();

        $this->actingAs($this->user)
            ->delete(route('posts.destroy', $this->params($type, $post->id)))
            ->assertForbidden();

        $this->assertModelExists($post);
    }

    #[TestDox('目標を削除すると、報告とその画像も消える')]
    public function test_deleting_goal_also_deletes_its_reports_and_their_images(): void
    {
        $goal = $this->ownPost(PostType::Goal, ['image_path' => null]);
        Storage::disk('public')->put('progress_images/p.png', 'img');
        Storage::disk('public')->put('achievement_images/a.png', 'img');
        ProgressReport::factory()->for($this->user)->create(['goal_post_id' => $goal->id, 'image_path' => 'progress_images/p.png']);
        AchievementReport::factory()->for($this->user)->create(['goal_post_id' => $goal->id, 'image_path' => 'achievement_images/a.png']);

        $this->actingAs($this->user)->delete(route('posts.destroy', $this->params(PostType::Goal, $goal->id)));

        $this->assertSame(0, ProgressReport::count());
        $this->assertSame(0, AchievementReport::count());
        Storage::disk('public')->assertMissing(['progress_images/p.png', 'achievement_images/a.png']);
    }

    #[TestDox('最後の達成報告を消すと、目標が挑戦中に戻る')]
    public function test_deleting_last_achievement_report_returns_goal_to_in_progress(): void
    {
        $goal = $this->ownPost(PostType::Goal, ['status' => GoalPostStatus::Achieved]);
        $report = AchievementReport::factory()->for($this->user)->create(['goal_post_id' => $goal->id]);

        $this->actingAs($this->user)->delete(route('posts.destroy', $this->params(PostType::Achievement, $report->id)));

        $this->assertModelMissing($report);
        $this->assertSame(GoalPostStatus::InProgress, $goal->fresh()->status);
    }

    #[TestDox('ほかの達成報告が残っていれば、目標は達成済みのまま')]
    public function test_goal_stays_achieved_while_other_achievement_reports_remain(): void
    {
        $goal = $this->ownPost(PostType::Goal, ['status' => GoalPostStatus::Achieved]);
        [$report] = AchievementReport::factory()->for($this->user)->count(2)->create(['goal_post_id' => $goal->id]);

        $this->actingAs($this->user)->delete(route('posts.destroy', $this->params(PostType::Achievement, $report->id)));

        $this->assertSame(GoalPostStatus::Achieved, $goal->fresh()->status);
    }

    // ---- ゲスト・管理者 ----

    #[TestDox('ゲストはログイン画面に送られる')]
    public function test_guest_is_redirected_to_login(): void
    {
        $post = GoalPost::factory()->create();
        $params = $this->params(PostType::Goal, $post->id);

        $this->get(route('posts.edit', $params))->assertRedirect(route('login'));
        $this->patch(route('posts.update', $params), ['title' => 'x'])->assertRedirect(route('login'));
        $this->delete(route('posts.destroy', $params))->assertRedirect(route('login'));

        $this->assertModelExists($post);
    }

    #[TestDox('管理者は一般向けの編集・削除を使えない')]
    public function test_admin_cannot_use_general_edit_routes(): void
    {
        $post = GoalPost::factory()->create();
        $params = $this->params(PostType::Goal, $post->id);
        $admin = User::factory()->admin()->create();

        // 管理者の削除は管理画面から行う
        $this->actingAs($admin)->delete(route('posts.destroy', $params))->assertForbidden();

        $this->assertModelExists($post);
    }
}
