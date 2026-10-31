@extends('layouts.app')
@section('title', 'ユーザー管理')
@section('content')
<div class="admin-shell">
    <div class="page-heading">
        <div><h1>ユーザー管理</h1><p class="subheading">ユーザーを確認し、不適切なユーザーを削除できます。</p></div>
        <a class="btn btn-outline" href="{{ route('admin.dashboard') }}">← ダッシュボード</a>
    </div>

    @if(session('status'))<div class="flash">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="alert">{{ $errors->first() }}</div>@endif

    <form class="filter-bar" method="GET" action="{{ route('admin.users.index') }}">
        <input class="form-control" type="search" name="keyword" value="{{ $keyword }}" placeholder="名前・メールアドレスで検索">
        <button class="btn btn-primary" type="submit">検索</button>
        @if($keyword)<a class="btn btn-outline" href="{{ route('admin.users.index') }}">クリア</a>@endif
    </form>

    <p class="text-muted">{{ number_format($users->total()) }}件</p>

    @if($users->isEmpty())
        <div class="empty-state">該当するユーザーはいません。</div>
    @else
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr><th>ID</th><th>名前</th><th>メールアドレス</th><th>投稿 / コメント</th><th>登録日時</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        {{-- 投稿は4種類のテーブルに分かれているため、種類ごとの件数を合計する --}}
                        @php($postCount = $user->goal_posts_count + $user->progress_reports_count + $user->achievement_reports_count + $user->knowledge_posts_count)
                        <tr>
                            <td class="nowrap">{{ $user->id }}</td>
                            <td class="nowrap"><a style="font-weight:700" href="{{ route('admin.users.show', $user) }}">{{ $user->name }}</a></td>
                            <td>{{ $user->email }}</td>
                            <td class="nowrap">{{ $user->postCount() }} / {{ $user->comments_count }}</td>
                            <td class="nowrap">{{ $user->created_at->format('Y/m/d H:i') }}</td>
                            <td class="nowrap"><a class="btn btn-light btn-sm" href="{{ route('admin.users.show', $user) }}">詳細</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $users->links('pagination::bootstrap-4') }}
    @endif
</div>
@endsection
