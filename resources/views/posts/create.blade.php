@extends('layouts.app')
@section('title', '投稿の種類を選ぶ')
@section('content')
<div class="page-shell" style="grid-template-columns:220px minmax(0,760px)">
    <aside class="sidebar">
        <a class="nav-link" href="{{ url('/') }}"><span class="nav-icon">⌂</span><span>ホーム</span></a>
        <a class="nav-link active" href="{{ url('/posts/create') }}"><span class="nav-icon">＋</span><span>投稿を作成</span></a>
        <a class="nav-link" href="{{ url('/search') }}"><span class="nav-icon">⌕</span><span>投稿を探す</span></a>
    </aside>
    <main>
        <div class="page-heading"><div><h1>何をシェアしますか？</h1><p class="subheading">今のあなたに合った投稿タイプを選びましょう。</p></div></div>
        <div class="choice-grid">
            <a class="choice-card" href="{{ \Illuminate\Support\Facades\Route::has('goals.create') ? route('goals.create') : url('/goals/create') }}"><span class="tag goal">目標宣言</span><strong>新しい目標に挑戦する</strong><span>これから達成したい目標を宣言して、挑戦をスタート。</span></a>
            <a class="choice-card" href="{{ \Illuminate\Support\Facades\Route::has('knowledge-posts.create') ? route('knowledge-posts.create') : url('/knowledge-posts/create') }}"><span class="tag knowledge">学び共有</span><strong>学んだ知識を共有する</strong><span>役に立った知識や経験を、みんなに伝えよう。</span></a>
        </div>

        {{-- 進捗報告・達成報告は、対象の目標を選んでから作成画面へ進む --}}
        <h2 style="font-size:18px;margin:28px 0 6px">目標の進捗・達成を報告する</h2>
        <p class="subheading" style="margin:0 0 14px">報告したい目標を選びましょう。</p>

        @forelse($goals as $goal)
            <article class="post-card">
                <div class="post-head">
                    <span class="tag goal">{{ $goal->status->label() }}</span>
                    <span class="post-meta">{{ $goal->created_at->format('Y年n月j日') }}に宣言</span>
                </div>
                <h3 class="post-title">{{ $goal->title }}</h3>
                <div class="composer-actions">
                    <a class="btn btn-light" href="{{ route('progress-reports.create', $goal) }}">＋ 進捗を報告</a>
                    <a class="btn btn-primary" href="{{ route('achievement-reports.create', $goal) }}">✓ 達成を報告</a>
                </div>
            </article>
        @empty
            <div class="empty-state">
                <p>報告できる目標がまだありません。まずは目標を宣言しましょう。</p>
                <a class="btn btn-primary" href="{{ route('goals.create') }}">目標を宣言する</a>
            </div>
        @endforelse
    </main>
</div>
@endsection
