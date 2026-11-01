<?php

namespace App\Http\Controllers;

use App\Enums\GoalPostStatus;
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
        //
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
