@extends('layouts.app')
@section('title', data_get($goal,'title','目標詳細'))
@section('content')
<div class="page-shell" style="grid-template-columns:220px minmax(0,760px)">
    <aside class="sidebar"><a class="nav-link" href="{{ url('/') }}"><span class="nav-icon">⌂</span><span>ホーム</span></a><a class="nav-link" href="{{ url('/posts/create') }}"><span class="nav-icon">＋</span><span>投稿を作成</span></a><a class="nav-link" href="{{ url('/search') }}"><span class="nav-icon">⌕</span><span>投稿を探す</span></a></aside>
    <main>
        <div class="page-heading"><div><span class="tag goal">目標宣言</span><h1 style="margin-top:10px">{{ $goal->title ?? '目標タイトル' }}</h1><p class="subheading">目標と、ここまでの歩み。</p></div></div>
        <article class="post-card">
            <div class="post-head"><div class="avatar">{{ mb_substr(data_get($goal,'user.name','U'),0,1) }}</div><div><div class="post-author">{{ data_get($goal,'user.name','ユーザー') }}</div><div class="post-meta">{{ data_get($goal,'created_at') }}</div></div><span class="tag" style="margin-left:auto">{{ data_get($goal,'status','挑戦中') }}</span></div>
            <p class="post-body" style="margin-top:18px">{{ $goal->body ?? $goal->description ?? '' }}</p>
            @if(data_get($goal,'image_path'))<img class="post-image" src="{{ asset('storage/'.$goal->image_path) }}" alt="目標画像">@endif
            <div class="post-footer"><span>♡ {{ data_get($goal,'likes_count',0) }} いいね</span><span>♧ {{ data_get($goal,'comments_count',0) }} コメント</span></div>
        </article>
        @if(auth()->check() && (int)auth()->id() === (int)data_get($goal,'user_id'))
            <div class="composer" style="flex-wrap:wrap"><strong style="width:100%">次の一歩を記録しよう</strong><a class="btn btn-light" href="{{ url('/goals/'.$goal->id.'/progress-reports/create') }}">＋ 進捗を報告</a><a class="btn btn-primary" href="{{ url('/goals/'.$goal->id.'/achievement-reports/create') }}">✓ 達成を報告</a></div>
        @endif
        <h2 style="font-size:18px;margin:24px 0 14px">進捗の記録</h2>
        @forelse(($goal->progressReports ?? []) as $report)
            <article class="post-card"><div class="post-head"><span class="tag progress">進捗報告</span><span class="post-meta">{{ data_get($report,'created_at') }}</span></div><h3 class="post-title">{{ data_get($report,'title','進捗報告') }}</h3><p class="post-body">{{ data_get($report,'body',data_get($report,'content','')) }}</p></article>
        @empty
            <div class="empty-state">まだ進捗報告はありません。最初の記録を残してみましょう。</div>
        @endforelse
    </main>
</div>
@endsection
