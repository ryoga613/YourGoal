<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\CommentLike;
use Illuminate\Support\Facades\Gate;

class CommentLikeController extends Controller
{
    /**
     * コメントにいいねを登録
     */
    public function store(Comment $comment)
    {
        Gate::authorize('create', CommentLike::class);
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

        $commentLike = CommentLike::where([
            'comment_id' => $comment->id,
            'user_id' => auth()->id(),
        ])->first();

        if ($commentLike) {
            Gate::authorize('delete', $commentLike);
            $commentLike->delete();
        }

        return back();
    }
}
