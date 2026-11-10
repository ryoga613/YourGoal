<?php

namespace Tests\Feature;

use App\Enums\PostType;
use App\Models\Comment;
use App\Models\KnowledgePost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * ユーザーのアイコン（<x-avatar>）：プロフィール画像があれば画像、なければ名前の頭文字
 */
class AvatarTest extends TestCase
{
    use RefreshDatabase;

    private function imageTag(User $user): string
    {
        return 'src="'.asset('storage/'.$user->profile_image_path).'"';
    }

    private function showUrl(PostType $type, int $id): string
    {
        return route('posts.show', ['type' => $type->value, 'id' => $id]);
    }

    // ---- 部品そのもの ----

    #[TestDox('プロフィール画像があれば画像を出す')]
    public function test_shows_image_when_user_has_profile_image(): void
    {
        $user = User::factory()->make(['name' => 'Taro', 'profile_image_path' => 'profile-images/taro.png']);

        $this->blade('<x-avatar :user="$user" />', ['user' => $user])
            ->assertSee('<img class="avatar"', false)
            ->assertSee($this->imageTag($user), false)
            ->assertSee('alt="Taroさんのプロフィール画像"', false);
    }

    #[TestDox('プロフィール画像がなければ名前の頭文字を出す')]
    public function test_shows_initial_when_user_has_no_profile_image(): void
    {
        $user = User::factory()->make(['name' => '花子', 'profile_image_path' => null]);

        $this->blade('<x-avatar :user="$user" />', ['user' => $user])
            ->assertSee('<div class="avatar">花</div>', false)
            ->assertDontSee('<img', false);
    }

    #[TestDox('ユーザーがいなければ、渡した名前の頭文字（なければ U）を出す')]
    public function test_shows_given_name_or_u_without_user(): void
    {
        $this->blade('<x-avatar name="Mika S." />')->assertSee('<div class="avatar">M</div>', false);
        $this->blade('<x-avatar :user="null" />')->assertSee('<div class="avatar">U</div>', false);
    }

    #[TestDox('大きさを指定すると、幅・高さ・文字の大きさが変わる')]
    public function test_size_changes_width_height_and_font_size(): void
    {
        $user = User::factory()->make(['name' => 'Taro', 'profile_image_path' => null]);

        $this->blade('<x-avatar :user="$user" :size="72" />', ['user' => $user])
            ->assertSee('style="width:72px;height:72px;font-size:29px"', false);
    }

    // ---- 各画面 ----

    #[TestDox('ヘッダーには、他人のプロフィールを見ていても自分のアイコンが出る')]
    public function test_header_shows_own_avatar_even_on_other_users_profile(): void
    {
        $me = User::factory()->create(['profile_image_path' => 'profile-images/me.png']);
        $other = User::factory()->create(['profile_image_path' => 'profile-images/other.png']);

        $this->actingAs($me)
            ->get(route('users.show', $other))
            ->assertOk()
            // ヘッダーのプロフィールへのリンクの中に、自分の画像が入っている
            ->assertSeeInOrder(['aria-label="プロフィール"', $this->imageTag($me)], false)
            // プロフィールカードには、そのプロフィールの人の画像が 72px で出る
            ->assertSee($this->imageTag($other), false)
            ->assertSee('style="width:72px;height:72px;font-size:29px"', false);
    }

    #[TestDox('投稿詳細では、投稿した人とコメントした人のアイコンが出る')]
    public function test_post_page_shows_author_and_commenter_avatars(): void
    {
        $author = User::factory()->create(['profile_image_path' => 'profile-images/author.png']);
        $commenter = User::factory()->create(['name' => 'Kenta', 'profile_image_path' => null]);
        $post = KnowledgePost::factory()->for($author)->create();
        Comment::factory()->forPost($post)->for($commenter)->create();

        $this->actingAs(User::factory()->create())
            ->get($this->showUrl(PostType::Knowledge, $post->id))
            ->assertOk()
            ->assertSee($this->imageTag($author), false)
            ->assertSee('<div class="avatar">K</div>', false);
    }

    #[TestDox('ホームと検索結果の投稿カードに、投稿した人のアイコンが出る')]
    public function test_home_and_search_cards_show_author_avatar(): void
    {
        $author = User::factory()->create(['profile_image_path' => 'profile-images/author.png']);
        KnowledgePost::factory()->for($author)->create(['title' => 'アイコンの投稿']);
        $viewer = User::factory()->create();

        $this->actingAs($viewer)->get(route('home'))
            ->assertOk()
            ->assertSee($this->imageTag($author), false);

        $this->actingAs($viewer)->get(route('posts.search', ['q' => 'アイコン']))
            ->assertOk()
            ->assertSee($this->imageTag($author), false);
    }
}
