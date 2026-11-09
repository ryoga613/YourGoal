<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    #[TestDox('ゲストはログイン画面に送られる')]
    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
        $this->get('/admin')->assertRedirect(route('login'));
    }

    #[TestDox('一般ユーザーはホームを開ける')]
    public function test_general_user_can_access_home(): void
    {
        $this->actingAs(User::factory()->create())->get('/')->assertOk();
    }

    #[TestDox('一般ユーザーは管理画面を開けない')]
    public function test_general_user_can_not_access_admin_pages(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
    }

    #[TestDox('管理者は管理画面を開ける')]
    public function test_admin_can_access_admin_dashboard(): void
    {
        $this->actingAs(User::factory()->admin()->create())->get('/admin')->assertOk();
    }

    #[TestDox('管理者は一般ユーザー向けの画面を開けない')]
    public function test_admin_can_not_access_general_user_pages(): void
    {
        $this->actingAs(User::factory()->admin()->create())->get('/')->assertForbidden();
    }

    #[TestDox('ログイン済みでログイン画面を開くと、役割ごとのトップに送られる')]
    public function test_authenticated_user_visiting_login_is_redirected_to_role_home(): void
    {
        $this->actingAs(User::factory()->create())->get('/login')->assertRedirect(route('home', absolute: false));
        $this->actingAs(User::factory()->admin()->create())->get('/login')->assertRedirect(route('admin.dashboard', absolute: false));
    }
}
