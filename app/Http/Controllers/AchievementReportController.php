<?php

namespace App\Http\Controllers;

use App\Enums\GoalPostStatus;
use App\Http\Requests\StoreAchievementReportRequest;
use App\Models\AchievementReport;
use App\Models\GoalPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AchievementReportController extends Controller
{
    /**
     * 達成報告フォームを表示
     */
    public function create(GoalPost $goal)
    {
        // 達成を報告できるのは目標を宣言した本人だけ
        abort_if($goal->user_id !== Auth::id(), 403);
        // 達成済みの目標には、もう達成報告できない
        abort_if($goal->status === GoalPostStatus::Achieved, 403);

        return view('achievement-reports.create', compact('goal'));
    }

    /**
     * 達成報告を保存し、対象目標を達成済みに変更
     */
    public function store(StoreAchievementReportRequest $request, GoalPost $goal)
    {
        abort_if($goal->user_id !== Auth::id(), 403);
        abort_if($goal->status === GoalPostStatus::Achieved, 403);

        $validated = $request->validated();

        $data = [
            'title' => $validated['title'],
            'body' => $validated['body'] ?? null,
            'image_path' => $request->file('image')?->store('achievement_images', 'public'),
            'user_id' => Auth::id(),
        ];

        // 報告の保存とステータス変更は、どちらかだけ成功しないようにまとめて行う
        DB::transaction(function () use ($goal, $data) {
            $goal->achievement_reports()->create($data);
            $goal->update(['status' => GoalPostStatus::Achieved]);
        });

        return redirect()->route('goals.show', $goal);
    }

    /**
     * 自分の達成報告を更新
     */
    public function update(Request $request, AchievementReport $achievementReport)
    {
        //
    }

    /**
     * 達成報告を削除し、条件に応じ目標を挑戦中へ戻す
     */
    public function destroy(AchievementReport $achievementReport)
    {
        //
    }
}
