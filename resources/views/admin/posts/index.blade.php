@extends('layouts.app')
@section('title', '投稿管理')
@section('content')
<div class="admin-shell">
    <div class="page-heading">
        <div><h1>投稿管理</h1><p class="subheading">4種類の投稿を確認し、不適切なものを削除できます。</p></div>
        <a class="btn btn-outline" href="{{ route('admin.dashboard') }}">← ダッシュボード</a>
    </div>

    @if(session('status'))<div class="flash">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="alert">{{ $errors->first() }}</div>@endif

    <form class="filter-bar" method="GET" action="{{ route('admin.posts.index') }}">
        <input class="form-control" type="search" name="keyword" value="{{ $keyword }}" placeholder="タイトル・本文・投稿者名で検索">
        <select class="form-control" name="type">
            <option value="">すべての投稿種類</option>
            @foreach(\App\Enums\PostType::cases() as $postType)
                <option value="{{ $postType->value }}" @selected($type === $postType)>{{ $postType->label() }}</option>
            @endforeach
        </select>
        <button class="btn btn-primary" type="submit">検索</button>
        @if($keyword || $type)<a class="btn btn-outline" href="{{ route('admin.posts.index') }}">クリア</a>@endif
    </form>

    <p class="text-muted">{{ number_format($posts->total()) }}件</p>

    @if($posts->isEmpty())
        <div class="empty-state">該当する投稿はありません。</div>
    @else
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr><th>ID</th><th>種類</th><th>タイトル / 本文</th><th>投稿者</th><th>コメント / いいね</th><th>投稿日時</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach($posts as ['type' => $postType, 'post' => $post])
                        @php($showUrl = route('admin.posts.show', ['type' => $postType->value, 'id' => $post->id]))
                        <tr>
                            <td class="nowrap">{{ $post->id }}</td>
                            <td class="nowrap"><span class="tag {{ $postType->tagClass() }}">{{ $postType->label() }}</span></td>
                            <td>
                                <a style="font-weight:700" href="{{ $showUrl }}">{{ $post->title ?? '（タイトルなし）' }}</a>
                                @if($post->body)<div class="truncate text-muted">{{ $post->body }}</div>@endif
                            </td>
                            <td class="nowrap">{{ $post->user->name }}</td>
                            <td class="nowrap">{{ $post->comments_count }} / {{ $post->post_likes_count }}</td>
                            <td class="nowrap">{{ $post->created_at->format('Y/m/d H:i') }}</td>
                            <td class="nowrap"><a class="btn btn-light btn-sm" href="{{ $showUrl }}">詳細</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $posts->links('pagination::bootstrap-4') }}
    @endif
</div>
@endsection
