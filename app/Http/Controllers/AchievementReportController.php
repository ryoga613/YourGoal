<?php

namespace App\Http\Controllers;

use App\Models\AchievementReport;
use App\Models\GoalPost;
use Illuminate\Http\Request;

class AchievementReportController extends Controller
{
    /**
     * 達成報告フォームを表示
     */
    public function create(GoalPost $goal)
    {
        //
    }

    /**
     * 達成報告を保存し、対象目標を達成済みに変更
     */
    public function store(Request $request, GoalPost $goal)
    {
        //
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
