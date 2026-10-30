<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AchievementReport;
use App\Models\Comment;
use App\Models\GoalPost;
use App\Models\KnowledgePost;
use App\Models\ProgressReport;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * ユーザー数・投稿数・コメント数・ジャンル数を表示
     */
    public function index()
    {
        // 投稿は4種類のテーブルに分かれているため、種類ごとに数えて合計する
        $postCounts = [
            '目標宣言' => GoalPost::count(),
            '進捗報告' => ProgressReport::count(),
            '達成報告' => AchievementReport::count(),
            '学び共有' => KnowledgePost::count(),
        ];

        return view('admin.dashboard', [
            // 管理者自身は管理対象ではないため、一般ユーザーのみ数える
            'userCount' => User::where('role', UserRole::User)->count(),
            'postCount' => array_sum($postCounts),
            'postCounts' => $postCounts,
            // 返信も含めたコメント総数
            'todayCommentCount' => Comment::whereDate('created_at', today())->count(),
        ]);
    }
}
