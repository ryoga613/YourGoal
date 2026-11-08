<?php

namespace Tests\Feature;

use App\Enums\GoalPostStatus;
use App\Models\GoalPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GoalStatusTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    private function goal(GoalPostStatus $status, ?User $owner = null): GoalPost
    {
        return GoalPost::factory()->for($owner ?? $this->user)->create(['status' => $status]);
    }

    /**
     * 目標の詳細画面（投稿詳細に統合）
     */
    private function showUrl(GoalPost $goal): string
    {
        return route('posts.show', ['type' => 'goal', 'id' => $goal->id]);
    }

    public static function changeableStatuses(): array
    {
        return [
            '検討中' => [GoalPostStatus::Considering],
            '挑戦中' => [GoalPostStatus::InProgress],
            '中止' => [GoalPostStatus::Cancelled],
        ];
    }

    #[DataProvider('changeableStatuses')]
    public function test_owner_can_change_status(GoalPostStatus $status): void
    {
        $from = $status === GoalPostStatus::InProgress ? GoalPostStatus::Considering : GoalPostStatus::InProgress;
        $goal = $this->goal($from);

        $this->actingAs($this->user)
            ->patch(route('goals.status.update', $goal), ['status' => $status->value])
            ->assertRedirect($this->showUrl($goal).'#goal-actions')
            ->assertSessionHas('goalStatus', "ステータスを「{$status->label()}」に変更しました。");

        $this->assertSame($status, $goal->fresh()->status);
    }

    public function test_cannot_change_to_achieved_directly(): void
    {
        $goal = $this->goal(GoalPostStatus::InProgress);

        $this->actingAs($this->user)
            ->from($this->showUrl($goal))
            ->patch(route('goals.status.update', $goal), ['status' => GoalPostStatus::Achieved->value])
            ->assertRedirect($this->showUrl($goal))
            ->assertSessionHasErrors(['status' => '選択したステータスには変更できません。']);

        $this->assertSame(GoalPostStatus::InProgress, $goal->fresh()->status);
    }

    public function test_status_is_required_and_must_be_valid(): void
    {
        $goal = $this->goal(GoalPostStatus::InProgress);

        $this->actingAs($this->user)
            ->patch(route('goals.status.update', $goal), [])
            ->assertSessionHasErrors(['status' => 'ステータスを選択してください。']);

        $this->actingAs($this->user)
            ->patch(route('goals.status.update', $goal), ['status' => 'unknown'])
            ->assertSessionHasErrors('status');

        $this->assertSame(GoalPostStatus::InProgress, $goal->fresh()->status);
    }

    public function test_achieved_goal_status_cannot_be_changed(): void
    {
        $goal = $this->goal(GoalPostStatus::Achieved);

        $this->actingAs($this->user)
            ->patch(route('goals.status.update', $goal), ['status' => GoalPostStatus::InProgress->value])
            ->assertForbidden();

        $this->assertSame(GoalPostStatus::Achieved, $goal->fresh()->status);
    }

    public function test_other_user_cannot_change_status(): void
    {
        $goal = $this->goal(GoalPostStatus::InProgress, User::factory()->create());

        $this->actingAs($this->user)
            ->patch(route('goals.status.update', $goal), ['status' => GoalPostStatus::Cancelled->value])
            ->assertForbidden();

        $this->assertSame(GoalPostStatus::InProgress, $goal->fresh()->status);
    }

    public function test_guest_cannot_change_status(): void
    {
        $goal = $this->goal(GoalPostStatus::InProgress);

        $this->patch(route('goals.status.update', $goal), ['status' => GoalPostStatus::Cancelled->value])
            ->assertRedirect(route('login'));

        $this->assertSame(GoalPostStatus::InProgress, $goal->fresh()->status);
    }

    public function test_status_form_is_shown_only_to_owner_of_unachieved_goal(): void
    {
        $goal = $this->goal(GoalPostStatus::InProgress);
        $action = route('goals.status.update', $goal);

        $this->actingAs($this->user)->get($this->showUrl($goal))
            ->assertOk()
            ->assertSee($action, false);

        $this->actingAs(User::factory()->create())->get($this->showUrl($goal))
            ->assertOk()
            ->assertDontSee($action, false);

        $achieved = $this->goal(GoalPostStatus::Achieved);
        $this->actingAs($this->user)->get($this->showUrl($achieved))
            ->assertOk()
            ->assertDontSee(route('goals.status.update', $achieved), false);
    }
}
