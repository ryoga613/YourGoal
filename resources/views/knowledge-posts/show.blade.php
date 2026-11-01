{{--
    学び・知識共有の詳細
    必要なデータ（コントローラーから $knowledge で渡す）:
    - $knowledge->user                         投稿者（name, profile_image_path）
    - $knowledge->post_likes_count             loadCount('post_likes')
    - $knowledge->comments_count               loadCount('comments')
    - $knowledge->comments（各 comment->user）  load('comments.user')
--}}
@extends('layouts.app')
@section('title', $knowledge->title)
@section('content')
<div class="page-shell" style="grid-template-columns:220px minmax(0,760px)">
    <aside class="sidebar">
        <a class="nav-link" href="{{ route('home') }}"><span class="nav-icon">⌂</span><span>ホーム</span></a>
        <a class="nav-link" href="{{ route('posts.create') }}"><span class="nav-icon">＋</span><span>投稿を作成</span></a>
        <a class="nav-link" href="{{ route('posts.search') }}"><span class="nav-icon">⌕</span><span>投稿を探す</span></a>
    </aside>

    <main>
        <div class="page-heading">
            <div>
                <span class="tag knowledge">学び・知識共有</span>
                <h1 style="margin-top:10px">{{ $knowledge->title }}</h1>
            </div>
        </div>

        <article class="post-card">
            <div class="post-head">
                @if($knowledge->user->profile_image_path)
                    <img class="avatar" src="{{ asset('storage/'.$knowledge->user->profile_image_path) }}" alt="">
                @else
                    <div class="avatar">{{ mb_substr($knowledge->user->name, 0, 1) }}</div>
                @endif
                <div>
                    <div class="post-author">{{ $knowledge->user->name }}</div>
                    <div class="post-meta">{{ $knowledge->created_at->format('Y年n月j日') }}</div>
                </div>
            </div>

            <p class="post-body" style="margin-top:18px">{{ $knowledge->body }}</p>

            @if($knowledge->image_path)
                <img class="post-image" src="{{ asset('storage/'.$knowledge->image_path) }}" alt="投稿画像">
            @endif

            <div class="post-footer">
                <span>♡ {{ $knowledge->post_likes_count }} いいね</span>
                <span>♧ {{ $knowledge->comments_count }} コメント</span>
            </div>
        </article>

        <h2 style="font-size:18px;margin:24px 0 14px">みんなのコメント</h2>
        @forelse($knowledge->comments as $comment)
            <article class="post-card">
                <div class="post-head">
                    <div class="avatar">{{ mb_substr($comment->user->name, 0, 1) }}</div>
                    <div>
                        <div class="post-author">{{ $comment->user->name }}</div>
                        <div class="post-meta">{{ $comment->created_at->format('Y年n月j日') }}</div>
                    </div>
                </div>
                <p class="post-body">{{ $comment->body }}</p>
            </article>
        @empty
            <div class="empty-state">まだコメントはありません。</div>
        @endforelse
    </main>
</div>
@endsection
