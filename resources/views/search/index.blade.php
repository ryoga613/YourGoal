@extends('layouts.app')
@section('title', '投稿を探す')
@section('content')
<div class="page-shell" style="grid-template-columns:220px minmax(0,1fr)">
    <aside class="sidebar"><a class="nav-link" href="{{ url('/') }}"><span class="nav-icon">⌂</span><span>ホーム</span></a><a class="nav-link active" href="{{ url('/search') }}"><span class="nav-icon">⌕</span><span>投稿を探す</span></a><a class="nav-link" href="{{ url('/posts/create') }}"><span class="nav-icon">＋</span><span>投稿を作成</span></a></aside>
    <main>
        <div class="page-heading"><div><h1>気になる投稿を探そう</h1><p class="subheading">キーワードや投稿種類から、あなたの興味に合う投稿を見つけましょう。</p></div></div>
        <form class="form-card" method="GET" action="{{ route('posts.search') }}">
            <div class="form-group">
                <label for="keyword">キーワード</label>
                <input class="form-control" id="keyword" name="keyword" value="{{ request('keyword') }}" placeholder="タイトル・本文・ユーザー名から検索">
                @error('keyword')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="choice-grid">
                <div class="form-group">
                    <label for="type">投稿種類</label>
                    <select class="form-control" id="type" name="type">
                        <option value="">すべての投稿</option>
                        @foreach (\App\Enums\PostType::cases() as $postType)
                            <option value="{{ $postType->value }}" @selected(request('type') === $postType->value)>{{ $postType->label() }}</option>
                        @endforeach
                    </select>
                    @error('type')<div class="form-error">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="form-actions"><a class="btn btn-outline" href="{{ route('posts.search') }}">条件をクリア</a><button class="btn btn-primary" type="submit">⌕ 検索する</button></div>
        </form>
        <div style="margin-top:24px">
            <div class="page-heading"><h2 style="font-size:18px">検索結果</h2><span class="post-meta">{{ $posts->total() }}件</span></div>
            @forelse ($posts as $post)
                @php($postType = $post->postType())
                <article class="post-card">
                    <div class="post-head">
                        <x-avatar :user="$post->user" />
                        <div>
                            <div class="post-author">
                                {{ $post->user?->name ?? '退会済みユーザー' }}
                                <span class="tag {{ $postType->tagClass() }}" style="margin-left:8px">{{ $postType->label() }}</span>
                            </div>
                            <p class="post-meta">{{ $post->created_at?->format('Y/m/d H:i') }}</p>
                        </div>
                    </div>
                    <a href="{{ route('posts.show', ['type' => $postType->value, 'id' => $post->id]) }}">
                        @if ($post->title)
                            <h3 class="post-title">{{ $post->title }}</h3>
                        @endif
                        <p class="post-body">{{ \Illuminate\Support\Str::limit($post->body, 180) }}</p>
                    </a>
                </article>
            @empty
                @if ($keyword !== null || request()->filled('type'))
                    <div class="empty-state"><h3>該当する投稿はありません</h3><p>キーワードや条件を変えて、もう一度検索してください。</p></div>
                @else
                    <div class="empty-state"><h3>まだ投稿がありません</h3><p>最初の投稿をしてみましょう。</p></div>
                @endif
            @endforelse
            {{ $posts->links() }}
        </div>
    </main>
</div>
@endsection
