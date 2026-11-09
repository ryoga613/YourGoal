<?php

namespace Tests\Feature;

use App\Models\KnowledgePost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * ページ送りが、layouts/app.blade.php の CSS（.pagination / .page-link）が当たる HTML で出ていること
 */
class PaginationStyleTest extends TestCase
{
    use RefreshDatabase;

    public static function pages(): array
    {
        return [
            '検索' => ['posts.search'],
            'ホーム' => ['home'],
        ];
    }

    #[TestDox('ページ送りはこのアプリの CSS が当たる HTML で表示される')]
    #[DataProvider('pages')]
    public function test_pagination_uses_styled_markup(string $routeName): void
    {
        // 1ページ20件なので、21件以上あるとページ送りが出る
        KnowledgePost::factory()->count(21)->create();

        $this->actingAs(User::factory()->create())
            ->get(route($routeName))
            ->assertOk()
            ->assertSee('<ul class="pagination">', false)
            ->assertSee('class="page-link"', false)
            // Tailwind 用の HTML（このアプリでは CSS が当たらない）は出さない
            ->assertDontSee('relative inline-flex', false);
    }
}
