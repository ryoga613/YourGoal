<?php

namespace Tests\Feature;

use App\Models\AchievementReport;
use App\Models\GoalPost;
use App\Models\KnowledgePost;
use App\Models\ProgressReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * プロフィール画面のビュー（コントローラ実装前でも、ビュー単体で表示を確認する）
 */
class ProfileViewTest extends TestCase
{
    use RefreshDatabase;

    #[TestDox('プロフィールにユーザー情報・目標・達成・投稿が表示される')]
    public function test_profile_shows_user_info_goals_achievements_and_posts(): void
    {
        $user = User::factory()->create(['name' => '田中一郎', 'bio' => '毎日コツコツ']);
        $goal = GoalPost::factory()->for($user)->create(['title' => '英検2級に合格する', 'status' => 'achieved']);
        $achievement = AchievementReport::factory()->for($goal, 'goal_post')->create(['title' => '合格できました']);
        $knowledge = KnowledgePost::factory()->for($user)->create(['title' => '単語の覚え方']);
        $user->loadCount(['goal_posts', 'progress_reports', 'achievement_reports', 'knowledge_posts']);

        $this->actingAs(User::factory()->create())
            ->view('profile.show', [
                'user' => $user,
                'goals' => collect([$goal]),
                'achievements' => collect([$achievement->load('goal_post')]),
                'posts' => collect([$knowledge, $achievement, $goal]),
            ])
            ->assertSee(['田中一郎', '毎日コツコツ'])
            // 目標1・達成1・学び1 の合計（件数は <strong> で囲んでいるので、タグを除いた文字で確認する）
            ->assertSeeText('3 投稿')
            ->assertSee('達成済み')
            ->assertSee('🎉 英検2級に合格する')
            ->assertSee(route('posts.show', ['type' => 'knowledge', 'id' => $knowledge->id]), false)
            ->assertSee(route('posts.show', ['type' => 'achievement', 'id' => $achievement->id]), false)
            // 他人のプロフィールには編集ボタンを出さない
            ->assertDontSee('プロフィールを編集');
    }

    #[TestDox('自分のプロフィールには編集ボタンと、空のときの案内が出る')]
    public function test_own_profile_shows_edit_button_and_empty_states(): void
    {
        $user = User::factory()->create(['bio' => null]);

        $this->actingAs($user)
            ->view('profile.show', ['user' => $user])
            ->assertSee('プロフィールを編集')
            ->assertSee(route('profile.edit'), false)
            ->assertSee('自己紹介がまだありません')
            ->assertSee(['まだ目標はありません。', 'まだ達成した目標はありません。', 'まだ投稿はありません。']);
    }

    #[TestDox('プロフィール編集画面に今の画像と、画像の入力エラーが出る')]
    public function test_edit_form_shows_current_image_and_image_error(): void
    {
        $user = User::factory()->create(['profile_image_path' => 'profile-images/me.png']);

        // 画像の入力欄の名前は profile_image_path（UpdateProfileRequest のルールと揃えている）
        $this->actingAs($user)
            ->withViewErrors(['profile_image_path' => '画像ファイルを選択してください。'])
            ->view('profile.edit')
            ->assertSee(asset('storage/profile-images/me.png'), false)
            ->assertSee('画像ファイルを選択してください。');
    }

    #[TestDox('タイトルのない進捗報告もプロフィールに一覧表示される')]
    public function test_progress_report_without_title_is_listed(): void
    {
        $user = User::factory()->create();
        $progress = ProgressReport::factory()->for(GoalPost::factory()->for($user), 'goal_post')->create(['title' => null, 'body' => '今日も続けた']);

        $this->actingAs($user)
            ->view('profile.show', ['user' => $user, 'posts' => collect([$progress])])
            ->assertSee('進捗報告')
            ->assertSee('今日も続けた');
    }
}
