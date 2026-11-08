<?php

namespace App\Http\Controllers;

use App\Enums\PostType;
use App\Models\AchievementReport;
use App\Models\GoalPost;
use App\Models\KnowledgePost;
use App\Models\ProgressReport;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
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
        $perPage = 20;
        $page = LengthAwarePaginator::resolveCurrentPage();

        // どの種類が上位に来るかわからないので、各種類から「今のページまでの件数」を取っておく
        $take = $page * $perPage;
        $total = 0;
        $posts = collect();

        // 4種類の投稿を取り、Blade でリンクを作れるよう type を付けてまとめる
        foreach (PostType::cases() as $postType) {
            $total += $postType->modelClass()::count();

            $query = $postType->modelClass()::with('user')
                ->withCount(['post_likes', 'comments'])
                // ログイン中のユーザーがいいね済みかどうか（$post->liked に true / false が入る）
                ->withExists(['post_likes as liked' => fn ($q) => $q->where('user_id', Auth::id())]);

            if ($sort === 'latest') {
                $query->latest()->latest('id');
            } else {
                $query->orderByDesc('post_likes_count')->latest();
            }

            foreach ($query->take($take)->get() as $post) {
                $post->type = $postType->value;
                $posts->push($post);
            }
        }

        $posts = $sort === 'latest'
            ? $posts->sortByDesc('created_at')
            : $posts->sortBy([['post_likes_count', 'desc'], ['created_at', 'desc']]);

        $posts = new LengthAwarePaginator(
            $posts->forPage($page, $perPage)->values(), // このページに表示する20件
            $total,                                     // 4種類の合計件数
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()] // ?sort=latest をページリンクに引き継ぐ
        );

        return view('home', compact('posts', 'sort'));
    }
}
