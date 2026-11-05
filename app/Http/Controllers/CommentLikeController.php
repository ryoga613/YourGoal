<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\CommentLike;

class CommentLikeController extends Controller
{
    /**
     * コメントにいいねを登録
     */
    public function store(Comment $comment)
    {
        CommentLike::firstOrCreate([
            'comment_id' => $comment->id,
            'user_id' => auth()->id(),
        ]);

        return back();
    }

    /**
     * コメントのいいねを解除
     */
    public function destroy(Comment $comment)
    {
        CommentLike::where([
            'comment_id' => $comment->id,
            'user_id' => auth()->id(),
        ])->delete();

        return back();
    }
}
