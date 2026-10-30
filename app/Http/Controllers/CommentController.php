<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * 投稿にコメントを追加
     */
    public function store(Request $request, string $type, int $id)
    {
        //
    }

    /**
     * 既存コメントへの返信を追加
     */
    public function reply(Request $request, Comment $comment)
    {
        //
    }

    /**
     * 自分のコメントを編集
     */
    public function update(Request $request, Comment $comment)
    {
        //
    }

    /**
     * 自分のコメントを削除
     */
    public function destroy(Comment $comment)
    {
        //
    }
}
