<?php

namespace App\Http\Controllers;

use App\Enums\GoalPostStatus;
use App\Enums\PostType;
use App\Http\Requests\UpdatePostRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    /**
     * 投稿種類選択画面を表示
     */
    public function create()
    {
        // 進捗・達成報告は目標を選んでから書くので、まだ達成・中止していない自分の目標を渡す
        $goals = Auth::user()->goal_posts()
            ->whereIn('status', [GoalPostStatus::Considering, GoalPostStatus::InProgress])
            ->latest()
            ->get();

        return view('posts.create', compact('goals'));
    }

    /**
     * 投稿者・内容・画像・ジャンル・いいね・コメントを表示
     */
    public function show(string $type, int $id)
    {
        // コメントは返信も含めて古い順にまとめて取得し、画面側で親子に組み立てる
        $post = PostType::from($type)->modelClass()::with([
            'user',
            'comments' => fn ($query) => $query->with('user')
                ->withCount('comment_likes')
                // ログイン中のユーザーがそのコメントにいいね済みかどうか（$comment->liked に true / false が入る）
                ->withExists(['comment_likes as liked' => fn ($q) => $q->where('user_id', Auth::id())])
                ->oldest()
                ->oldest('id'),
        ])
            ->withCount(['post_likes', 'comments'])
            // ログイン中のユーザーがいいね済みかどうか（$post->liked に true / false が入る）
            ->withExists(['post_likes as liked' => fn ($q) => $q->where('user_id', Auth::id())])
            ->findOrFail($id);

        return view('posts.show', [
            'post' => $post,
            // 親コメントのIDごとの返信一覧（キーが 0 のグループが投稿への直接のコメント）
            'commentsByParent' => $post->comments->groupBy(fn ($comment) => $comment->parent_id ?? 0),
        ]);
    }

    /**
     * 自分の投稿の編集フォームを表示
     */
    public function edit(string $type, int $id)
    {
        $postType = PostType::from($type);
        $post = $postType->modelClass()::findOrFail($id);

        return view('posts.edit', [
            'type' => $postType,
            'post' => $post,
        ]);
    }

    /**
     * 自分の投稿のタイトル・本文・画像を更新
     */
    public function update(UpdatePostRequest $request, string $type, int $id)
    {
        $postType = PostType::from($type);
        $post = $postType->modelClass()::findOrFail($id);
        $validated = $request->validated();

        $data = [
            'title' => $validated['title'] ?? null,
            'body' => $validated['body'] ?? null,
        ];

        // 画像は新しく選んだときだけ差し替え、古い画像ファイルは消す
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store($postType->imageDirectory(), 'public');
            $this->deleteImage($post->image_path);
        }

        $post->update($data);

        return redirect()
            ->route('posts.show', ['type' => $postType->value, 'id' => $post->id])
            ->with('status', '投稿を更新しました。');
    }

    /**
     * 自分の投稿を削除
     */
    public function destroy(string $type, int $id)
    {
        $postType = PostType::from($type);
        $post = $postType->modelClass()::findOrFail($id);

        // コメント・いいね（目標宣言の場合は紐づく進捗報告・達成報告も）は、外部キーの cascadeOnDelete によって一緒に削除される
        DB::transaction(function () use ($post, $postType) {
            $post->delete();

            // 達成報告を消して1件も残らなければ、達成済みだった目標を挑戦中に戻す
            if ($postType === PostType::Achievement) {
                $goal = $post->goal_post;
                if ($goal->status === GoalPostStatus::Achieved && ! $goal->achievement_reports()->exists()) {
                    $goal->update(['status' => GoalPostStatus::InProgress]);
                }
            }
        });

        $this->deleteImage($post->image_path);

        return redirect()
            ->route('users.show', Auth::user())
            ->with('status', '投稿を削除しました。');
    }

    /**
     * public ディスクに保存した投稿画像を消す
     */
    private function deleteImage(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
