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
use Illuminate\Support\Facades\DB;

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
        $types = $type ? [$type] : PostType::cases();

        // ② 種類ごとに「種類・ID・投稿日時」だけを取るクエリを作り、UNION ALL で1つにまとめる
        $union = null;

        foreach ($types as $postType) {
            $query = $postType->modelClass()::query()
                ->toBase()
                ->selectRaw('? as type', [$postType->value])
                ->addSelect(['id', 'created_at']);

            // キーワードがあれば、タイトルか本文か投稿者の名前で絞り込む
            if ($keyword) {
                // % や _ を「何でも一致」ではなく、ただの文字として検索する
                $pattern = '%'.$this->escapeLike($keyword).'%';

                $query->where(function ($q) use ($pattern) {
                    $q->where('title', 'like', $pattern)
                        ->orWhere('body', 'like', $pattern)
                        // 投稿者の名前は users テーブルにあるため、users を user_id で探して検索する
                        ->orWhereIn('user_id', fn ($userQuery) => $userQuery
                            ->select('id')
                            ->from('users')
                            ->where('name', 'like', $pattern));
                });
            }

            $union = $union ? $union->unionAll($query) : $query;
        }

        // ③ 新しい順に並べて、DB で20件ずつページ分けする
        // 投稿日時が同じでもページ送りで抜けや重複が出ないよう、種類とIDでも並べる
        $rows = DB::query()
            ->fromSub($union, 'posts')
            ->orderByDesc('created_at')
            ->orderBy('type')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        // ④ このページに出る投稿だけを、種類ごとにまとめて取ってくる
        $postsByType = $rows->getCollection()
            ->groupBy('type')
            ->map(fn ($items, $value) => PostType::from($value)->modelClass()::query()
                ->with('user')
                ->withCount(self::COUNT_RELATIONS)
                ->findMany($items->pluck('id'))
                ->keyBy('id'));

        // ⑤ ページ分けした順番のまま、画面で使う形（種類と投稿）に置き換える
        $posts = $rows->through(fn ($row) => [
            'type' => PostType::from($row->type),
            'post' => $postsByType[$row->type][$row->id],
        ]);

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
