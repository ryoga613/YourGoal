@extends('layouts.app')
@section('title', 'コメント管理')
@section('content')
<div class="admin-shell">
    <div class="page-heading">
        <div><h1>コメント管理</h1><p class="subheading">コメントと返信を確認し、不適切なものを削除できます。</p></div>
        <a class="btn btn-outline" href="{{ route('admin.dashboard') }}">← ダッシュボード</a>
    </div>

    @if(session('status'))<div class="flash">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="alert">{{ $errors->first() }}</div>@endif

    <form class="filter-bar" method="GET" action="{{ route('admin.comments.index') }}">
        <input class="form-control" type="search" name="keyword" value="{{ $keyword }}" placeholder="本文・投稿者名で検索">
        <select class="form-control" name="type">
            <option value="">すべての投稿種類</option>
            @foreach(\App\Enums\PostType::cases() as $postType)
                <option value="{{ $postType->value }}" @selected($type === $postType)>{{ $postType->label() }}</option>
            @endforeach
        </select>
        <button class="btn btn-primary" type="submit">検索</button>
        @if($keyword || $type)<a class="btn btn-outline" href="{{ route('admin.comments.index') }}">クリア</a>@endif
    </form>

    <p class="text-muted">{{ number_format($comments->total()) }}件</p>

    @if($comments->isEmpty())
        <div class="empty-state">該当するコメントはありません。</div>
    @else
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr><th>ID</th><th>本文</th><th>投稿者</th><th>コメント先</th><th>返信 / いいね</th><th>投稿日時</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach($comments as $comment)
                        @php($postType = $comment->postType())
                        <tr>
                            <td class="nowrap">{{ $comment->id }}</td>
                            <td>
                                @if($comment->parent)<div class="text-muted" style="font-size:12px">↳ {{ $comment->parent->user->name }} さんへの返信</div>@endif
                                <a class="truncate" href="{{ route('admin.comments.show', $comment) }}">{{ $comment->body }}</a>
                            </td>
                            <td class="nowrap">{{ $comment->user->name }}</td>
                            <td>
                                @if($postType)
                                    <span class="tag {{ $postType->tagClass() }}">{{ $postType->label() }}</span>
                                    <div class="truncate">{{ $comment->post()?->title ?? '（タイトルなし）' }}</div>
                                @endif
                            </td>
                            <td class="nowrap">{{ $comment->replies_count }} / {{ $comment->comment_likes_count }}</td>
                            <td class="nowrap">{{ $comment->created_at->format('Y/m/d H:i') }}</td>
                            <td class="nowrap"><a class="btn btn-light btn-sm" href="{{ route('admin.comments.show', $comment) }}">詳細</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $comments->links('pagination::bootstrap-4') }}
    @endif
</div>
@endsection
