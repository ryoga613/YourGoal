<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    #[TestDox('ログイン画面が表示される')]
    public function test_login_screen_can_be_rendered(): void
    {
        $this->get('/login')->assertOk();
    }

    #[TestDox('一般ユーザーはログイン後ホームに移る')]
    public function test_general_user_is_redirected_to_home_after_login(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('home', absolute: false));

        $this->assertAuthenticatedAs($user);
    }

    #[TestDox('管理者はログイン後に管理画面に移る')]
    public function test_admin_is_redirected_to_admin_dashboard_after_login(): void
    {
        $admin = User::factory()->admin()->create();

        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard', absolute: false));

        $this->assertAuthenticatedAs($admin);
    }

    #[TestDox('パスワードが違うとログインできない')]
    public function test_user_can_not_login_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password'])
            ->assertSessionHasErrors(['email' => '認証に失敗しました。']);

        $this->assertGuest();
    }

    #[TestDox('ログアウトできる')]
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout')->assertRedirect('/');

        $this->assertGuest();
    }

    #[TestDox('新規登録したユーザーは一般ユーザーになる')]
    public function test_registered_user_becomes_general_user(): void
    {
        $this->post('/register', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'admin',
        ])->assertRedirect(route('home', absolute: false));

        $this->assertSame(UserRole::User, User::where('email', 'new@example.com')->first()->role);
    }
}
