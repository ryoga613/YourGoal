<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    #[TestDox('管理者のログイン画面が表示される')]
    public function test_admin_login_screen_can_be_rendered(): void
    {
        $this->get('/admin/login')->assertOk()->assertSee('管理者ログイン');
    }

    #[TestDox('管理者は管理者のログイン画面からログインすると管理画面に移る')]
    public function test_admin_can_login_and_is_redirected_to_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard', absolute: false));

        $this->assertAuthenticatedAs($admin);
    }

    #[TestDox('一般ユーザーは管理者のログイン画面からログインできない')]
    public function test_general_user_can_not_login_from_admin_login(): void
    {
        $user = User::factory()->create();

        $this->from('/admin/login')
            ->post('/admin/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect('/admin/login')
            ->assertSessionHasErrors(['email' => '認証に失敗しました。']);

        $this->assertGuest();
    }

    #[TestDox('パスワードが違うと管理者もログインできない')]
    public function test_admin_can_not_login_with_invalid_password(): void
    {
        $admin = User::factory()->admin()->create();

        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'wrong-password'])
            ->assertSessionHasErrors(['email' => '認証に失敗しました。']);

        $this->assertGuest();
    }

    #[TestDox('管理者の新規登録画面が表示される')]
    public function test_admin_register_screen_can_be_rendered(): void
    {
        $this->get('/admin/register')->assertOk()->assertSee('管理者の新規登録');
    }

    #[TestDox('管理者の新規登録をすると管理者としてログインし、管理画面に移る')]
    public function test_admin_can_register(): void
    {
        $this->post('/admin/register', [
            'name' => 'New Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $admin = User::where('email', 'admin@example.com')->first();
        $this->assertSame(UserRole::Admin, $admin->role);
        $this->assertAuthenticatedAs($admin);
    }

    #[TestDox('管理者の新規登録でも入力チェックを行う')]
    public function test_admin_register_validates_input(): void
    {
        $this->post('/admin/register', [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'password',
            'password_confirmation' => 'different',
        ])->assertSessionHasErrors(['name', 'email', 'password']);

        $this->assertGuest();
    }

    #[TestDox('ログイン済みで管理者のログイン・新規登録画面を開くと、役割ごとのトップに送られる')]
    public function test_authenticated_user_visiting_admin_auth_is_redirected_to_role_home(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get('/admin/login')->assertRedirect(route('admin.dashboard', absolute: false));
        $this->actingAs($admin)->get('/admin/register')->assertRedirect(route('admin.dashboard', absolute: false));

        $this->actingAs(User::factory()->create())->get('/admin/login')->assertRedirect(route('home', absolute: false));
    }
}
