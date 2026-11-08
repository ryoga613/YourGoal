<?php

namespace App\Http\Controllers;

use App\Models\AchievementReport;
use App\Models\GoalPost;
use App\Models\KnowledgePost;
use App\Models\PostLike;
use App\Models\ProgressReport;
use Illuminate\Support\Facades\Gate;

class LikeController extends Controller
{
    /**
     * 対象投稿にいいねを登録
     */
    public function store(string $type, int $id)
    {
        Gate::authorize('create', PostLike::class);

        if ($type === 'goal') {
            $post = GoalPost::findOrFail($id);
            $foreignKey = 'goal_post_id';
        } elseif ($type === 'progress') {
            $post = ProgressReport::findOrFail($id);
            $foreignKey = 'progress_report_id';
        } elseif ($type === 'achievement') {
            $post = AchievementReport::findOrFail($id);
            $foreignKey = 'achievement_report_id';
        } elseif ($type === 'knowledge') {
            $post = KnowledgePost::findOrFail($id);
            $foreignKey = 'knowledge_post_id';
        } else {
            return back()->with('message', '不正な投稿タイプです。');
        }

        PostLike::firstOrCreate([
            $foreignKey => $post->id,
            'user_id' => auth()->id(),
        ]);

        return back();
    }

    /**
     * 対象投稿のいいねを解除
     */
    public function destroy(string $type, int $id)
    {

        if ($type === 'goal') {
            $post = GoalPost::findOrFail($id);
            $foreignKey = 'goal_post_id';
        } elseif ($type === 'progress') {
            $post = ProgressReport::findOrFail($id);
            $foreignKey = 'progress_report_id';
        } elseif ($type === 'achievement') {
            $post = AchievementReport::findOrFail($id);
            $foreignKey = 'achievement_report_id';
        } elseif ($type === 'knowledge') {
            $post = KnowledgePost::findOrFail($id);
            $foreignKey = 'knowledge_post_id';
        } else {
            return back()->with('message', '不正な投稿タイプです。');
        }

        $postLike = PostLike::where([
            $foreignKey => $post->id,
            'user_id' => auth()->id(),
        ])->first();

        if ($postLike) {
            Gate::authorize('delete', $postLike);
            $postLike->delete();
        }

        return back();
    }
}
