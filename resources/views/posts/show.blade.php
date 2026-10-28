@extends('layouts.app')
@section('title', data_get($post,'title','投稿詳細'))
@section('content')
<div class="page-shell" style="grid-template-columns:220px minmax(0,760px)">
    <aside class="sidebar"><a class="nav-link" href="{{ url('/') }}"><span class="nav-icon">⌂</span><span>ホーム</span></a><a class="nav-link" href="{{ url('/search') }}"><span class="nav-icon">⌕</span><span>投稿を探す</span></a></aside>
    <main>
        <a href="{{ url('/') }}" class="post-meta">← ホームに戻る</a>
        <article class="post-card" style="margin-top:15px">
            <div class="post-head"><div class="avatar">{{ mb_substr(data_get($post,'user.name','U'),0,1) }}</div><div><div class="post-author">{{ data_get($post,'user.name','ユーザー') }}</div><div class="post-meta">{{ data_get($post,'created_at') }}</div></div></div>
            <h1 class="post-title" style="font-size:24px">{{ data_get($post,'title','投稿タイトル') }}</h1>
            <p class="post-body">{{ data_get($post,'body',data_get($post,'content','')) }}</p>
            @if(data_get($post,'image_path'))<img class="post-image" src="{{ asset('storage/'.data_get($post,'image_path')) }}" alt="投稿画像">@endif
            <div class="post-footer"><button class="post-action" type="button">♡ {{ data_get($post,'likes_count',0) }} いいね</button><span>♧ {{ data_get($post,'comments_count',0) }} コメント</span></div>
        </article>
        <section class="form-card">
            <h2 style="font-size:17px;margin-top:0">コメントする</h2>
            <form method="POST" action="{{ \Illuminate\Support\Facades\Route::has('comments.store') ? route('comments.store', ['type'=>request()->route('type'),'id'=>request()->route('id')]) : '#' }}">
                @csrf
                <div class="form-group"><textarea class="form-control" name="body" rows="3" placeholder="応援や感想をコメントしよう" required>{{ old('body') }}</textarea>@error('body')<div class="form-error">{{ $message }}</div>@enderror</div>
                <div class="form-actions"><button class="btn btn-primary" type="submit">コメントを投稿</button></div>
            </form>
            <h3 style="font-size:15px;margin-top:26px">みんなのコメント</h3>
            @forelse(($post->comments ?? []) as $comment)
                <div style="display:flex;gap:10px;padding:14px 0;border-top:1px solid var(--line)"><div class="avatar">{{ mb_substr(data_get($comment,'user.name','U'),0,1) }}</div><div><strong>{{ data_get($comment,'user.name','ユーザー') }}</strong><div class="post-meta">{{ data_get($comment,'created_at') }}</div><p style="margin:7px 0">{{ data_get($comment,'body') }}</p></div></div>
            @empty
                <p class="post-meta">まだコメントはありません。最初のコメントを投稿してみましょう。</p>
            @endforelse
        </section>
    </main>
</div>
@endsection
