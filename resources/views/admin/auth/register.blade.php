@extends('layouts.app')
@section('title', '管理者の新規登録')
@section('content')
<div class="auth-wrap"><div class="auth-card">
    <div class="brand" style="margin-bottom:22px"><span class="brand-mark">Y</span>YourGoal</div>
    <h1>管理者の新規登録</h1><p class="subheading">管理者アカウントを作成します。</p>
    @if($errors->any())<div class="alert">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('admin.register.store') }}">
        @csrf
        <div class="form-group"><label for="name">表示名</label><input class="form-control" id="name" name="name" value="{{ old('name') }}" autocomplete="name" required></div>
        <div class="form-group"><label for="email">メールアドレス</label><input class="form-control" id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required></div>
        <div class="form-group"><label for="password">パスワード</label><input class="form-control" id="password" type="password" name="password" autocomplete="new-password" minlength="8" required><div class="form-help">8文字以上で設定してください。</div></div>
        <div class="form-group"><label for="password_confirmation">パスワード（確認）</label><input class="form-control" id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" minlength="8" required></div>
        <button class="btn btn-primary btn-block" type="submit">管理者アカウントを作成</button>
    </form>
</div></div>
@endsection
