<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminUserIndexRequest;
use App\Models\User;
use App\Support\EscapeLike;

class UserController extends Controller
{
    use EscapeLike;

    /**
     * ユーザー一覧・検索
     */
    public function index(AdminUserIndexRequest $request)
    {
        $validated = $request->validated();
        $keyword = $validated['keyword'] ?? null;
        $userQuery = User::where('role', 'user')->withCount(['goal_posts', 'progress_reports', 'achievement_reports', 'knowledge_posts', 'comments'])
            ->latest();
        // $postCount = $userQuery as ->postCount

        if ($keyword) {
            $userQuery->where(function ($query) use ($keyword) {
                $query->Where('email', 'like', '%'.$this->escapeLike($keyword).'%')
                    ->orWhere('name', 'like', '%'.$this->escapeLike($keyword).'%');
            });
        }

        $users = $userQuery->paginate(20)->withQueryString();

        return view('admin.user-index', compact('keyword', 'users'));
    }

    /**
     * ユーザー情報と関連投稿を表示
     */
    public function show(User $user)
    {
        return view('admin.user-detail', compact('user'));
    }

    /**
     * ユーザーを削除
     */
    public function destroy(User $user)
    {
        // 管理者は管理対象ではないため削除できない（自分自身を消してしまうのも防ぐ）
        if ($user->isAdmin()) {
            abort(403);
        }

        // 投稿・コメント・いいねは、一緒に削除される
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'ユーザーを削除しました。');
    }
}
