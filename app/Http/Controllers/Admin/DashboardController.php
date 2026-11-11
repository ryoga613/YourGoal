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
     * ユーザー数・投稿数・コメント数を表示
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
            // 今日のコメント数（返信も含む）
            // whereDate は DATE(created_at) と関数を通して比べるためインデックスが使えない。
            // 「今日の0時以上・明日の0時より前」の範囲で比べれば、created_at のインデックスが使える
            'todayCommentCount' => Comment::where('created_at', '>=', today())
                ->where('created_at', '<', today()->addDay())
                ->count(),
        ]);
    }
}
