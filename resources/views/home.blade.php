@extends('layouts.app')
@section('title', 'ホーム')
@section('content')
<div class="page-shell">
    <aside class="sidebar">
        <div class="side-label">MENU</div>
        <a class="nav-link active" href="{{ \Illuminate\Support\Facades\Route::has('home') ? route('home') : url('/') }}"><span class="nav-icon">⌂</span><span>ホーム</span></a>
        <a class="nav-link" href="{{ \Illuminate\Support\Facades\Route::has('posts.create') ? route('posts.create') : url('/posts/create') }}"><span class="nav-icon">＋</span><span>投稿を作成</span></a>
        <a class="nav-link" href="{{ \Illuminate\Support\Facades\Route::has('posts.search') ? route('posts.search') : url('/search') }}"><span class="nav-icon">⌕</span><span>投稿を探す</span></a>
        <div class="side-label">POST CATEGORY</div>
        <a class="nav-link" href="{{ \Illuminate\Support\Facades\Route::has('goals.create') ? route('goals.create') : url('/goals/create') }}"><span class="nav-icon">◎</span><span>目標宣言</span></a>
        <a class="nav-link" href="{{ \Illuminate\Support\Facades\Route::has('knowledge-posts.create') ? route('knowledge-posts.create') : url('/knowledge-posts/create') }}"><span class="nav-icon">✦</span><span>学び・知識共有</span></a>
        <div class="side-label">MY PAGE</div>
        @auth
        <a class="nav-link" href="{{ \Illuminate\Support\Facades\Route::has('users.show') ? route('users.show', auth()->id()) : url('/users/'.auth()->id()) }}"><span class="nav-icon">♙</span><span>マイプロフィール</span></a>
        @endauth
        <a class="nav-link" href="{{ \Illuminate\Support\Facades\Route::has('profile.edit') ? route('profile.edit') : url('/profile/edit') }}"><span class="nav-icon">⚙</span><span>プロフィール設定</span></a>
    </aside>

    <main class="main-column">
        <div class="page-heading">
            <div><h1>みんなの一歩を見てみよう</h1><p class="subheading">誰かの挑戦が、あなたの次の一歩になる。</p></div>
            <a class="btn btn-primary" href="{{ \Illuminate\Support\Facades\Route::has('posts.create') ? route('posts.create') : url('/posts/create') }}">＋ 投稿する</a>
        </div>
        <div class="composer">
            <div class="avatar">{{ mb_substr(auth()->user()->name ?? 'G', 0, 1) }}</div>
            <div style="flex:1">
                <a class="composer-prompt" style="display:block" href="{{ \Illuminate\Support\Facades\Route::has('posts.create') ? route('posts.create') : url('/posts/create') }}">今、取り組んでいることは？</a>
                <div class="composer-actions">
                    <a class="btn btn-light" href="{{ \Illuminate\Support\Facades\Route::has('goals.create') ? route('goals.create') : url('/goals/create') }}">◎ 目標を宣言</a>
                    <a class="btn btn-outline" href="{{ \Illuminate\Support\Facades\Route::has('knowledge-posts.create') ? route('knowledge-posts.create') : url('/knowledge-posts/create') }}">✦ 学びを共有</a>
                </div>
            </div>
        </div>
        <div class="tabs">
            <a class="tab active" href="{{ url('/') }}">おすすめ</a>
            <a class="tab" href="{{ url('/?sort=latest') }}">新着</a>
        </div>

        @php
            $demoPosts = [
                ['type'=>'goal','label'=>'目標宣言','name'=>'Mika S.','initial'=>'M','time'=>'20分前','title'=>'毎日30分、英語学習を続ける！','body'=>'3か月後に海外の人と自然に会話できるようになるのが目標です。まずは毎日、単語とリスニングを少しずつ積み重ねます。','genre'=>'語学・学習','likes'=>24,'comments'=>6],
                ['type'=>'progress','label'=>'進捗報告','name'=>'Kenta','initial'=>'K','time'=>'1時間前','title'=>'朝活を1週間続けられた','body'=>'最初は起きるのが大変だったけど、寝る前に明日のタスクを決めておくとスムーズでした。小さな工夫が大事だと実感。','genre'=>'習慣化','likes'=>18,'comments'=>3],
                ['type'=>'knowledge','label'=>'学び共有','name'=>'Yui','initial'=>'Y','time'=>'3時間前','title'=>'勉強した内容を人に説明すると理解が深まる','body'=>'インプットだけで終わらせず、短くても自分の言葉でまとめると、理解できていない部分に気づけます。','genre'=>'勉強法','likes'=>37,'comments'=>9],
            ];
            $feedItems = isset($posts) && count($posts) ? $posts : $demoPosts;
        @endphp

        @forelse($feedItems as $post)
            @php
                $type = data_get($post, 'type', data_get($post, 'post_type', 'knowledge'));
                $label = data_get($post, 'label') ?? match($type) {'goal'=>'目標宣言','progress'=>'進捗報告','achievement'=>'達成報告',default=>'学び共有'};
                $tagClass = match($type) {'goal'=>'goal','progress'=>'progress','achievement'=>'achievement',default=>'knowledge'};
                $title = data_get($post, 'title', '投稿タイトル');
                $body = data_get($post, 'body', data_get($post, 'content', '投稿本文がここに表示されます。'));
                $author = data_get($post, 'user.name', data_get($post, 'name', 'ユーザー'));
                $initial = mb_substr($author, 0, 1);
                $id = data_get($post, 'id');
            @endphp
            <article class="post-card">
                <div class="post-head">
                    <div class="avatar">{{ $initial }}</div>
                    <div><div class="post-author">{{ $author }}</div><div class="post-meta">{{ data_get($post, 'created_at') ? \Illuminate\Support\Carbon::parse(data_get($post, 'created_at'))->diffForHumans() : data_get($post, 'time', '最近') }}</div></div>
                    <span class="tag {{ $tagClass }}" style="margin-left:8px">{{ $label }}</span>
                    <span class="post-menu">•••</span>
                </div>
                <a href="{{ \Illuminate\Support\Facades\Route::has('posts.show') && $id ? route('posts.show', ['type'=>$type,'id'=>$id]) : '#' }}">
                    <h2 class="post-title">{{ $title }}</h2>
                    <p class="post-body">{{ \Illuminate\Support\Str::limit($body, 240) }}</p>
                </a>
                @if(data_get($post, 'image_path'))
                    <img class="post-image" src="{{ asset('storage/'.data_get($post, 'image_path')) }}" alt="投稿画像">
                @elseif(data_get($post, 'demo_image'))
                    <div class="post-image">投稿に関連するイメージ</div>
                @endif
                <div style="margin-top:12px"><span class="tag">{{ data_get($post, 'genre.name', data_get($post, 'genre', '学び・成長')) }}</span></div>
                <div class="post-footer">
                    <button class="post-action" type="button">♡ {{ data_get($post, 'likes_count', data_get($post, 'likes', 0)) }} いいね</button>
                    <a class="post-action" href="{{ \Illuminate\Support\Facades\Route::has('posts.show') && $id ? route('posts.show', ['type'=>$type,'id'=>$id]) : '#' }}">♧ {{ data_get($post, 'comments_count', data_get($post, 'comments', 0)) }} コメント</a>
                    <button class="post-action" type="button">↗ シェア</button>
                </div>
            </article>
        @empty
            <div class="empty-state"><h3>まだ投稿がありません</h3><p>最初の一歩を投稿して、コミュニティを始めましょう。</p><a class="btn btn-primary" href="{{ url('/posts/create') }}">投稿を作成する</a></div>
        @endforelse
        @if(isset($posts) && method_exists($posts, 'links'))
            <div style="margin-top:20px">{{ $posts->links() }}</div>
        @endif
    </main>

    <aside class="rightbar">
        <section class="rightbar-section">
            <h3>🔥 注目のジャンル</h3>
            <div class="trend"><span class="trend-num">01</span><div><strong>勉強・資格</strong><div class="post-meta">今日の投稿 128件</div></div></div>
            <div class="trend"><span class="trend-num">02</span><div><strong>習慣化</strong><div class="post-meta">今日の投稿 86件</div></div></div>
            <div class="trend"><span class="trend-num">03</span><div><strong>キャリア</strong><div class="post-meta">今日の投稿 64件</div></div></div>
            <a class="btn btn-outline btn-block" href="{{ url('/search') }}">ジャンルを探す</a>
        </section>
        <section class="rightbar-section">
            <h3>🌱 今日のヒント</h3>
            <p style="color:var(--muted);margin:0">大きな目標も、小さな一歩の積み重ね。今日できたことを記録してみよう。</p>
        </section>
        <section class="rightbar-section">
            <h3>✨ はじめての投稿</h3>
            <p style="color:var(--muted)">目標や学びをシェアして、みんなと成長しよう。</p>
            <a class="btn btn-primary btn-block" href="{{ url('/posts/create') }}">投稿を作成</a>
        </section>
    </aside>
</div>
@endsection
