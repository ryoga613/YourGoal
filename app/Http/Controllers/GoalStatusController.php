<?php

namespace App\Http\Controllers;

use App\Enums\GoalPostStatus;
use App\Http\Requests\UpdateGoalStatusRequest;
use App\Models\GoalPost;

class GoalStatusController extends Controller
{
    /**
     * 目標ステータスを変更
     */
    public function update(UpdateGoalStatusRequest $request, GoalPost $goal)
    {
        // 本人かどうか・達成済みでないかは UpdateGoalStatusRequest の authorize で確認済み
        $status = GoalPostStatus::from($request->validated('status'));
        $goal->update(['status' => $status]);

        return redirect()
            ->route('goals.show', $goal)
            ->with('status', "ステータスを「{$status->label()}」に変更しました。");
    }
}
