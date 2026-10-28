@extends('layouts.app')
@section('title', '目標を宣言する')
@section('content')
<div class="page-shell" style="grid-template-columns:220px minmax(0,760px)">
    <aside class="sidebar"><a class="nav-link" href="{{ url('/') }}"><span class="nav-icon">⌂</span><span>ホーム</span></a><a class="nav-link active" href="{{ url('/goals/create') }}"><span class="nav-icon">◎</span><span>目標宣言</span></a><a class="nav-link" href="{{ url('/search') }}"><span class="nav-icon">⌕</span><span>投稿を探す</span></a></aside>
    <main>
        <div class="page-heading"><div><h1>新しい目標を宣言しよう</h1><p class="subheading">目標を言葉にすることが、最初の一歩。</p></div></div>
        <form class="form-card" method="POST" action="{{ \Illuminate\Support\Facades\Route::has('goals.store') ? route('goals.store') : url('/goals') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group"><label for="title">目標タイトル <span style="color:#d34b5a">*</span></label><input class="form-control" id="title" name="title" value="{{ old('title') }}" placeholder="例：3か月で基本情報技術者試験に合格する" required>@error('title')<div class="form-error">{{ $message }}</div>@enderror</div>
            <div class="form-group"><label for="body">目標の説明 <span style="color:#d34b5a">*</span></label><textarea class="form-control" id="body" name="body" rows="6" placeholder="なぜこの目標に挑戦するのか、どんな方法で取り組むのかを書いてみよう。" required>{{ old('body') }}</textarea>@error('body')<div class="form-error">{{ $message }}</div>@enderror</div>
            <div class="form-group"><label for="genre_id">ジャンル</label><select class="form-control" id="genre_id" name="genre_id"><option value="">ジャンルを選択</option>@foreach(($genres ?? []) as $genre)<option value="{{ $genre->id }}" @selected(old('genre_id') == $genre->id)>{{ $genre->name }}</option>@endforeach</select><div class="form-help">ジャンルは管理者が登録したものから選択します。</div>@error('genre_id')<div class="form-error">{{ $message }}</div>@enderror</div>
            <div class="form-group"><label for="image">画像（任意）</label><input class="form-control" id="image" type="file" name="image" accept="image/*"><div class="form-help">目標に関連する画像を1枚添付できます。</div>@error('image')<div class="form-error">{{ $message }}</div>@enderror</div>
            <div class="form-actions"><a class="btn btn-outline" href="{{ url('/') }}">キャンセル</a><button class="btn btn-primary" type="submit">目標を宣言する →</button></div>
        </form>
    </main>
</div>
@endsection
