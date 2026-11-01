<?php

namespace App\Http\Controllers;

use App\Models\AchievementReport;
use App\Models\GoalPost;
use App\Models\KnowledgePost;
use App\Models\ProgressReport;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * 投稿種類選択画面を表示
     */
    public function create()
    {
        return view('posts.create');
    }

    /**
     * 投稿者・内容・画像・ジャンル・いいね・コメントを表示
     */
    public function show(string $type, int $id)
    {
        if ($type === 'knowledge') {
            $post = KnowledgePost::with(['user', 'comments.user'])
                ->withCount(['post_likes', 'comments'])
                ->findOrFail($id);
        } elseif ($type === 'goal') {
            $post = GoalPost::with(['user', 'comments.user'])
                ->withCount(['post_likes', 'comments'])
                ->findOrFail($id);
        } elseif ($type === 'progress') {
            $post = ProgressReport::with(['user', 'comments.user'])
                ->withCount(['post_likes', 'comments'])
                ->findOrFail($id);
        } elseif ($type === 'achievement') {
            $post = AchievementReport::with(['user', 'comments.user'])
                ->withCount(['post_likes', 'comments'])
                ->findOrFail($id);
        }

        return view('posts.show', compact('type', 'post'));
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
