@extends('layouts.app')
@section('title', data_get($user,'name','プロフィール'))
@section('content')
<div class="page-shell" style="grid-template-columns:220px minmax(0,1fr)">
    <aside class="sidebar"><a class="nav-link" href="{{ url('/') }}"><span class="nav-icon">⌂</span><span>ホーム</span></a><a class="nav-link" href="{{ url('/profile/edit') }}"><span class="nav-icon">⚙</span><span>プロフィール編集</span></a></aside>
    <main>
        <div class="profile-cover"></div>
        <section class="profile-info">
            <div class="avatar profile-avatar">{{ mb_substr(data_get($user,'name','U'),0,1) }}</div>
            <div class="profile-name">{{ data_get($user,'name','ユーザー') }}</div>
            <p style="color:var(--muted);margin:8px 0">{{ data_get($user,'bio','自己紹介はまだありません。') }}</p>
            <div class="profile-stats"><span><strong>{{ data_get($user,'posts_count',0) }}</strong> 投稿</span><span><strong>{{ data_get($user,'goals_count',0) }}</strong> 目標</span><span><strong>{{ data_get($user,'achievements_count',0) }}</strong> 達成</span></div>
            @auth @if((int)auth()->id()===(int)data_get($user,'id'))<a class="btn btn-outline" style="margin-top:16px" href="{{ url('/profile/edit') }}">プロフィールを編集</a>@endif @endauth
        </section>
        <div class="tabs" style="margin-top:22px"><a class="tab active" href="#">投稿</a><a class="tab" href="#">目標</a><a class="tab" href="#">達成履歴</a></div>
        @forelse(($posts ?? []) as $post)
            <article class="post-card"><div class="post-head"><span class="tag">{{ data_get($post,'type','投稿') }}</span><span class="post-meta">{{ data_get($post,'created_at') }}</span></div><h3 class="post-title">{{ data_get($post,'title','投稿タイトル') }}</h3><p class="post-body">{{ \Illuminate\Support\Str::limit(data_get($post,'body',data_get($post,'content','')),180) }}</p></article>
        @empty
            <div class="empty-state">投稿がまだありません。</div>
        @endforelse
    </main>
</div>
@endsection
