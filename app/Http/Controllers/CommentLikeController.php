<?php

namespace App\Http\Controllers;

use App\Models\Comment;

class CommentLikeController extends Controller
{
    /**
     * コメントにいいねを登録
     */
    public function store(Comment $comment)
    {
        //
    }

    /**
     * コメントのいいねを解除
     */
    public function destroy(Comment $comment)
    {
        //
    }
}
