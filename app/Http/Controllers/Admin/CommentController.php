<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * コメント一覧・検索
     */
    public function index(Request $request)
    {
        //
    }

    /**
     * コメント詳細を表示
     */
    public function show(Comment $comment)
    {
        //
    }

    /**
     * 不適切なコメントを削除
     */
    public function destroy(Comment $comment)
    {
        //
    }
}
