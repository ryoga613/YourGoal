<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ProfileController extends Controller
{
    /**
     * プロフィール・投稿・目標・達成履歴を表示
     */
    public function show(Request $request, User $user)
    {

        $user->loadCount('goal_posts', 'progress_reports', 'achievement_reports', 'knowledge_posts');

        $goals = $user->goal_posts()->get();
        $achievements = $user->achievement_reports()->get();
        $progresses = $user->progress_reports()->get();
        $knowledges = $user->knowledge_posts()->get();

        $posts = $goals
            ->concat($achievements)
            ->concat($progresses)
            ->concat($knowledges)
            ->sortByDesc('created_at')   // 新しい順に並べる（必要なら）
            ->values();

        // ページネーションの設定
        $perPage = 2;
        $page = LengthAwarePaginator::resolveCurrentPage();

        $posts = new LengthAwarePaginator(
            $posts->forPage($page, $perPage),   // ④ このページの投稿
            $posts->count(),                    // ③ 全部で何件
            $perPage,                         // ② 1ページ何件
            $page,                            // ① 今何ページ目
            ['path' => $request->url()]       // ボタンのリンク先（今のページの URL）
        );

        return view('profile.show', [
            'user' => $user,
            'goals' => $goals,
            'achievements' => $achievements,
            'progresses' => $progresses,
            'posts' => $posts,
        ]);
    }

    /**
     * 自分のプロフィール編集フォームを表示
     */
    public function edit()
    {
        return view('profile.edit', [
            'user' => auth()->user(),
        ]);
    }

    /**
     * 表示名・自己紹介・プロフィール画像を更新
     */
    public function update(UpdateProfileRequest $request)
    {
        $validated = $request->validated();

        $user = auth()->user();

        $data = [
            'name' => $validated['name'],
            'bio' => $validated['bio'] ?? null,
        ];

        if ($request->hasFile('profile_image_path')) {
            $data['profile_image_path'] = $request->file('profile_image_path')->store('profile-images', 'public');
        }

        $user->update($data);

        // dd($validated);

        return redirect()->route('users.show', $user)->with('status', 'プロフィールを更新しました。');

    }
}
