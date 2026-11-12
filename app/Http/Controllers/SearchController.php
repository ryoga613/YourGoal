<?php

namespace App\Http\Controllers;

use App\Enums\PostType;
use App\Http\Requests\SearchIndexRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    /**
     * キーワード（タイトル・本文・ユーザー名）と投稿種類で検索し、新しい順に表示
     */
    public function index(SearchIndexRequest $request)
    {
        $validated = $request->validated();
        $keyword = $validated['keyword'] ?? null;

        $PostRaws = null;

        // $types に入っている種類の分だけ(選ばれていれば1つ、なければ4つ全部)、
        // テーブルごとに「type(名札)・id・created_at」を取るクエリを作って、縦に積み重ねる
        $types = isset($validated['type']) ? [PostType::from($validated['type'])] : PostType::cases();
        foreach ($types as $type) {
            $query = $type->modelClass()::query()
                ->selectRaw('? as type', [$type->value])
                ->addSelect(['id', 'created_at'])
                // キーワードが入力されていれば、タイトル・本文・投稿者名で絞り込む
                ->when($keyword !== null, fn (Builder $q) => $this->whereKeyword($q, $keyword));

            $PostRaws = $PostRaws ? $PostRaws->unionAll($query) : $query;
        }
        $posts = DB::query()
            ->fromSub($PostRaws, 'posts')    // 混ぜた全体を「posts」テーブルとして扱う
            ->orderByDesc('created_at')   // その posts を新しい順に並べる
            ->orderByDesc('id')           // 同じ時刻なら id の大きい順（ページの境目で重複・抜けを防ぐ）
            ->paginate(20)                // その posts から20件取る
            ->withQueryString();          // キーワード・種類をページリンクに引き継ぐ

        $models = $posts->getCollection()
            ->groupBy('type')
            ->map(fn ($rows, $type) => PostType::from($type)->modelClass()::with('user')
                ->findMany($rows->pluck('id'))
                ->keyBy('id'));

        // DB が決めた並び順のまま、stdClass をモデルに差し替える
        $posts->setCollection($posts->getCollection()
            ->map(fn ($row) => $models[$row->type][$row->id] ?? null)
            ->filter()
            ->values());

        return view('search.index', [
            'posts' => $posts,
            'keyword' => $keyword,
        ]);

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
