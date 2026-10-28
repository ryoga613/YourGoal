@extends('layouts.app')
@section('title', 'ログイン')
@section('content')
<div class="auth-wrap"><div class="auth-card">
    <div class="brand" style="margin-bottom:22px"><span class="brand-mark">G</span>growly</div>
    <h1>おかえりなさい</h1><p class="subheading">ログインして、みんなの成長を見てみよう。</p>
    @if($errors->any())<div class="alert">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ \Illuminate\Support\Facades\Route::has('login.store') ? route('login.store') : url('/login') }}">
        @csrf
        <div class="form-group"><label for="email">メールアドレス</label><input class="form-control" id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus></div>
        <div class="form-group"><label for="password">パスワード</label><input class="form-control" id="password" type="password" name="password" autocomplete="current-password" required></div>
        <button class="btn btn-primary btn-block" type="submit">ログイン</button>
    </form>
    <div class="auth-foot">アカウントをお持ちでない方は <a style="color:var(--blue);font-weight:800" href="{{ \Illuminate\Support\Facades\Route::has('register') ? route('register') : url('/register') }}">新規登録</a></div>
</div></div>
@endsection
