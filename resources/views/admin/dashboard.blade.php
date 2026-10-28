@extends('layouts.app')
@section('title', '管理者ダッシュボード')
@section('content')
<div class="admin-shell">
    <div class="page-heading"><div><h1>管理者ダッシュボード</h1><p class="subheading">Growlyの利用状況を確認できます。</p></div></div>
    <div class="stats-grid">
        <div class="stat-card"><div class="stat-label">ユーザー数</div><div class="stat-value">{{ $userCount ?? 0 }}</div></div>
        <div class="stat-card"><div class="stat-label">投稿数</div><div class="stat-value">{{ $postCount ?? 0 }}</div></div>
        <div class="stat-card"><div class="stat-label">コメント数</div><div class="stat-value">{{ $commentCount ?? 0 }}</div></div>
        <div class="stat-card"><div class="stat-label">ジャンル数</div><div class="stat-value">{{ $genreCount ?? 0 }}</div></div>
    </div>
    <div class="form-card" style="margin-top:22px">
        <h2 style="font-size:17px;margin-top:0">管理メニュー</h2>
        <div class="choice-grid">
            <a class="choice-card" href="{{ url('/admin/users') }}"><strong>ユーザー管理 →</strong><span>ユーザー一覧の確認、検索、削除</span></a>
            <a class="choice-card" href="{{ url('/admin/posts') }}"><strong>投稿管理 →</strong><span>投稿の確認、検索、不適切な投稿の削除</span></a>
            <a class="choice-card" href="{{ url('/admin/comments') }}"><strong>コメント管理 →</strong><span>コメントの確認と管理</span></a>
            <a class="choice-card" href="{{ url('/admin/genres') }}"><strong>ジャンル管理 →</strong><span>ジャンルの追加、編集、削除</span></a>
        </div>
    </div>
</div>
@endsection
