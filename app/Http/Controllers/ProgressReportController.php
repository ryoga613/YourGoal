<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProgressReportRequest;
use App\Models\GoalPost;
use App\Models\ProgressReport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ProgressReportController extends Controller
{
    /**
     * 対象目標を指定した進捗報告フォームを表示
     */
    public function create(GoalPost $goal)
    {
        Gate::authorize('create', [ProgressReport::class, $goal]);

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

        return redirect()->route('posts.show', ['type' => 'goal', 'id' => $goal->id]);
    }
}
