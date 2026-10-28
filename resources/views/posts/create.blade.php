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
            <a class="choice-card" href="{{ url('/goals') }}"><span class="tag progress">進捗報告</span><strong>取り組みを記録する</strong><span>目標に向けた進捗や、途中で気づいたことを共有。</span></a>
            <a class="choice-card" href="{{ url('/goals') }}"><span class="tag achievement">達成報告</span><strong>できたことを報告する</strong><span>努力の成果を振り返り、達成の喜びをシェア。</span></a>
            <a class="choice-card" href="{{ \Illuminate\Support\Facades\Route::has('knowledge-posts.create') ? route('knowledge-posts.create') : url('/knowledge-posts/create') }}"><span class="tag knowledge">学び共有</span><strong>学んだ知識を共有する</strong><span>役に立った知識や経験を、みんなに伝えよう。</span></a>
        </div>
    </main>
</div>
@endsection
