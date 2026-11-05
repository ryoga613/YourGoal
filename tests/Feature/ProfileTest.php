<?php

namespace Tests\Feature;

use App\Models\GoalPost;
use App\Models\KnowledgePost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['name' => '元の名前', 'bio' => '元の自己紹介']);
    }

    // ---- 表示 ----

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('users.show', $this->user))->assertRedirect(route('login'));
    }

    public function test_profile_shows_post_count_and_10_posts_per_page(): void
    {
        KnowledgePost::factory()->for($this->user)->count(11)->create();
        GoalPost::factory()->for($this->user)->create(['title' => '一番新しい目標', 'created_at' => now()->addMinute()]);

        $this->actingAs(User::factory()->create())
            ->get(route('users.show', $this->user))
            ->assertOk()
            ->assertSeeText('12 投稿')
            ->assertSee('一番新しい目標')
            // 1ページ10件なので、12件だと2ページ目までになる（2件ずつだと6ページになってしまう）
            ->assertSee(route('users.show', $this->user).'?page=2', false)
            ->assertDontSee(route('users.show', $this->user).'?page=3', false);
    }

    // ---- 編集 ----

    public function test_edit_form_shows_current_values(): void
    {
        $this->actingAs($this->user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('元の名前')
            ->assertSee('元の自己紹介');
    }

    public function test_user_can_update_name_and_bio(): void
    {
        $this->actingAs($this->user)
            ->patch(route('profile.update'), ['name' => '新しい名前', 'bio' => '新しい自己紹介'])
            ->assertRedirect(route('users.show', $this->user))
            ->assertSessionHas('status', 'プロフィールを更新しました。');

        $this->user->refresh();
        $this->assertSame('新しい名前', $this->user->name);
        $this->assertSame('新しい自己紹介', $this->user->bio);
    }

    public function test_uploaded_image_is_stored_and_path_saved(): void
    {
        Storage::fake('public');

        $this->actingAs($this->user)
            ->patch(route('profile.update'), [
                'name' => '元の名前',
                'profile_image_path' => UploadedFile::fake()->image('me.png'),
            ])
            ->assertSessionHasNoErrors();

        $path = $this->user->fresh()->profile_image_path;
        $this->assertStringStartsWith('profile-images/', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_updating_without_image_keeps_current_image(): void
    {
        $this->user->update(['profile_image_path' => 'profile-images/current.png']);

        $this->actingAs($this->user)
            ->patch(route('profile.update'), ['name' => '新しい名前']);

        $this->assertSame('profile-images/current.png', $this->user->fresh()->profile_image_path);
    }

    public function test_svg_is_rejected(): void
    {
        Storage::fake('public');

        $this->actingAs($this->user)
            ->patch(route('profile.update'), [
                'name' => '元の名前',
                'profile_image_path' => UploadedFile::fake()->createWithContent('x.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
            ])
            ->assertSessionHasErrors('profile_image_path');

        $this->assertNull($this->user->fresh()->profile_image_path);
    }

    public function test_name_is_required(): void
    {
        $this->actingAs($this->user)
            ->patch(route('profile.update'), ['name' => ''])
            ->assertSessionHasErrors(['name' => '名前は必須です。']);

        $this->assertSame('元の名前', $this->user->fresh()->name);
    }
}
