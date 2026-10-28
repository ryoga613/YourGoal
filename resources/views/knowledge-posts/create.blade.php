@extends('layouts.app')
@section('title', '学びを共有する')
@section('content')
<div class="page-shell" style="grid-template-columns:220px minmax(0,760px)">
    <aside class="sidebar"><a class="nav-link" href="{{ url('/') }}"><span class="nav-icon">⌂</span><span>ホーム</span></a><a class="nav-link active" href="{{ url('/knowledge-posts/create') }}"><span class="nav-icon">✦</span><span>学び・知識共有</span></a><a class="nav-link" href="{{ url('/search') }}"><span class="nav-icon">⌕</span><span>投稿を探す</span></a></aside>
    <main>
        <div class="page-heading"><div><h1>学びや知識をシェアしよう</h1><p class="subheading">あなたの気づきが、誰かの役に立つかもしれません。</p></div></div>
        <form class="form-card" method="POST" action="{{ \Illuminate\Support\Facades\Route::has('knowledge-posts.store') ? route('knowledge-posts.store') : url('/knowledge-posts') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group"><label for="title">タイトル <span style="color:#d34b5a">*</span></label><input class="form-control" id="title" name="title" value="{{ old('title') }}" placeholder="学んだことを一言で表すと？" required>@error('title')<div class="form-error">{{ $message }}</div>@enderror</div>
            <div class="form-group"><label for="body">本文 <span style="color:#d34b5a">*</span></label><textarea class="form-control" id="body" name="body" rows="9" placeholder="学んだ内容、参考になったこと、実践してみた感想などを書いてみましょう。" required>{{ old('body') }}</textarea>@error('body')<div class="form-error">{{ $message }}</div>@enderror</div>
            <div class="form-group"><label for="image">画像（任意）</label><input class="form-control" id="image" type="file" name="image" accept="image/*"><div class="form-help">画像は1枚まで添付できます。</div>@error('image')<div class="form-error">{{ $message }}</div>@enderror</div>
            <div class="form-actions"><a class="btn btn-outline" href="{{ url('/') }}">キャンセル</a><button class="btn btn-primary" type="submit">投稿する →</button></div>
        </form>
    </main>
</div>
@endsection
