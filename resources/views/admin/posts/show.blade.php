@extends('layouts.app')
@section('title', '投稿詳細')
@section('content')
@php
    // 削除時に一緒に消えるデータの説明（目標宣言は紐づく進捗報告・達成報告も消える）
    $deleteTargets = $type === \App\Enums\PostType::Goal
        ? "進捗報告{$post->progress_reports_count}件・達成報告{$post->achievement_reports_count}件と、"
        : '';
@endphp
<div class="admin-shell">
    <div class="page-heading">
        <div><h1>投稿詳細</h1><p class="subheading">{{ $type->label() }} / ID: {{ $post->id }}</p></div>
        <a class="btn btn-outline" href="{{ route('admin.posts.index') }}">← 投稿一覧</a>
    </div>

    <div class="form-card">
        <dl class="detail-list">
            <dt>種類</dt><dd><span class="tag {{ $type->tagClass() }}">{{ $type->label() }}</span></dd>
            <dt>タイトル</dt><dd>{{ $post->title ?? '（タイトルなし）' }}</dd>
            <dt>本文</dt><dd class="post-body">{{ $post->body ?? '—' }}</dd>
            @if($post->image_path)
                <dt>画像</dt>
                {{-- ダミーデータは外部URL、アップロード画像は storage 配下のパスが入る --}}
                <dd><img class="post-image" style="max-width:360px" src="{{ \Illuminate\Support\Str::startsWith($post->image_path, ['http://', 'https://']) ? $post->image_path : asset('storage/'.$post->image_path) }}" alt="投稿画像"></dd>
            @endif
            <dt>投稿者</dt><dd>{{ $post->user->name }}（{{ $post->user->email }}）</dd>
            @if($type === \App\Enums\PostType::Goal)
                <dt>ステータス</dt><dd>{{ $post->status->label() }}</dd>
                <dt>関連する報告</dt><dd>進捗報告 {{ number_format($post->progress_reports_count) }}件 / 達成報告 {{ number_format($post->achievement_reports_count) }}件</dd>
            @elseif($post->goal_post)
                <dt>対象の目標</dt>
                <dd><a style="color:var(--blue);font-weight:700" href="{{ route('admin.posts.show', ['type' => 'goal', 'id' => $post->goal_post->id]) }}">{{ $post->goal_post->title }}</a></dd>
            @endif
            <dt>いいね</dt><dd>{{ number_format($post->post_likes_count) }}</dd>
            <dt>投稿日時</dt><dd>{{ $post->created_at->format('Y/m/d H:i') }}</dd>
            <dt>更新日時</dt><dd>{{ $post->updated_at->format('Y/m/d H:i') }}</dd>
        </dl>
    </div>

    <div class="form-card" style="margin-top:18px">
        <h2 style="font-size:17px;margin-top:0">コメント（{{ $post->comments_count }}件）</h2>
        @forelse($post->comments as $comment)
            <div class="reply-item">
                <div class="post-meta">{{ $comment->user->name }} ・ {{ $comment->created_at->format('Y/m/d H:i') }}@if($comment->parent_id) ・ 返信@endif</div>
                <a href="{{ route('admin.comments.show', $comment) }}">{{ $comment->body }}</a>
            </div>
        @empty
            <p class="text-muted" style="margin:0">コメントはありません。</p>
        @endforelse
    </div>

    <form method="POST" action="{{ route('admin.posts.destroy', ['type' => $type->value, 'id' => $post->id]) }}" style="margin-top:18px;text-align:right"
          onsubmit="return confirm('この投稿を削除しますか？\n{{ $deleteTargets }}コメント・いいねも一緒に削除されます。')">
        @csrf
        @method('DELETE')
        <button class="btn btn-danger" type="submit">この投稿を削除</button>
    </form>
</div>
@endsection
