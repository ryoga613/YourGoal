<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PostType;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminPostIndexRequest;
use App\Models\AchievementReport;
use App\Models\GoalPost;
use App\Models\KnowledgePost;
use App\Models\ProgressReport;
use App\Support\EscapeLike;
use Illuminate\Pagination\LengthAwarePaginator;

class PostController extends Controller
{
    use EscapeLike;

    /**
     * 一覧・詳細で数える関連データ
     */
    private const COUNT_RELATIONS = ['comments', 'post_likes'];

    /**
     * 投稿一覧・検索・種類絞り込み
     */
    public function index(AdminPostIndexRequest $request)
    {
        $validated = $request->validated();

        $keyword = $validated['keyword'] ?? null;
        $type = isset($validated['type']) ? PostType::from($validated['type']) : null;

        // ① 検索する投稿の種類を決める(選ばれていればその種類だけ、なければ全種類)
        if ($type) {
            $types = [$type];
        } else {
            $types = PostType::cases();
        }

        // ② 種類ごとに投稿を取ってきて、1つの箱にまとめる
        $allPosts = collect();

        foreach ($types as $postType) {
            $modelClass = $postType->modelClass(); // 例: GoalPost::class

            $query = $modelClass::query()
                ->with('user')
                ->withCount(self::COUNT_RELATIONS);

            // キーワードがあれば、タイトルか本文か投稿者の名前で絞り込む
            if ($keyword) {
                // % や _ を「何でも一致」ではなく、ただの文字として検索する
                $pattern = '%'.$this->escapeLike($keyword).'%';

                $query->where(function ($q) use ($pattern) {
                    $q->where('title', 'like', $pattern)
                        ->orWhere('body', 'like', $pattern)
                        // 投稿者の名前は users テーブルにあるため、user リレーションをたどって検索する
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', $pattern));
                });
            }

            foreach ($query->get() as $post) {
                $allPosts->push([
                    'type' => $postType,
                    'post' => $post,
                ]);
            }
        }

        // ③ 新しい順に並べる
        $allPosts = $allPosts
            ->sortByDesc(fn ($item) => $item['post']->created_at)
            ->values();

        // ④ 20件ずつページ分けする
        $perPage = 20;
        $page = $request->input('page', 1);

        $posts = new LengthAwarePaginator(
            $allPosts->forPage($page, $perPage), // このページに表示する20件
            $allPosts->count(),                  // 全部で何件か
            $perPage,                            // 1ページの件数
            $page,                               // 今のページ番号
            ['path' => $request->url(), 'query' => $request->query()] // 検索条件を引き継ぐ
        );

        return view('admin.posts.index', [
            'posts' => $posts,
            'keyword' => $keyword,
            'type' => $type,
        ]);
    }

    /**
     * 投稿詳細を表示
     */
    public function show(string $type, int $id)
    {
        $postType = PostType::from($type);
        $post = $this->findPost($postType, $id);

        $post->load(['comments' => fn ($query) => $query->with('user')->oldest()]);

        match ($postType) {
            PostType::Goal => $post->loadCount(['progress_reports', 'achievement_reports']),
            PostType::Progress, PostType::Achievement => $post->load('goal_post'),
            PostType::Knowledge => null,
        };

        return view('admin.posts.show', [
            'type' => $postType,
            'post' => $post,
        ]);
    }

    /**
     * 不適切な投稿を削除
     */
    public function destroy(string $type, int $id)
    {
        // コメント・いいね（目標宣言の場合は紐づく進捗報告・達成報告も）は、外部キーの cascadeOnDelete によって一緒に削除される
        PostType::from($type)->modelClass()::findOrFail($id)->delete();

        return redirect()
            ->route('admin.posts.index')
            ->with('status', '投稿を削除しました。');
    }

    /**
     * 種類とIDから投稿を取得する（存在しなければ404）
     */
    private function findPost(PostType $type, int $id): GoalPost|ProgressReport|AchievementReport|KnowledgePost
    {
        return $type->modelClass()::query()
            ->with('user')
            ->withCount(self::COUNT_RELATIONS)
            ->findOrFail($id);
    }
}
