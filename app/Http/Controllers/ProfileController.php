<?php

namespace App\Http\Controllers;

use App\Enums\PostType;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    /**
     * プロフィール・投稿・目標・達成履歴を表示
     */
    public function show(User $user)
    {
        $user->loadCount('goal_posts', 'progress_reports', 'achievement_reports', 'knowledge_posts');

        $goals = $user->goal_posts()->latest()->get();
        // 達成履歴では達成した目標のタイトルを表示するので、目標も一緒に読み込む（N+1 を防ぐ）
        $achievements = $user->achievement_reports()->with('goal_post')->latest()->get();

        // 最近の投稿：4種類の投稿の「type・id・created_at」だけを UNION ALL で1つの表にまとめる
        $union = null;
        foreach (PostType::cases() as $postType) {
            $query = $postType->modelClass()::query()
                ->selectRaw('? as type', [$postType->value])
                ->addSelect(['id', 'created_at'])
                ->where('user_id', $user->id);

            $union = $union ? $union->unionAll($query) : $query;
        }

        // 並べ替えとページ分けは DB に任せ、このページの分の id だけを受け取る
        $posts = DB::query()
            ->fromSub($union, 'posts')
            ->orderByDesc('created_at')
            ->orderBy('type')
            ->orderByDesc('id')     // 同じ時刻でもページの境目で重複・抜けが出ないように
            ->paginate(5);

        // このページの分だけ、種類ごとに本体を読み込む
        $models = $posts->getCollection()
            ->groupBy('type')
            ->map(fn ($rows, $type) => PostType::from($type)->modelClass()::findMany($rows->pluck('id'))->keyBy('id'));

        // DB が決めた並び順のまま、モデルに差し替える（読み込みの間に削除された投稿は飛ばす）
        $posts->setCollection($posts->getCollection()
            ->map(fn ($row) => $models[$row->type][$row->id] ?? null)
            ->filter()
            ->values());

        return view('profile.show', [
            'user' => $user,
            'goals' => $goals,
            'achievements' => $achievements,
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

        return redirect()->route('users.show', $user)->with('status', 'プロフィールを更新しました。');
    }
}
