<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * TODO: Genreモデル作成後、{genre} の引数を Genre $genre（ルートモデルバインディング）に変更する
 */
class GenreController extends Controller
{
    /**
     * ジャンル一覧を表示
     */
    public function index()
    {
        //
    }

    /**
     * ジャンル作成フォームを表示
     */
    public function create()
    {
        //
    }

    /**
     * ジャンルを作成
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * ジャンル編集フォームを表示
     */
    public function edit(string $genre)
    {
        //
    }

    /**
     * ジャンル名を更新
     */
    public function update(Request $request, string $genre)
    {
        //
    }

    /**
     * ジャンルを削除
     */
    public function destroy(string $genre)
    {
        //
    }
}
