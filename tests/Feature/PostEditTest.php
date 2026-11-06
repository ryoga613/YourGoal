<?php

namespace Tests\Feature;

use App\Enums\GoalPostStatus;
use App\Enums\PostType;
use App\Models\AchievementReport;
use App\Models\Comment;
use App\Models\GoalPost;
use App\Models\ProgressReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * 自分の投稿の編集・更新・削除（本人かどうかの確認はポリシーでまとめて実装するので、ここでは扱わない）
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

    // ---- 編集フォーム ----

    #[DataProvider('postTypes')]
    public function test_edit_form_shows_current_values(PostType $type): void
    {
        $post = $type->modelClass()::factory()->for($this->user)->create(['title' => '今のタイトル', 'body' => '今の本文']);

        $this->actingAs($this->user)
            ->get(route('posts.edit', $this->params($type, $post->id)))
            ->assertOk()
            ->assertSee('今のタイトル')
            ->assertSee('今の本文')
            ->assertSee(route('posts.update', $this->params($type, $post->id)), false);
    }

    public function test_edit_form_returns_404_for_missing_post(): void
    {
        $this->actingAs($this->user)
            ->get(route('posts.edit', ['type' => 'goal', 'id' => 999]))
            ->assertNotFound();
    }

    public function test_post_detail_shows_edit_and_delete_only_to_owner(): void
    {
        $post = GoalPost::factory()->for($this->user)->create();
        $editUrl = route('posts.edit', $this->params(PostType::Goal, $post->id));

        $this->actingAs($this->user)->get(route('posts.show', $this->params(PostType::Goal, $post->id)))
            ->assertSee($editUrl, false);

        $this->actingAs(User::factory()->create())->get(route('posts.show', $this->params(PostType::Goal, $post->id)))
            ->assertDontSee($editUrl, false);
    }

    // ---- 更新 ----

    #[DataProvider('postTypes')]
    public function test_owner_can_update_title_body_and_image(PostType $type): void
    {
        $post = $type->modelClass()::factory()->for($this->user)->create(['image_path' => 'old/old.png']);
        Storage::disk('public')->put('old/old.png', 'old');

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
        $this->assertStringStartsWith($type->imageDirectory().'/', $post->image_path);
        Storage::disk('public')->assertExists($post->image_path);
        Storage::disk('public')->assertMissing('old/old.png');
    }

    public function test_image_is_kept_when_not_selected(): void
    {
        $post = GoalPost::factory()->for($this->user)->create(['image_path' => 'goal_images/keep.png']);

        $this->actingAs($this->user)
            ->patch(route('posts.update', $this->params(PostType::Goal, $post->id)), ['title' => '新しいタイトル', 'body' => '']);

        $post->refresh();
        $this->assertSame('goal_images/keep.png', $post->image_path);
        $this->assertNull($post->body);
    }

    public function test_title_is_required_except_for_progress_report(): void
    {
        $post = GoalPost::factory()->for($this->user)->create(['title' => '元のタイトル']);

        $this->actingAs($this->user)
            ->patch(route('posts.update', $this->params(PostType::Goal, $post->id)), ['title' => '', 'body' => '本文'])
            ->assertSessionHasErrors(['title' => 'タイトルを入力してください。']);

        $this->assertSame('元のタイトル', $post->fresh()->title);
    }

    public function test_progress_report_needs_title_body_or_image(): void
    {
        $post = ProgressReport::factory()->for($this->user)->create(['image_path' => null]);

        $this->actingAs($this->user)
            ->patch(route('posts.update', $this->params(PostType::Progress, $post->id)), ['title' => '', 'body' => ''])
            ->assertSessionHasErrors(['title' => 'タイトル・本文・画像のいずれかを入力してください。']);
    }

    public function test_progress_report_with_existing_image_can_clear_title_and_body(): void
    {
        $post = ProgressReport::factory()->for($this->user)->create(['image_path' => 'progress_images/a.png']);

        $this->actingAs($this->user)
            ->patch(route('posts.update', $this->params(PostType::Progress, $post->id)), ['title' => '', 'body' => ''])
            ->assertSessionHasNoErrors();

        $post->refresh();
        $this->assertNull($post->title);
        $this->assertNull($post->body);
        $this->assertSame('progress_images/a.png', $post->image_path);
    }

    // ---- 削除 ----

    #[DataProvider('postTypes')]
    public function test_owner_can_delete_post_with_comments_and_image(PostType $type): void
    {
        $post = $type->modelClass()::factory()->for($this->user)->create(['image_path' => 'x/delete.png']);
        Storage::disk('public')->put('x/delete.png', 'img');
        Comment::factory()->create([$type->foreignKey() => $post->id]);

        $this->actingAs($this->user)
            ->delete(route('posts.destroy', $this->params($type, $post->id)))
            ->assertRedirect(route('users.show', $this->user))
            ->assertSessionHas('status', '投稿を削除しました。');

        $this->assertModelMissing($post);
        $this->assertSame(0, Comment::count());
        Storage::disk('public')->assertMissing('x/delete.png');
    }

    public function test_deleting_goal_also_deletes_its_reports(): void
    {
        $goal = GoalPost::factory()->for($this->user)->create();
        ProgressReport::factory()->for($this->user)->create(['goal_post_id' => $goal->id]);
        AchievementReport::factory()->for($this->user)->create(['goal_post_id' => $goal->id]);

        $this->actingAs($this->user)->delete(route('posts.destroy', $this->params(PostType::Goal, $goal->id)));

        $this->assertSame(0, ProgressReport::count());
        $this->assertSame(0, AchievementReport::count());
    }

    public function test_deleting_achievement_report_returns_goal_to_in_progress(): void
    {
        $goal = GoalPost::factory()->for($this->user)->create(['status' => GoalPostStatus::Achieved]);
        $report = AchievementReport::factory()->for($this->user)->create(['goal_post_id' => $goal->id]);

        $this->actingAs($this->user)->delete(route('posts.destroy', $this->params(PostType::Achievement, $report->id)));

        $this->assertModelMissing($report);
        $this->assertSame(GoalPostStatus::InProgress, $goal->fresh()->status);
    }

    public function test_goal_stays_achieved_while_other_achievement_reports_remain(): void
    {
        $goal = GoalPost::factory()->for($this->user)->create(['status' => GoalPostStatus::Achieved]);
        [$report] = AchievementReport::factory()->for($this->user)->count(2)->create(['goal_post_id' => $goal->id]);

        $this->actingAs($this->user)->delete(route('posts.destroy', $this->params(PostType::Achievement, $report->id)));

        $this->assertSame(GoalPostStatus::Achieved, $goal->fresh()->status);
    }

    public function test_guest_cannot_edit_update_or_delete(): void
    {
        $post = GoalPost::factory()->create();
        $params = $this->params(PostType::Goal, $post->id);

        $this->get(route('posts.edit', $params))->assertRedirect(route('login'));
        $this->patch(route('posts.update', $params), ['title' => 'x'])->assertRedirect(route('login'));
        $this->delete(route('posts.destroy', $params))->assertRedirect(route('login'));

        $this->assertModelExists($post);
    }
}
