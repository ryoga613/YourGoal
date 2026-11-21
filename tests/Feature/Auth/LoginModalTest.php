<?php

namespace Tests\Feature\Auth;

use App\Models\GoalPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

class LoginModalTest extends TestCase
{
    use RefreshDatabase;

    #[TestDox('ゲストがホームを開くと、ログインモーダルが読み込まれ、投稿作成などのリンクにモーダルを開く印が付く')]
    public function test_guest_home_has_login_modal(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('id="login-modal"', false)
            ->assertSee('href="'.route('posts.create').'" data-requires-login', false)
            ->assertSee('href="'.route('profile.edit').'" data-requires-login', false);
    }

    #[TestDox('ゲストが投稿詳細を開くと、いいね・コメントのフォームにモーダルを開く印が付く')]
    public function test_guest_post_show_marks_like_and_comment_forms(): void
    {
        $goal = GoalPost::factory()->create();

        $response = $this->get(route('posts.show', ['type' => 'goal', 'id' => $goal->id]))->assertOk();

        $response->assertSee('<form data-requires-login method="POST" action="'.route('likes.store', ['type' => 'goal', 'id' => $goal->id]).'"', false);
        $response->assertSee('<form data-requires-login method="POST" action="'.route('comments.store', ['type' => 'goal', 'id' => $goal->id]).'"', false);
        // ログインに成功したら、このページに戻ってくる
        $response->assertSee('name="redirect_to" value="/posts/goal/'.$goal->id.'"', false);
    }

    #[TestDox('ログイン中のユーザーにはログインモーダルを出さない')]
    public function test_authenticated_user_does_not_get_login_modal(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('home'))
            ->assertOk()
            ->assertDontSee('id="login-modal"', false);
    }

    #[TestDox('ログイン・新規登録の画面にはログインモーダルを出さない')]
    public function test_auth_screens_do_not_include_login_modal(): void
    {
        foreach (['/login', '/register', '/admin/login', '/admin/register'] as $path) {
            $this->get($path)->assertOk()->assertDontSee('id="login-modal"', false);
        }
    }

    #[TestDox('モーダルからログインすると、モーダルを開いたページに戻る')]
    public function test_login_from_modal_returns_to_page(): void
    {
        $user = User::factory()->create();
        $goal = GoalPost::factory()->create();
        $path = '/posts/goal/'.$goal->id;

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'login_modal' => '1',
            'redirect_to' => $path,
        ])->assertRedirect($path);

        $this->assertAuthenticatedAs($user);
    }

    #[TestDox('戻り先が別のサイトを指していたら、戻らずにホームへ移る')]
    #[DataProvider('unsafeRedirects')]
    public function test_unsafe_redirect_is_ignored(string $redirectTo): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'redirect_to' => $redirectTo,
        ])->assertRedirect(route('home', absolute: false));
    }

    public static function unsafeRedirects(): array
    {
        return [
            'スキーム付きURL' => ['https://evil.example/'],
            'プロトコル相対URL' => ['//evil.example/'],
            'バックスラッシュ' => ['/\\evil.example/'],
        ];
    }

    #[TestDox('モーダルからのログインに失敗すると、元のページに戻ってモーダルにエラーを出す')]
    public function test_failed_login_from_modal_reopens_modal_with_error(): void
    {
        $user = User::factory()->create();

        $this->from(route('home'))
            ->post('/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
                'login_modal' => '1',
                'redirect_to' => '/',
            ])->assertRedirect(route('home'));

        $this->get(route('home'))
            ->assertSee('認証に失敗しました。')
            ->assertSee('value="'.$user->email.'"', false)
            ->assertSee('openLoginModal();', false);

        $this->assertGuest();
    }
}
