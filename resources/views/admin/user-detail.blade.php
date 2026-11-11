@extends('layouts.app')
@section('title', 'ユーザー詳細')
@section('content')
@php($commentCount = $user->comments()->count())
<div class="admin-shell">
    <div class="page-heading">
        <div><h1>ユーザー詳細</h1><p class="subheading">ID: {{ $user->id }}</p></div>
        <a class="btn btn-outline" href="{{ route('admin.users.index') }}">← ユーザー一覧</a>
    </div>

    <div class="form-card">
        <dl class="detail-list">
            <dt>名前</dt><dd>{{ $user->name }}</dd>
            <dt>メールアドレス</dt><dd>{{ $user->email }}</dd>
            <dt>自己紹介</dt><dd class="post-body">{{ $user->bio ?? '—' }}</dd>
            <dt>投稿</dt><dd>{{ number_format($posts->total()) }}件</dd>
            <dt>コメント</dt><dd>{{ number_format($commentCount) }}件</dd>
            <dt>登録日時</dt><dd>{{ $user->created_at->format('Y/m/d H:i') }}</dd>
        </dl>
    </div>

    <div class="form-card" style="margin-top:18px">
        <h2 style="font-size:17px;margin-top:0">投稿（{{ number_format($posts->total()) }}件）</h2>
        @forelse($posts as $post)
            <div class="reply-item">
                <div class="post-meta"><span class="tag {{ $post->postType->tagClass() }}">{{ $post->postType->label() }}</span> ・ {{ $post->created_at->format('Y/m/d H:i') }}</div>
                <a style="font-weight:700" href="{{ route('admin.posts.show', ['type' => $post->postType->value, 'id' => $post->id]) }}">{{ $post->title ?? '（タイトルなし）' }}</a>
            </div>
        @empty
            <p class="text-muted" style="margin:0">投稿はありません。</p>
        @endforelse
        {{ $posts->links('pagination::bootstrap-4') }}
    </div>

    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" style="margin-top:18px;text-align:right"
          onsubmit="return confirm('このユーザーを削除しますか？\n投稿{{ $posts->total() }}件・コメント{{ $commentCount }}件と、それらへのいいねも一緒に削除されます。')">
        @csrf
        @method('DELETE')
        <button class="btn btn-danger" type="submit">このユーザーを削除</button>
    </form>
</div>
@endsection
