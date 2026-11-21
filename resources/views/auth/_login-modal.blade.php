{{--
    ゲスト向けのログインモーダル（layouts/app で読み込む）
    data-requires-login を付けたリンクやフォームをゲストが使おうとすると、画面遷移の代わりにこのモーダルを開く
    ログインに成功すると、モーダルを開いたページに戻る（App\Http\Responses\LoginResponse）
--}}
<dialog class="login-modal" id="login-modal" aria-labelledby="login-modal-title">
    <div class="auth-card">
        <button class="login-modal-close" type="button" data-close-login-modal aria-label="閉じる">×</button>
        <h1 id="login-modal-title">ログインが必要です</h1>
        <p class="subheading">この機能を使うには、ログインしてください。</p>
        {{-- login_modal を送り返してもらい、ログインに失敗して戻ってきたときにモーダルを開き直す --}}
        @if(old('login_modal') && $errors->any())<div class="alert">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('login.store') }}">
            @csrf
            <input type="hidden" name="login_modal" value="1">
            <input type="hidden" name="redirect_to" value="{{ request()->getRequestUri() }}">
            <div class="form-group"><label for="login-modal-email">メールアドレス</label><input class="form-control" id="login-modal-email" type="email" name="email" value="{{ old('login_modal') ? old('email') : '' }}" autocomplete="email" required></div>
            <div class="form-group"><label for="login-modal-password">パスワード</label><input class="form-control" id="login-modal-password" type="password" name="password" autocomplete="current-password" required></div>
            <button class="btn btn-primary btn-block" type="submit">ログイン</button>
        </form>
        <div class="auth-foot">アカウントをお持ちでない方は <a style="color:var(--blue);font-weight:800" href="{{ route('register') }}">新規登録</a></div>
    </div>
</dialog>
