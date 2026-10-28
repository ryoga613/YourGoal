@extends('layouts.app')
@section('title', 'プロフィール編集')
@section('content')
<div class="page-shell" style="grid-template-columns:220px minmax(0,760px)">
    <aside class="sidebar"><a class="nav-link" href="{{ url('/') }}"><span class="nav-icon">⌂</span><span>ホーム</span></a><a class="nav-link active" href="{{ url('/profile/edit') }}"><span class="nav-icon">⚙</span><span>プロフィール編集</span></a></aside>
    <main>
        <div class="page-heading"><div><h1>プロフィールを編集</h1><p class="subheading">あなたのことをコミュニティに紹介しましょう。</p></div></div>
        <form class="form-card" method="POST" action="{{ \Illuminate\Support\Facades\Route::has('profile.update') ? route('profile.update') : url('/profile') }}" enctype="multipart/form-data">
            @csrf @method('PATCH')
            <div class="form-group"><label for="name">表示名</label><input class="form-control" id="name" name="name" value="{{ old('name', auth()->user()->name ?? '') }}" required>@error('name')<div class="form-error">{{ $message }}</div>@enderror</div>
            <div class="form-group"><label for="bio">自己紹介</label><textarea class="form-control" id="bio" name="bio" rows="5" maxlength="500" placeholder="興味のあること、取り組んでいることなど">{{ old('bio', auth()->user()->bio ?? '') }}</textarea>@error('bio')<div class="form-error">{{ $message }}</div>@enderror</div>
            <div class="form-group"><label for="avatar">プロフィール画像</label><input class="form-control" id="avatar" type="file" name="avatar" accept="image/*"></div>
            <div class="form-actions"><a class="btn btn-outline" href="{{ url('/') }}">キャンセル</a><button class="btn btn-primary" type="submit">変更を保存</button></div>
        </form>
    </main>
</div>
@endsection
