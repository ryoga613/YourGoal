<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PostType;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminUserIndexRequest;
use App\Models\User;
use App\Support\EscapeLike;
use Illuminate\Support\Facades\DB;

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
        $userQuery = User::where('role', UserRole::User)->withCount(['goal_posts', 'progress_reports', 'achievement_reports', 'knowledge_posts', 'comments'])
            ->latest()->latest('id');

        if ($keyword) {
            $userQuery->where(function ($query) use ($keyword) {
                $query->where('email', 'like', '%'.$this->escapeLike($keyword).'%')
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

        $union = null;
        foreach (PostType::cases() as $postType) {
            $query = $postType->modelClass()::query()
                ->selectRaw('? as type', [$postType->value])
                ->addSelect(['id', 'created_at'])
                ->where('user_id', $user->id);

            $union = $union ? $union->unionAll($query) : $query;
        }

        $rows = DB::query()
            ->fromSub($union, 'posts')
            ->orderByDesc('created_at')
            ->orderBy('type')
            ->orderByDesc('id')
            ->paginate(5);
        $postsByType = $rows->getCollection()
            ->groupBy('type')
            ->map(fn ($items, $value) => PostType::from($value)->modelClass()::query()
                ->findMany($items->pluck('id'))
                ->keyBy('id'));

        // ④ 並び順のまま、投稿モデルに置き換える(ビューで使う種類も持たせる)
        $posts = $rows->through(function ($row) use ($postsByType) {
            $post = $postsByType[$row->type][$row->id];
            $post->postType = PostType::from($row->type);

            return $post;
        });

        return view('admin.user-detail', compact('user', 'posts'));
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
