@extends('layouts.app')
@section('title', '目標を宣言する')
@section('content')
<div class="page-shell" style="grid-template-columns:220px minmax(0,760px)">
    <aside class="sidebar">
        <a class="nav-link" href="{{ route('home') }}"><span class="nav-icon">⌂</span><span>ホーム</span></a>
        <a class="nav-link active" href="{{ route('goals.create') }}"><span class="nav-icon">◎</span><span>目標宣言</span></a>
        <a class="nav-link" href="{{ route('posts.search') }}"><span class="nav-icon">⌕</span><span>投稿を探す</span></a>
    </aside>

    <main>
        <div class="page-heading">
            <div>
                <h1>新しい目標を宣言しよう</h1>
                <p class="subheading">目標を言葉にすることが、最初の一歩。</p>
            </div>
        </div>

        <form class="form-card" method="POST" action="{{ route('goals.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label for="title">目標タイトル <span style="color:#d34b5a">*</span></label>
                <input class="form-control" id="title" name="title" value="{{ old('title') }}" placeholder="例：3か月で基本情報技術者試験に合格する" required>
                @error('title')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="body">目標の説明 （任意）<span style="color:#d34b5a"></span></label>
                <textarea class="form-control" id="body" name="body" rows="6" placeholder="なぜこの目標に挑戦するのか、どんな方法で取り組むのかを書いてみよう。">{{ old('body') }}</textarea>
                @error('body')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="image">画像（任意）</label>
                <input class="form-control" id="image" type="file" name="image" accept="image/*">
                <div class="form-help">目標に関連する画像を1枚添付できます。</div>
                @error('image')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-actions">
                <a class="btn btn-outline" href="{{ route('home') }}">キャンセル</a>
                <button class="btn btn-primary" type="submit">目標を宣言する →</button>
            </div>
        </form>
    </main>
</div>
@endsection
