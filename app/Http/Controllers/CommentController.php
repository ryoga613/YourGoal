<?php

namespace App\Http\Controllers;

use App\Enums\PostType;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Models\Comment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    /**
     * 投稿にコメントを追加
     */
    public function store(StoreCommentRequest $request, string $type, int $id)
    {
        Gate::authorize('create', Comment::class);

        $validated = $request->validated();
        $postType = PostType::from($type);

        // 存在しない投稿へのコメントは 404 にする
        $post = $postType->modelClass()::findOrFail($id);

        if ($postType === PostType::Goal) {
            $data['goal_post_id'] = $post->id;
        } elseif ($postType === PostType::Achievement) {
            $data['achievement_report_id'] = $post->id;
        } elseif ($postType === PostType::Knowledge) {
            $data['knowledge_post_id'] = $post->id;
        } else {
            $data['progress_report_id'] = $post->id;
        }

        $data['user_id'] = Auth::id();
        $data['body'] = $validated['body'];

        $comment = Comment::create($data);

        return $this->redirectToComment($comment, 'コメントを投稿しました。');
    }

    /**
     * 既存コメントへの返信を追加
     */
    public function reply(StoreCommentRequest $request, Comment $comment)
    {
        Gate::authorize('create', Comment::class);

        $validated = $request->validated();

        $postType = $comment->postType();

        // 返信は親コメントと同じ投稿に紐づける
        $reply = Comment::create([
            'user_id' => Auth::id(),
            $postType->foreignKey() => $comment->{$postType->foreignKey()},
            'parent_id' => $comment->id,
            'body' => $validated['body'],
        ]);

        return $this->redirectToComment($reply, '返信を投稿しました。');
    }

    /**
     * 自分のコメントを編集
     */
    public function update(UpdateCommentRequest $request, Comment $comment)
    {
        // 本人かどうかは UpdateCommentRequest の authorize で確認済み
        Gate::authorize('update', $comment);

        $validated = $request->validated();
        $comment->update(['body' => $validated['body']]);

        return $this->redirectToComment($comment, 'コメントを更新しました。');
    }

    /**
     * 自分のコメントを削除
     */
    public function destroy(Comment $comment)
    {
        Gate::authorize('delete', $comment);

        // 返信とコメントへのいいねは、外部キーの cascadeOnDelete によって一緒に削除される
        $comment->delete();

        return redirect()
            ->to($this->postUrl($comment).'#comments')
            ->with('status', 'コメントを削除しました。');
    }

    /**
     * コメントした投稿の詳細ページに戻り、そのコメントの位置までスクロールする
     */
    private function redirectToComment(Comment $comment, string $message): RedirectResponse
    {
        Gate::authorize('view', $comment);

        return redirect()
            ->to($this->postUrl($comment).'#comment-'.$comment->id)
            ->with('status', $message);
    }

    /**
     * コメント先の投稿の詳細ページのURL
     */
    private function postUrl(Comment $comment): string
    {
        Gate::authorize('view', $comment);
        $postType = $comment->postType();

        return route('posts.show', [
            'type' => $postType->value,
            'id' => $comment->{$postType->foreignKey()},
        ]);
    }
}
