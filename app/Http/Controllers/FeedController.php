<?php

namespace App\Http\Controllers;

use App\Enums\PostType;
use App\Models\AchievementReport;
use App\Models\GoalPost;
use App\Models\KnowledgePost;
use App\Models\ProgressReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class FeedController extends Controller
{
    /**
     * 投稿一覧を新着順または人気順で表示
     */
    public function index(Request $request)
    {
        // 投稿の閲覧権限を確認
        Gate::authorize('view', GoalPost::class);
        Gate::authorize('view', AchievementReport::class);
        Gate::authorize('view', ProgressReport::class);
        Gate::authorize('view', KnowledgePost::class);

        // ?sort=latest なら新着順、それ以外はおすすめ（いいねの多い順）
        $sort = $request->query('sort') === 'latest' ? 'latest' : 'popular';

        // 4種類の投稿の「id・種類・投稿日時」だけを UNION ALL で1つの表にまとめる
        // いいね数はおすすめ順の並べ替えにだけ使うので、おすすめ順のときだけ数える
        $feed = null;
        foreach (PostType::cases() as $postType) {
            $query = $postType->modelClass()::query()
                ->select('id', 'created_at')
                ->selectRaw('? as type', [$postType->value])
                ->when($sort === 'popular', fn ($q) => $q->withCount('post_likes'));

            $feed = $feed ? $feed->unionAll($query) : $query;
        }

        // 並べ替えとページ分けは DB に任せ、このページの20件分の id だけを受け取る
        $feed = DB::query()->fromSub($feed, 'feed');

        if ($sort === 'latest') {
            $feed->orderByDesc('created_at');
        } else {
            $feed->orderByDesc('post_likes_count')->orderByDesc('created_at');
        }

        // 全部で何件かは、いいね数を数えずに種類ごとの件数を足して求める
        // （paginate に任せると、件数を出すためだけに全投稿のいいね数まで数えてしまう）
        $total = collect(PostType::cases())->sum(fn (PostType $postType) => $postType->modelClass()::count());

        $posts = $feed->orderBy('type')->orderByDesc('id') // 同じ値のときも順番がぶれないように
            ->paginate(20, total: $total)
            ->withQueryString(); // ?sort=latest をページリンクに引き継ぐ

        // 20件分だけ、種類ごとに本体を読み込む
        $models = $posts->getCollection()
            ->groupBy('type')
            ->map(fn ($rows, $type) => PostType::from($type)->modelClass()::with('user')
                ->withCount(['post_likes', 'comments'])
                // ログイン中のユーザーがいいね済みかどうか（$post->liked に true / false が入る）
                ->withExists(['post_likes as liked' => fn ($q) => $q->where('user_id', Auth::id())])
                ->findMany($rows->pluck('id'))
                ->keyBy('id'));

        // DB が決めた並び順のまま、モデルに差し替える（Blade でリンクを作れるよう type を付ける）
        $posts->setCollection($posts->getCollection()
            ->map(function ($row) use ($models) {
                $post = $models[$row->type][$row->id] ?? null; // 読み込みの間に削除された投稿は飛ばす
                $post?->setAttribute('type', $row->type);

                return $post;
            })
            ->filter()
            ->values());

        return view('home', compact('posts', 'sort'));
    }
}
