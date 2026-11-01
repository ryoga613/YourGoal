<?php

namespace App\Http\Controllers;

use App\Enums\PostType;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    /**
     * 投稿一覧を新着順または人気順で表示
     */
    public function index(Request $request)
    {
        // ?sort=latest なら新着順、それ以外はおすすめ（いいねの多い順）
        $sort = $request->query('sort') === 'latest' ? 'latest' : 'popular';
        $limit = 20;

        $posts = collect();

        // 4種類の投稿をそれぞれ上位20件ずつ取り、Blade でリンクを作れるよう type を付けてまとめる
        foreach (PostType::cases() as $postType) {
            $query = $postType->modelClass()::with('user')
                ->withCount(['post_likes', 'comments']);

            if ($sort === 'latest') {
                $query->latest()->latest('id');
            } else {
                $query->orderByDesc('post_likes_count')->latest();
            }

            foreach ($query->take($limit)->get() as $post) {
                $post->type = $postType->value;
                $posts->push($post);
            }
        }

        $posts = $sort === 'latest'
            ? $posts->sortByDesc('created_at')
            : $posts->sortBy([['post_likes_count', 'desc'], ['created_at', 'desc']]);

        $posts = $posts->take($limit)->values();

        return view('home', compact('posts', 'sort'));
    }
}
