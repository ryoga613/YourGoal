<?php

namespace App\Http\Controllers;

use App\Enums\GoalPostStatus;
use App\Http\Requests\StoreAchievementReportRequest;
use App\Models\AchievementReport;
use App\Models\GoalPost;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AchievementReportController extends Controller
{
    /**
     * 達成報告フォームを表示
     */
    public function create(GoalPost $goal)
    {
        // 投稿の作成はログインユーザー、標宣言投稿のstatusが達成済みでない場合なら誰でも可能
        Gate::authorize('create', [AchievementReport::class, $goal]);

        return view('achievement-reports.create', compact('goal'));
    }

    /**
     * 達成報告を保存し、対象目標を達成済みに変更
     */
    public function store(StoreAchievementReportRequest $request, GoalPost $goal)
    {
        // 投稿の作成はログインユーザー、標宣言投稿のstatusが達成済みでない場合なら誰でも可能
        Gate::authorize('store', [AchievementReport::class, $goal]);
        $validated = $request->validated();

        $data = [
            'goal_post_id' => $goal->id,
            'title' => $validated['title'],
            'body' => $validated['body'] ?? null,
            'image_path' => $request->file('image')?->store('achievement_images', 'public'),
            'user_id' => Auth::id(),
        ];

        // 報告の保存とステータス変更は、どちらかだけ成功しないようにまとめて行う
        $achievement = DB::transaction(function () use ($goal, $data) {
            $achievement = AchievementReport::create($data);
            $goal->update(['status' => GoalPostStatus::Achieved]);

            return $achievement;
        });

        return redirect()->route('posts.show', ['type' => 'achievement', 'id' => $achievement->id]);

    }
}
