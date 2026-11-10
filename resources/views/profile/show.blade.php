{{--
    ユーザーのプロフィール（/users/{user}）
    必要なデータ（ProfileController@show から渡す）:
    - $user          表示するユーザー（name, bio, profile_image_path）
                     投稿数の表示に loadCount(['goal_posts', 'progress_reports', 'achievement_reports', 'knowledge_posts']) が必要
    - $goals         そのユーザーの目標（新しい順）。status で「挑戦中」などのタグを出す
    - $achievements  そのユーザーの達成報告（新しい順）。with('goal_post') で達成した目標も読み込む
    - $posts         そのユーザーの最近の投稿（4種類をまとめて新しい順、10件程度）
    渡されなかったものは空として表示する
--}}
@extends('layouts.app')
@section('title', $user->name.'さんのプロフィール')
@section('content')
@php
    $goals = $goals ?? collect();
    $achievements = $achievements ?? collect();
    $posts = $posts ?? collect();
    $isMe = auth()->id() === $user->id;
@endphp
<div class="page-shell" style="grid-template-columns:220px minmax(0,760px)">
    <aside class="sidebar">
        <a class="nav-link" href="{{ route('home') }}"><span class="nav-icon">⌂</span><span>ホーム</span></a>
        <a class="nav-link" href="{{ route('posts.search') }}"><span class="nav-icon">⌕</span><span>投稿を探す</span></a>
        <a class="nav-link {{ $isMe ? 'active' : '' }}" href="{{ route('users.show', auth()->id()) }}"><span class="nav-icon">♙</span><span>マイプロフィール</span></a>
    </aside>

    <main>
        @if (session('status'))<div class="flash">{{ session('status') }}</div>@endif

        {{-- プロフィールカード --}}
        <section class="post-card">
            <div class="post-head" style="align-items:flex-start">
                <x-avatar :user="$user" :size="72" />
                <div style="flex:1;min-width:0">
                    <h1 style="font-size:22px;margin:4px 0 6px">{{ $user->name }}</h1>
                    <div class="post-meta">{{ $user->created_at?->format('Y年n月') }}から参加</div>
                </div>
                @if ($isMe)
                    <a class="btn btn-outline btn-sm" href="{{ route('profile.edit') }}">プロフィールを編集</a>
                @endif
            </div>

            @if (filled($user->bio))
                <p class="post-body" style="margin-top:16px;overflow-wrap:anywhere">{{ $user->bio }}</p>
            @else
                <p class="post-meta" style="margin-top:16px">{{ $isMe ? '自己紹介がまだありません。プロフィールを編集して、あなたのことを紹介しましょう。' : '自己紹介はまだありません。' }}</p>
            @endif

            <div class="post-footer">
                <span><strong>{{ $user->postCount() }}</strong> 投稿</span>
                <span><strong>{{ $user->goal_posts_count ?? $goals->count() }}</strong> 目標</span>
                <span><strong>{{ $user->achievement_reports_count ?? $achievements->count() }}</strong> 達成</span>
            </div>
        </section>

        {{-- 目標一覧 --}}
        <h2 style="font-size:18px;margin:26px 0 12px">目標</h2>
        @forelse ($goals as $goal)
            <a class="post-card" style="display:flex;align-items:center;gap:10px" href="{{ route('posts.show', ['type' => 'goal', 'id' => $goal->id]) }}">
                <span class="tag goal">{{ $goal->status?->label() }}</span>
                <span class="post-title" style="margin:0;flex:1;min-width:0;overflow-wrap:anywhere">{{ $goal->title }}</span>
                <span class="post-meta">{{ $goal->created_at?->format('Y/m/d') }}</span>
            </a>
        @empty
            <p class="post-meta">まだ目標はありません。</p>
        @endforelse

        {{-- 達成履歴 --}}
        <h2 style="font-size:18px;margin:26px 0 12px">達成履歴</h2>
        @forelse ($achievements as $achievement)
            <a class="post-card" style="display:block" href="{{ route('posts.show', ['type' => 'achievement', 'id' => $achievement->id]) }}">
                <div class="post-meta">{{ $achievement->created_at?->format('Y/m/d') }} に達成</div>
                <div class="post-title" style="margin:6px 0 4px;overflow-wrap:anywhere">🎉 {{ $achievement->goal_post?->title ?? $achievement->title }}</div>
                @if ($achievement->goal_post && $achievement->title)
                    <div class="post-body">{{ $achievement->title }}</div>
                @endif
            </a>
        @empty
            <p class="post-meta">まだ達成した目標はありません。</p>
        @endforelse

        {{-- 最近の投稿（4種類） --}}
        <h2 style="font-size:18px;margin:26px 0 12px">最近の投稿</h2>
        @forelse ($posts as $post)
            @php($postType = $post->postType())
            <a class="post-card" style="display:block" href="{{ route('posts.show', ['type' => $postType->value, 'id' => $post->id]) }}">
                <div class="post-head">
                    <span class="tag {{ $postType->tagClass() }}">{{ $postType->label() }}</span>
                    <span class="post-meta">{{ $post->created_at?->format('Y/m/d H:i') }}</span>
                </div>
                @if ($post->title)
                    <h3 class="post-title" style="overflow-wrap:anywhere">{{ $post->title }}</h3>
                @endif
                <p class="post-body">{{ \Illuminate\Support\Str::limit($post->body, 120) }}</p>
            </a>
        @empty
            <p class="post-meta">まだ投稿はありません。</p>
        @endforelse
        
        @if ($posts instanceof \Illuminate\Contracts\Pagination\Paginator)
    {{ $posts->links() }}
@endif    
    </main>
</div>
@endsection
