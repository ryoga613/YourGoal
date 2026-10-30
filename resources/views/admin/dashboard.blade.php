@extends('layouts.app')
@section('title', '管理者ダッシュボード')
@section('content')
<div class="admin-shell">
    <div class="page-heading"><div><h1>管理者ダッシュボード</h1><p class="subheading">Growlyの利用状況を確認できます。</p></div></div>
    <div class="stats-grid">
        <div class="stat-card"><div class="stat-label">ユーザー数</div><div class="stat-value">{{ number_format($userCount) }}</div></div>
        <div class="stat-card">
            <div class="stat-label">投稿数</div><div class="stat-value">{{ number_format($postCount) }}</div>
            <div class="form-help">
                @foreach($postCounts as $label => $count)
                    {{ $label }} {{ number_format($count) }}@if(! $loop->last) / @endif
                @endforeach
            </div>
        </div>
        <div class="stat-card"><div class="stat-label">今日のコメント数</div><div class="stat-value">{{ number_format($todayCommentCount) }}</div></div>
    </div>
    <div class="form-card" style="margin-top:22px">
        <h2 style="font-size:17px;margin-top:0">管理メニュー</h2>
        <div class="choice-grid">
            <a class="choice-card" href="{{ route('admin.users.index') }}"><strong>ユーザー管理 →</strong><span>ユーザー一覧の確認、検索、削除</span></a>
            <a class="choice-card" href="{{ route('admin.posts.index') }}"><strong>投稿管理 →</strong><span>投稿の確認、検索、不適切な投稿の削除</span></a>
            <a class="choice-card" href="{{ route('admin.comments.index') }}"><strong>コメント管理 →</strong><span>コメントの確認と管理</span></a>
        </div>
    </div>
</div>
@endsection
