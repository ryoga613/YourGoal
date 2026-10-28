@extends('layouts.app')
@section('title', '投稿を探す')
@section('content')
<div class="page-shell" style="grid-template-columns:220px minmax(0,1fr)">
    <aside class="sidebar"><a class="nav-link" href="{{ url('/') }}"><span class="nav-icon">⌂</span><span>ホーム</span></a><a class="nav-link active" href="{{ url('/search') }}"><span class="nav-icon">⌕</span><span>投稿を探す</span></a><a class="nav-link" href="{{ url('/posts/create') }}"><span class="nav-icon">＋</span><span>投稿を作成</span></a></aside>
    <main>
        <div class="page-heading"><div><h1>気になる投稿を探そう</h1><p class="subheading">キーワードやジャンルから、あなたの興味に合う投稿を見つけましょう。</p></div></div>
        <form class="form-card" method="GET" action="{{ \Illuminate\Support\Facades\Route::has('posts.search') ? route('posts.search') : url('/search') }}">
            <div class="form-group"><label for="keyword">キーワード</label><input class="form-control" id="keyword" name="keyword" value="{{ request('keyword') }}" placeholder="タイトルや本文から検索"></div>
            <div class="choice-grid">
                <div class="form-group"><label for="genre_id">ジャンル</label><select class="form-control" id="genre_id" name="genre_id"><option value="">すべてのジャンル</option>@foreach(($genres ?? []) as $genre)<option value="{{ $genre->id }}" @selected(request('genre_id') == $genre->id)>{{ $genre->name }}</option>@endforeach</select></div>
                <div class="form-group"><label for="type">投稿種類</label><select class="form-control" id="type" name="type"><option value="">すべての投稿</option><option value="goal" @selected(request('type')==='goal')>目標宣言</option><option value="progress" @selected(request('type')==='progress')>進捗報告</option><option value="achievement" @selected(request('type')==='achievement')>達成報告</option><option value="knowledge" @selected(request('type')==='knowledge')>学び共有</option></select></div>
            </div>
            <div class="form-actions"><a class="btn btn-outline" href="{{ url('/search') }}">条件をクリア</a><button class="btn btn-primary" type="submit">⌕ 検索する</button></div>
        </form>
        <div style="margin-top:24px">
            <div class="page-heading"><h2 style="font-size:18px">検索結果</h2><span class="post-meta">{{ isset($posts) && method_exists($posts,'total') ? $posts->total().'件' : '' }}</span></div>
            @forelse(($posts ?? []) as $post)
                <article class="post-card"><div class="post-head"><div class="avatar">{{ mb_substr(data_get($post,'user.name','U'),0,1) }}</div><div><div class="post-author">{{ data_get($post,'user.name','ユーザー') }}</div><div class="post-meta">{{ data_get($post,'created_at') }}</div></div></div><h3 class="post-title">{{ data_get($post,'title','投稿タイトル') }}</h3><p class="post-body">{{ \Illuminate\Support\Str::limit(data_get($post,'body',data_get($post,'content','')),180) }}</p></article>
            @empty
                <div class="empty-state"><h3>検索結果がここに表示されます</h3><p>キーワードや条件を指定して検索してください。</p></div>
            @endforelse
            @if(isset($posts) && method_exists($posts,'links')){{ $posts->withQueryString()->links() }}@endif
        </div>
    </main>
</div>
@endsection
