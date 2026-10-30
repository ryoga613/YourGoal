<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PostType;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminCommentIndexRequest;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Builder;

class CommentController extends Controller
{
    /**
     * 一覧表示時に読み込むリレーション（コメント先の投稿は4種類すべてを読み込む）
     */
    private const LIST_RELATIONS = ['user', 'parent.user', 'goal_post', 'progress_report', 'achievement_report', 'knowledge_post'];

    /**
     * コメント一覧・検索
     */
    public function index(AdminCommentIndexRequest $request)
    {
        $validated = $request->validated();

        $keyword = $validated['keyword'] ?? null;
        $type = isset($validated['type']) ? PostType::from($validated['type']) : null;

        $commentsQuery = Comment::query()
            ->with(self::LIST_RELATIONS)
            ->withCount(['replies', 'comment_likes']);

        // キーワード検索(コメント投稿者の名前 or コメントの本文で)
        if (! empty($keyword)) {
            $pattern = '%'.$this->escapeLike($keyword).'%';

            $commentsQuery->where(function (Builder $query) use ($pattern) {
                $query->where('body', 'like', $pattern)
                    ->orWhereHas('user', fn (Builder $q) => $q->where('name', 'like', $pattern));
            });
        }

        // 投稿種類を指定
        if ($type) {
            $commentsQuery->forPostType($type);
        }

        // 同じ日時のコメントがあってもページ送りで順序がぶれないよう、IDでも並べる
        $comments = $commentsQuery->latest()->latest('id')->paginate(20)->withQueryString();

        return view('admin.comments.index', [
            'comments' => $comments,
            'keyword' => $keyword,
            'type' => $type,
        ]);
    }

    /**
     * コメント詳細を表示
     */
    public function show(Comment $comment)
    {
        $comment->load([...self::LIST_RELATIONS, 'replies' => fn ($query) => $query->with('user')->oldest()])
            ->loadCount('comment_likes');

        return view('admin.comments.show', [
            'comment' => $comment,
            // 削除時に一緒に消える返信（返信への返信も含む）の件数
            'descendantCount' => $comment->descendantCount(),
        ]);
    }

    /**
     * 不適切なコメントを削除
     */
    public function destroy(Comment $comment)
    {
        // 返信とコメントへのいいねは、外部キーの cascadeOnDelete によって一緒に削除される
        $comment->delete();

        return redirect()
            ->route('admin.comments.index')
            ->with('status', 'コメントを削除しました。');
    }

    /**
     * LIKE検索で % と _ をワイルドカードではなく文字として扱うようにエスケープする
     */
    private function escapeLike(string $value): string
    {
        return addcslashes($value, '\\%_');
    }
}
