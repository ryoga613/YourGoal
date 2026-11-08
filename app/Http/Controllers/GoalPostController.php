<?php

namespace App\Http\Controllers;

use App\Enums\GoalPostStatus;
use App\Http\Requests\StoreUserGoalPostRequest;
use App\Models\GoalPost;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class GoalPostController extends Controller
{
    /**
     * 目標宣言フォームを表示
     */
    public function create()
    {
        Gate::authorize('create', GoalPost::class);

        return view('goals.create');
    }

    /**
     * 目標を作成し初期状態を挑戦中にする
     */
    public function store(StoreUserGoalPostRequest $request)
    {
        Gate::authorize('create', GoalPost::class);

        $validated = $request->validated();

        $data = [
            'title' => $validated['title'],
            'body' => $validated['body'] ?? null,
            'image_path' => $request->file('image')?->store('goal_images', 'public'),
            'user_id' => Auth::id(),
            'status' => GoalPostStatus::InProgress,
        ];

        $goal = GoalPost::create($data);

        return redirect()->route('goals.show', $goal);
    }

    /**
     * 目標と関連する進捗・達成報告を表示
     */
    public function show(GoalPost $goal)
    {
        Gate::authorize('view', $goal);

        $goal->loadCount(['post_likes', 'comments']);

        return view('goals.show', compact('goal'));
    }
}
