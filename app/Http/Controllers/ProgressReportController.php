<?php

namespace App\Http\Controllers;

use App\Models\GoalPost;
use App\Models\ProgressReport;
use Illuminate\Http\Request;

class ProgressReportController extends Controller
{
    /**
     * 対象目標を指定した進捗報告フォームを表示
     */
    public function create(GoalPost $goal)
    {
        //
    }

    /**
     * 進捗・気づきを保存し目標に紐づける
     */
    public function store(Request $request, GoalPost $goal)
    {
        //
    }

    /**
     * 自分の進捗報告のタイトル・本文・画像を更新
     */
    public function update(Request $request, ProgressReport $progressReport)
    {
        //
    }

    /**
     * 進捗報告を削除
     */
    public function destroy(ProgressReport $progressReport)
    {
        //
    }
}
