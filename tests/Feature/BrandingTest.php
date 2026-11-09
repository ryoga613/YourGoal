<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * サービス名の表示：どの画面も YourGoal になっていて、旧サービス名 Growly が残っていない
 */
class BrandingTest extends TestCase
{
    use RefreshDatabase;

    private function assertNoOldName(TestResponse $response): TestResponse
    {
        // assertDontSee は大文字・小文字を区別するので、両方の書き方を確かめる
        return $response->assertDontSee('Growly')->assertDontSee('growly');
    }

    public static function guestPages(): array
    {
        return [
            'ログイン' => ['login', 'ログイン'],
            '新規登録' => ['register', '新規登録'],
        ];
    }

    #[TestDox('ログイン・新規登録画面のタイトルとロゴが YourGoal になっている')]
    #[DataProvider('guestPages')]
    public function test_guest_pages_show_your_goal(string $route, string $title): void
    {
        $response = $this->get(route($route))
            ->assertOk()
            ->assertSee('<title>'.$title.' | YourGoal</title>', false)
            ->assertSee('<span class="brand-mark">Y</span>YourGoal', false);

        $this->assertNoOldName($response);
    }

    #[TestDox('ログイン後の画面のヘッダーとフッターが YourGoal になっている')]
    public function test_layout_header_and_footer_show_your_goal(): void
    {
        $response = $this->actingAs(User::factory()->create())
            ->get(route('home'))
            ->assertOk()
            ->assertSee('<title>ホーム | YourGoal</title>', false)
            ->assertSee('<span class="brand-mark">Y</span>YourGoal</a>', false)
            ->assertSee('© '.date('Y').' YourGoal — 小さな一歩を、みんなの成長に。');

        $this->assertNoOldName($response);
    }

    #[TestDox('管理者ダッシュボードの説明文が YourGoal になっている')]
    public function test_admin_dashboard_shows_your_goal(): void
    {
        $response = $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('YourGoalの利用状況を確認できます。');

        $this->assertNoOldName($response);
    }
}
