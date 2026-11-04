<?php

namespace App\Http\Controllers;

use App\Enums\PostType;
use App\Http\Requests\SearchIndexRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchController extends Controller
{
    /**
     * キーワード（タイトル・本文・ユーザー名）と投稿種類で検索し、新しい順に表示
     */
    public function index(SearchIndexRequest $request)
    {
        $validated = $request->validated();
        $keyword = $validated['keyword'] ?? null;

        // type が指定されていればその種類だけ、なければ4種類すべてを検索する
        $types = isset($validated['type'])
            ? [PostType::from($validated['type'])]
            : PostType::cases();

        $perPage = 20;
        $page = LengthAwarePaginator::resolveCurrentPage();

        // どの種類が上位に来るかわからないので、各種類から「今のページまでの件数」を取っておく
        $take = $page * $perPage;
        $total = 0;
        $posts = collect();

        foreach ($types as $postType) {
            $query = $postType->modelClass()::query()
                ->when($keyword !== null, fn (Builder $q) => $this->whereKeyword($q, $keyword));

            // 件数は絞り込み後・取得前に数える（take() を付ける前のクエリで数える）
            $total += (clone $query)->count();

            $posts = $posts->concat(
                $query->with('user')->latest()->latest('id')->take($take)->get()
            );
        }

        $posts = new LengthAwarePaginator(
            $posts->sortByDesc('created_at')->forPage($page, $perPage)->values(), // このページの20件
            $total,                                                               // 検索にヒットした合計件数
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()] // 検索条件をページリンクに引き継ぐ
        );

        return view('search.index', compact('posts', 'keyword'));
    }

    /**
     * タイトル・本文・投稿者名のどれかにキーワードを含む投稿に絞り込む
     */
    private function whereKeyword(Builder $query, string $keyword): Builder
    {
        // % と _ は LIKE の記号なので、文字として検索できるようエスケープする
        $like = '%'.addcslashes($keyword, '\\%_').'%';

        // OR 条件は必ず where(function...) で囲む。囲まないと、あとから AND 条件を足したときに絞り込みが効かなくなる
        return $query->where(function (Builder $q) use ($like) {
            $q->where('title', 'like', $like)
                ->orWhere('body', 'like', $like)
                ->orWhereHas('user', fn (Builder $u) => $u->where('name', 'like', $like));
        });
    }
}
