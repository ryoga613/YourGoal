@extends('layouts.app')
@section('title', '新規登録')
@section('content')
<div class="auth-wrap"><div class="auth-card">
    <div class="brand" style="margin-bottom:22px"><span class="brand-mark">G</span>growly</div>
    <h1>一緒に成長を始めよう</h1><p class="subheading">アカウントを作成して、最初の一歩をシェアしよう。</p>
    @if($errors->any())<div class="alert">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ \Illuminate\Support\Facades\Route::has('register.store') ? route('register.store') : url('/register') }}">
        @csrf
        <div class="form-group"><label for="name">表示名</label><input class="form-control" id="name" name="name" value="{{ old('name') }}" autocomplete="name" required></div>
        <div class="form-group"><label for="email">メールアドレス</label><input class="form-control" id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required></div>
        <div class="form-group"><label for="password">パスワード</label><input class="form-control" id="password" type="password" name="password" autocomplete="new-password" minlength="8" required><div class="form-help">8文字以上で設定してください。</div></div>
        <div class="form-group"><label for="password_confirmation">パスワード（確認）</label><input class="form-control" id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" minlength="8" required></div>
        <button class="btn btn-primary btn-block" type="submit">アカウントを作成</button>
    </form>
    <div class="auth-foot">すでにアカウントをお持ちの方は <a style="color:var(--blue);font-weight:800" href="{{ \Illuminate\Support\Facades\Route::has('login') ? route('login') : url('/login') }}">ログイン</a></div>
</div></div>
@endsection
