<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/home')->assertRedirect(route('login'));
        $this->get('/admin/dashboard')->assertRedirect(route('login'));
    }

    public function test_general_user_can_access_home(): void
    {
        $this->actingAs(User::factory()->create())->get('/home')->assertOk();
    }

    public function test_general_user_can_not_access_admin_pages(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin/dashboard')->assertForbidden();
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $this->actingAs(User::factory()->admin()->create())->get('/admin/dashboard')->assertOk();
    }

    public function test_admin_can_not_access_general_user_pages(): void
    {
        $this->actingAs(User::factory()->admin()->create())->get('/home')->assertForbidden();
    }

    public function test_authenticated_user_visiting_login_is_redirected_to_role_home(): void
    {
        $this->actingAs(User::factory()->create())->get('/login')->assertRedirect(route('home', absolute: false));
        $this->actingAs(User::factory()->admin()->create())->get('/login')->assertRedirect(route('admin.dashboard', absolute: false));
    }
}
