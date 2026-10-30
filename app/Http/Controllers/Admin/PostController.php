<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * 投稿一覧・検索・種類絞り込み
     */
    public function index(Request $request)
    {
        //
    }

    /**
     * 投稿詳細を表示
     */
    public function show(string $type, int $id)
    {
        //
    }

    /**
     * 不適切な投稿を削除
     */
    public function destroy(string $type, int $id)
    {
        //
    }
}
