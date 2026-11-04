<?php

namespace App\Http\Controllers;

use App\Enums\GoalPostStatus;
use App\Enums\PostType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            'comments' => fn ($query) => $query->with('user')->oldest()->oldest('id'),
        ])
            ->withCount(['post_likes', 'comments'])
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
        //
    }

    /**
     * 自分の投稿のタイトル・本文・画像を更新
     */
    public function update(Request $request, string $type, int $id)
    {
        //
    }

    /**
     * 自分の投稿を削除
     */
    public function destroy(string $type, int $id)
    {
        //
    }
}
