<?php

namespace Tests\Feature;

use App\Enums\GoalPostStatus;
use App\Models\GoalPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 目標への進捗報告・達成報告（目標を宣言した本人だけ。達成報告は検討中・挑戦中の目標だけ）
 */
class ReportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->user = User::factory()->create();
    }

    private function goal(GoalPostStatus $status, ?User $owner = null): GoalPost
    {
        return GoalPost::factory()->for($owner ?? $this->user)->create(['status' => $status]);
    }

    private function goalUrl(GoalPost $goal): string
    {
        return route('posts.show', ['type' => 'goal', 'id' => $goal->id]);
    }

    public static function allStatuses(): array
    {
        return [
            '挑戦中' => [GoalPostStatus::InProgress],
            '検討中' => [GoalPostStatus::Considering],
            '達成済み' => [GoalPostStatus::Achieved],
            '中止' => [GoalPostStatus::Cancelled],
        ];
    }

    public static function achievableStatuses(): array
    {
        return [
            '挑戦中' => [GoalPostStatus::InProgress],
            '検討中' => [GoalPostStatus::Considering],
        ];
    }

    public static function finishedStatuses(): array
    {
        return [
            '達成済み' => [GoalPostStatus::Achieved],
            '中止' => [GoalPostStatus::Cancelled],
        ];
    }

    // ---- 進捗報告 ----

    #[TestDox('本人は目標がどの状態でも進捗報告でき、目標の状態は変わらない')]
    #[DataProvider('allStatuses')]
    public function test_owner_can_report_progress_on_any_status(GoalPostStatus $status): void
    {
        $goal = $this->goal($status);

        $this->actingAs($this->user)
            ->get(route('progress-reports.create', $goal))
            ->assertOk();

        $this->actingAs($this->user)
            ->post(route('progress-reports.store', $goal), [
                'title' => '1週間続いた',
                'body' => '朝に走るのが習慣になった',
                'image' => UploadedFile::fake()->image('run.png'),
            ])
            ->assertRedirect($this->goalUrl($goal));

        $report = $goal->progress_reports()->sole();
        $this->assertSame($this->user->id, $report->user_id);
        $this->assertSame('1週間続いた', $report->title);
        $this->assertStringStartsWith('progress_images/', $report->image_path);
        // 進捗報告では目標の状態は変わらない
        $this->assertSame($status, $goal->fresh()->status);
    }

    #[TestDox('進捗報告はタイトル・本文・画像のどれかが必要')]
    public function test_progress_report_needs_title_body_or_image(): void
    {
        $goal = $this->goal(GoalPostStatus::InProgress);

        $this->actingAs($this->user)
            ->post(route('progress-reports.store', $goal), ['title' => '', 'body' => ''])
            ->assertSessionHasErrors(['title' => 'タイトル・本文・画像のいずれかを入力してください。']);

        $this->assertSame(0, $goal->progress_reports()->count());
    }

    #[TestDox('進捗報告は本文だけでもよい')]
    public function test_progress_report_can_be_body_only(): void
    {
        $goal = $this->goal(GoalPostStatus::InProgress);

        $this->actingAs($this->user)
            ->post(route('progress-reports.store', $goal), ['body' => '本文だけの報告'])
            ->assertSessionHasNoErrors();

        $this->assertNull($goal->progress_reports()->sole()->title);
    }

    #[TestDox('他人の目標には進捗報告できない')]
    public function test_other_user_cannot_report_progress(): void
    {
        $goal = $this->goal(GoalPostStatus::InProgress, User::factory()->create());

        $this->actingAs($this->user)->get(route('progress-reports.create', $goal))->assertForbidden();
        $this->actingAs($this->user)->post(route('progress-reports.store', $goal), ['title' => '進捗'])->assertForbidden();

        $this->assertSame(0, $goal->progress_reports()->count());
    }

    // ---- 達成報告 ----

    #[TestDox('本人が達成報告すると、目標が達成済みになる')]
    #[DataProvider('achievableStatuses')]
    public function test_owner_can_report_achievement_and_goal_becomes_achieved(GoalPostStatus $status): void
    {
        $goal = $this->goal($status);

        $this->actingAs($this->user)
            ->get(route('achievement-reports.create', $goal))
            ->assertOk();

        $response = $this->actingAs($this->user)
            ->post(route('achievement-reports.store', $goal), ['title' => '完走できた！', 'body' => '目標達成']);

        $report = $goal->achievement_reports()->sole();
        // 達成報告のあとは、作った達成報告の詳細に移る
        $response->assertRedirect(route('posts.show', ['type' => 'achievement', 'id' => $report->id]));
        $this->assertSame($this->user->id, $report->user_id);
        $this->assertSame('完走できた！', $report->title);
        $this->assertSame(GoalPostStatus::Achieved, $goal->fresh()->status);
    }

    #[TestDox('達成済み・中止の目標には達成報告できない')]
    #[DataProvider('finishedStatuses')]
    public function test_cannot_report_achievement_on_achieved_or_cancelled_goal(GoalPostStatus $status): void
    {
        $goal = $this->goal($status);

        $this->actingAs($this->user)->get(route('achievement-reports.create', $goal))->assertForbidden();
        $this->actingAs($this->user)->post(route('achievement-reports.store', $goal), ['title' => '達成'])->assertForbidden();

        $this->assertSame(0, $goal->achievement_reports()->count());
        $this->assertSame($status, $goal->fresh()->status);
    }

    #[TestDox('達成報告のタイトルは必須（エラー時は目標の状態も変わらない）')]
    public function test_achievement_title_is_required(): void
    {
        $goal = $this->goal(GoalPostStatus::InProgress);

        $this->actingAs($this->user)
            ->post(route('achievement-reports.store', $goal), ['title' => '', 'body' => '本文'])
            ->assertSessionHasErrors(['title' => 'タイトルを入力してください。']);

        // 入力エラーのときは目標の状態も変わらない
        $this->assertSame(0, $goal->achievement_reports()->count());
        $this->assertSame(GoalPostStatus::InProgress, $goal->fresh()->status);
    }

    #[TestDox('他人の目標には達成報告できない')]
    public function test_other_user_cannot_report_achievement(): void
    {
        $goal = $this->goal(GoalPostStatus::InProgress, User::factory()->create());

        $this->actingAs($this->user)->get(route('achievement-reports.create', $goal))->assertForbidden();
        $this->actingAs($this->user)->post(route('achievement-reports.store', $goal), ['title' => '達成'])->assertForbidden();

        $this->assertSame(0, $goal->achievement_reports()->count());
        $this->assertSame(GoalPostStatus::InProgress, $goal->fresh()->status);
    }

    // ---- ゲスト ----

    #[TestDox('ゲストはログイン画面に送られる')]
    public function test_guest_is_redirected_to_login(): void
    {
        $goal = $this->goal(GoalPostStatus::InProgress);

        $this->get(route('progress-reports.create', $goal))->assertRedirect(route('login'));
        $this->post(route('progress-reports.store', $goal), ['title' => '進捗'])->assertRedirect(route('login'));
        $this->get(route('achievement-reports.create', $goal))->assertRedirect(route('login'));
        $this->post(route('achievement-reports.store', $goal), ['title' => '達成'])->assertRedirect(route('login'));

        $this->assertSame(0, $goal->progress_reports()->count() + $goal->achievement_reports()->count());
    }
}
