<?php

namespace Tests\Feature;

use App\Models\GoalPost;
use App\Models\KnowledgePost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\TestDox;
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

    #[TestDox('ゲストはログイン画面に送られる')]
    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('users.show', $this->user))->assertRedirect(route('login'));
    }

    #[TestDox('プロフィールに投稿数が出て、投稿は1ページ5件ずつ並ぶ')]
    public function test_profile_shows_post_count_and_5_posts_per_page(): void
    {
        KnowledgePost::factory()->for($this->user)->count(11)->create();
        GoalPost::factory()->for($this->user)->create(['title' => '一番新しい目標', 'created_at' => now()->addMinute()]);

        $this->actingAs(User::factory()->create())
            ->get(route('users.show', $this->user))
            ->assertOk()
            ->assertSeeText('12 投稿')
            ->assertSee('一番新しい目標')
            // 1ページ5件なので、12件だと3ページ目までになる（10件ずつだと2ページで終わってしまう）
            ->assertSee(route('users.show', $this->user).'?page=3', false)
            ->assertDontSee(route('users.show', $this->user).'?page=4', false);
    }

    // ---- 編集 ----

    #[TestDox('プロフィール編集画面に今の値が入っている')]
    public function test_edit_form_shows_current_values(): void
    {
        $this->actingAs($this->user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('元の名前')
            ->assertSee('元の自己紹介');
    }

    #[TestDox('表示名と自己紹介を更新できる')]
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

    #[TestDox('アップロードした画像が保存され、パスが記録される')]
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

    #[TestDox('画像を選ばずに更新すると今の画像が残る')]
    public function test_updating_without_image_keeps_current_image(): void
    {
        $this->user->update(['profile_image_path' => 'profile-images/current.png']);

        $this->actingAs($this->user)
            ->patch(route('profile.update'), ['name' => '新しい名前']);

        $this->assertSame('profile-images/current.png', $this->user->fresh()->profile_image_path);
    }

    #[TestDox('SVG 画像はアップロードできない')]
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

    #[TestDox('表示名は必須')]
    public function test_name_is_required(): void
    {
        $this->actingAs($this->user)
            ->patch(route('profile.update'), ['name' => ''])
            ->assertSessionHasErrors(['name' => '名前は必須です。']);

        $this->assertSame('元の名前', $this->user->fresh()->name);
    }
}
