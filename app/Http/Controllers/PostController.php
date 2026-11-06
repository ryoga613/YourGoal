<?php

namespace App\Http\Controllers;

use App\Enums\GoalPostStatus;
use App\Enums\PostType;
use App\Http\Requests\UpdatePostRequest;
use App\Models\AchievementReport;
use App\Models\GoalPost;
use App\Models\KnowledgePost;
use App\Models\ProgressReport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
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
    public function edit(PostType $type, int $id)
    {

        if ($type === PostType::Goal) {
            $post = GoalPost::findOrFail($id);

        } elseif ($type === PostType::Progress) {
            $post = ProgressReport::findOrFail($id);

        } elseif ($type === PostType::Achievement) {
            $post = AchievementReport::findOrFail($id);
            
        } elseif ($type === PostType::Knowledge) {
            $post = KnowledgePost::findOrFail($id);

        } else {
            abort(404);
        }
        Gate::authorize('update', $post);

        return view('posts.edit', compact('post', 'type'));
    }

    /**
     * 自分の投稿のタイトル・本文・画像を更新
     */
    public function update(UpdatePostRequest $request, PostType $type, int $id)
    {
        $post = $request->target();
        $validated = $request->validated();

        $data = [
            'title' => $validated['title'] ?? null,
            'body' => $validated['body'] ?? null,
        ];

        // 画像は新しく選んだときだけ差し替え、古い画像ファイルは消す
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store($type->value.'_images', 'public');

            if ($post->image_path) {
                Storage::disk('public')->delete($post->image_path);
            }
        }

        $post->update($data);

        return redirect()
            ->route('posts.show', ['type' => $type->value, 'id' => $post->id])
            ->with('status', '投稿を更新しました。');
    }

    /**
     * 自分の投稿を削除
     */
    public function destroy(PostType $type, int $id)
    {
        $post = $type->modelClass()::findOrFail($id);
        Gate::authorize('delete', $post);

        // 消える投稿の画像（目標宣言の場合は、一緒に消える進捗報告・達成報告の画像も）
        $imagePaths = collect([$post->image_path]);
        if ($post instanceof GoalPost) {
            $imagePaths = $imagePaths
                ->merge($post->progress_reports()->pluck('image_path'))
                ->merge($post->achievement_reports()->pluck('image_path'));
        }

        // コメント・いいね（目標宣言の場合は紐づく進捗報告・達成報告も）は、外部キーの cascadeOnDelete によって一緒に削除される
        DB::transaction(function () use ($post) {
            $post->delete();

            // 達成報告を消して1件も残らなければ、達成済みだった目標を挑戦中に戻す
            if ($post instanceof AchievementReport) {
                $goal = $post->goal_post;

                if ($goal->status === GoalPostStatus::Achieved && ! $goal->achievement_reports()->exists()) {
                    $goal->update(['status' => GoalPostStatus::InProgress]);
                }
            }
        });

        // 画像ファイルは、データベースの削除が終わってから消す
        Storage::disk('public')->delete($imagePaths->filter()->all());

        return redirect()
            ->route('users.show', Auth::user())
            ->with('status', '投稿を削除しました。');
    }
}
