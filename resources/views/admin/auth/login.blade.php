@extends('layouts.app')
@section('title', '管理者ログイン')
@section('content')
<div class="auth-wrap"><div class="auth-card">
    <div class="brand" style="margin-bottom:22px"><span class="brand-mark">Y</span>YourGoal</div>
    <h1>管理者ログイン</h1><p class="subheading">管理者アカウントでログインしてください。</p>
    @if($errors->any())<div class="alert">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('admin.login.store') }}">
        @csrf
        <div class="form-group"><label for="email">メールアドレス</label><input class="form-control" id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus></div>
        <div class="form-group"><label for="password">パスワード</label><input class="form-control" id="password" type="password" name="password" autocomplete="current-password" required></div>
        <button class="btn btn-primary btn-block" type="submit">ログイン</button>
    </form>
</div></div>
@endsection
