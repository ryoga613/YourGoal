<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProgressReportRequest;
use App\Models\GoalPost;
use App\Models\ProgressReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgressReportController extends Controller
{
    /**
     * 対象目標を指定した進捗報告フォームを表示
     */
    public function create(GoalPost $goal)
    {
        return view('progress-reports.create', compact('goal'));
    }

    /**
     * 進捗・気づきを保存し目標に紐づける
     */
    public function store(StoreProgressReportRequest $request, GoalPost $goal)
    {
        // 進捗を報告できるのは目標を宣言した本人だけ
        abort_if($goal->user_id !== Auth::id(), 403);

        $validated = $request->validated();

        $data = [
            'title' => $validated['title'] ?? null,
            'body' => $validated['body'] ?? null,
            'image_path' => $request->file('image')?->store('progress_images', 'public'),
            'user_id' => Auth::id(),
        ];

        // goal_post_id は目標のリレーション経由で自動で入る
        $goal->progress_reports()->create($data);

        return redirect()->route('goals.show', $goal);
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
