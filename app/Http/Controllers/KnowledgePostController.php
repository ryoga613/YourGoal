<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserKnowledgePostRequest;
use App\Models\KnowledgePost;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class KnowledgePostController extends Controller
{
    /**
     * 学び・知識共有フォームを表示
     */
    public function create()
    {
        Gate::authorize('create', KnowledgePost::class);

        return view('knowledge-posts.create');
    }

    /**
     * 学び・知識共有を保存
     */
    public function store(StoreUserKnowledgePostRequest $request)
    {
        Gate::authorize('create', KnowledgePost::class);

        $validated = $request->validated();

        $data = [
            'title' => $validated['title'],
            'body' => $validated['body'] ?? null,
            'image_path' => $request->file('image')?->store('knowledge_images', 'public'),
            'user_id' => Auth::id(),
        ];

        $knowledge = KnowledgePost::create($data);

        return redirect()->route('knowledge-posts.show', $knowledge);
    }

    /**
     * 学び・知識共有の詳細を表示
     */
    public function show(KnowledgePost $knowledge)
    {
        Gate::authorize('view', KnowledgePost::class);

        $knowledge->load(['user', 'comments.user'])->loadCount(['post_likes', 'comments']);

        return view('knowledge-posts.show', compact('knowledge'));
    }
}
