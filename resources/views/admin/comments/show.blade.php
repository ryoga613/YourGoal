@extends('layouts.app')
@section('title', 'コメント詳細')
@section('content')
@php($postType = $comment->postType())
@php($post = $comment->post())
<div class="admin-shell">
    <div class="page-heading">
        <div><h1>コメント詳細</h1><p class="subheading">ID: {{ $comment->id }}</p></div>
        <a class="btn btn-outline" href="{{ route('admin.comments.index') }}">← コメント一覧</a>
    </div>

    <div class="form-card">
        <dl class="detail-list">
            <dt>本文</dt><dd class="post-body">{{ $comment->body }}</dd>
            <dt>投稿者</dt><dd>{{ $comment->user->name }}（{{ $comment->user->email }}）</dd>
            <dt>コメント先</dt>
            <dd>
                @if($postType && $post)
                    <span class="tag {{ $postType->tagClass() }}">{{ $postType->label() }}</span>
                    <a style="color:var(--blue);font-weight:700" href="{{ route('admin.posts.show', ['type' => $postType->value, 'id' => $post->id]) }}">{{ $post->title ?? '（タイトルなし）' }}</a>
                @else
                    <span class="text-muted">—</span>
                @endif
            </dd>
            @if($comment->parent)
                <dt>返信先</dt>
                <dd><a style="color:var(--blue)" href="{{ route('admin.comments.show', $comment->parent) }}">{{ $comment->parent->user->name }}：{{ \Illuminate\Support\Str::limit($comment->parent->body, 60) }}</a></dd>
            @endif
            <dt>いいね</dt><dd>{{ number_format($comment->comment_likes_count) }}</dd>
            <dt>投稿日時</dt><dd>{{ $comment->created_at->format('Y/m/d H:i') }}</dd>
            <dt>更新日時</dt><dd>{{ $comment->updated_at->format('Y/m/d H:i') }}</dd>
        </dl>
    </div>

    <div class="form-card" style="margin-top:18px">
        <h2 style="font-size:17px;margin-top:0">返信（{{ $comment->replies->count() }}件）</h2>
        @forelse($comment->replies as $reply)
            <div class="reply-item">
                <div class="post-meta">{{ $reply->user->name }} ・ {{ $reply->created_at->format('Y/m/d H:i') }}</div>
                <a href="{{ route('admin.comments.show', $reply) }}">{{ $reply->body }}</a>
            </div>
        @empty
            <p class="text-muted" style="margin:0">返信はありません。</p>
        @endforelse
    </div>

    <form method="POST" action="{{ route('admin.comments.destroy', $comment) }}" style="margin-top:18px;text-align:right"
          onsubmit="return confirm('このコメントを削除しますか？\n返信{{ $descendantCount }}件（返信への返信を含む）と、それらへのいいねも一緒に削除されます。')">
        @csrf
        @method('DELETE')
        <button class="btn btn-danger" type="submit">このコメントを削除</button>
    </form>
</div>
@endsection
