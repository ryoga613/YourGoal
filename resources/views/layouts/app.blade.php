<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Growly') | Growly</title>
    <style>
        :root{--blue:#3563e9;--blue-dark:#244bc0;--ink:#20243a;--muted:#737b91;--line:#e9ecf3;--bg:#f6f8fc;--white:#fff;--green:#16856b;--orange:#c97822;--pink:#b24b87}
        *{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--ink);font-family:-apple-system,BlinkMacSystemFont,"Segoe UI","Noto Sans JP",sans-serif;font-size:14px;line-height:1.65}
        a{color:inherit;text-decoration:none}button,input,textarea,select{font:inherit}button,.btn{cursor:pointer;border:0;border-radius:10px;padding:10px 15px;font-weight:700;display:inline-flex;align-items:center;justify-content:center;gap:7px}
        .btn-primary{background:var(--blue);color:#fff}.btn-primary:hover{background:var(--blue-dark)}.btn-light{background:#eef2ff;color:var(--blue)}.btn-outline{border:1px solid var(--line);background:#fff;color:var(--ink)}.btn-block{width:100%}
        .topbar{height:72px;background:#fff;border-bottom:1px solid var(--line);position:sticky;top:0;z-index:10;display:flex;align-items:center;padding:0 max(24px,calc((100% - 1320px)/2));gap:28px}
        .brand{font-size:24px;font-weight:850;letter-spacing:-1px;color:var(--blue);white-space:nowrap}.brand-mark{display:inline-grid;place-items:center;background:var(--blue);color:white;width:30px;height:30px;border-radius:10px;margin-right:7px;font-size:16px}
        .top-search{max-width:520px;flex:1;position:relative}.top-search input{width:100%;border:1px solid var(--line);border-radius:12px;padding:12px 15px 12px 42px;background:#f8f9fc;outline:none}.search-icon{position:absolute;left:15px;top:10px;color:var(--muted);font-size:18px}
        .top-actions{margin-left:auto;display:flex;align-items:center;gap:14px}.icon-link{font-size:20px;color:#59627a}.avatar{width:36px;height:36px;border-radius:50%;background:#e6ebff;color:var(--blue);display:inline-grid;place-items:center;font-weight:800;flex-shrink:0}
        .page-shell{max-width:1320px;margin:28px auto;padding:0 24px;display:grid;grid-template-columns:220px minmax(0,1fr) 260px;gap:24px;align-items:start}
        .sidebar,.rightbar,.panel,.post-card,.form-card,.auth-card{background:#fff;border:1px solid var(--line);border-radius:16px}.sidebar{padding:14px;position:sticky;top:94px}.side-label{font-size:11px;font-weight:800;color:#9aa1b2;letter-spacing:1px;padding:10px 10px 7px}.nav-link{display:flex;align-items:center;gap:12px;padding:11px 12px;border-radius:10px;color:#5e667c;font-weight:650;margin:2px 0}.nav-link.active,.nav-link:hover{background:#eef2ff;color:var(--blue)}.nav-icon{width:22px;text-align:center;font-size:17px}
        .main-column{min-width:0}.rightbar{padding:18px;position:sticky;top:94px}.rightbar h3{font-size:14px;margin:0 0 14px}.rightbar-section+.rightbar-section{border-top:1px solid var(--line);margin-top:20px;padding-top:18px}.trend{display:flex;gap:10px;align-items:center;margin:12px 0}.trend-num{font-size:12px;color:#9aa1b2;width:15px}.tag{display:inline-flex;align-items:center;border-radius:7px;padding:4px 8px;font-size:11px;font-weight:800;background:#eef2ff;color:var(--blue)}.tag.goal{background:#fff3df;color:#a96813}.tag.progress{background:#e7f8f1;color:#16856b}.tag.achievement{background:#fce9f4;color:#a63f7b}.tag.knowledge{background:#eeeaff;color:#6650c9}
        .page-heading{display:flex;justify-content:space-between;align-items:center;margin:0 0 18px;gap:12px}.page-heading h1{font-size:24px;line-height:1.3;margin:0;letter-spacing:-.5px}.subheading{color:var(--muted);margin:5px 0 0}.tabs{display:flex;gap:22px;border-bottom:1px solid var(--line);margin-bottom:16px}.tab{padding:13px 2px;color:var(--muted);font-weight:750;border-bottom:2px solid transparent}.tab.active{color:var(--blue);border-color:var(--blue)}
        .composer{background:#fff;border:1px solid var(--line);border-radius:16px;padding:16px;margin-bottom:16px;display:flex;gap:12px;align-items:center}.composer-prompt{flex:1;border:1px solid var(--line);border-radius:12px;padding:12px 14px;color:#9aa1b2;background:#fafbfe}.composer-actions{display:flex;gap:8px;margin-top:12px;flex-wrap:wrap}
        .post-card{padding:20px;margin-bottom:14px}.post-head{display:flex;align-items:center;gap:10px}.post-author{font-weight:800}.post-meta{font-size:12px;color:var(--muted)}.post-menu{margin-left:auto;color:#a0a6b5}.post-title{font-size:17px;margin:15px 0 7px;font-weight:800;line-height:1.5}.post-body{color:#555e73;white-space:pre-line}.post-image{width:100%;height:220px;object-fit:cover;background:linear-gradient(135deg,#dfe8ff,#f5e8ff);border-radius:12px;margin-top:14px;display:grid;place-items:center;color:#6979b2;font-weight:700}.post-footer{display:flex;gap:22px;border-top:1px solid var(--line);padding-top:13px;margin-top:16px;color:#737b91}.post-action{background:transparent;padding:4px 0;color:inherit;font-weight:650}.post-action:hover{color:var(--blue)}
        .form-card{padding:24px}.form-group{margin-bottom:18px}.form-group label{display:block;font-weight:750;margin-bottom:7px}.form-control{width:100%;border:1px solid #dfe3ec;border-radius:10px;padding:12px 13px;background:#fff;outline:none}.form-control:focus{border-color:var(--blue);box-shadow:0 0 0 3px #3563e91a}.form-help{font-size:12px;color:var(--muted);margin-top:5px}.form-error{color:#c0394c;font-size:12px;margin-top:4px}.form-actions{display:flex;gap:10px;justify-content:flex-end;margin-top:24px}.choice-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}.choice-card{padding:18px;border:1px solid var(--line);border-radius:13px;background:#fff;display:block}.choice-card:hover{border-color:var(--blue);background:#f8f9ff}.choice-card strong{display:block;font-size:15px;margin:7px 0}.choice-card span{color:var(--muted);font-size:12px}
        .profile-cover{height:130px;border-radius:16px 16px 0 0;background:linear-gradient(120deg,#dbe5ff,#e9e2ff,#fce7f2)}.profile-info{padding:0 22px 22px;background:#fff;border:1px solid var(--line);border-top:0;border-radius:0 0 16px 16px}.profile-avatar{width:78px;height:78px;border:4px solid #fff;margin-top:-38px;font-size:25px}.profile-name{font-size:22px;font-weight:850;margin:8px 0 0}.profile-stats{display:flex;gap:24px;margin-top:15px;color:var(--muted)}.profile-stats strong{color:var(--ink)}
        .auth-wrap{min-height:calc(100vh - 72px);display:grid;place-items:center;padding:35px 20px}.auth-card{width:min(100%,440px);padding:32px}.auth-card h1{font-size:25px;margin:0}.auth-card .subheading{margin-bottom:25px}.auth-foot{text-align:center;color:var(--muted);margin-top:20px}.alert{padding:12px 14px;background:#fff0f0;color:#b42332;border-radius:10px;margin-bottom:16px}.empty-state{text-align:center;padding:50px 20px;color:var(--muted);background:#fff;border:1px dashed #dce1eb;border-radius:16px}
        .admin-shell{max-width:1180px;margin:28px auto;padding:0 24px}.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}.stat-card{padding:20px;background:#fff;border:1px solid var(--line);border-radius:15px}.stat-label{color:var(--muted);font-weight:650}.stat-value{font-size:28px;font-weight:850;margin-top:8px}
        .footer{text-align:center;color:#a0a6b5;padding:28px;font-size:12px}
        @media(max-width:1100px){.page-shell{grid-template-columns:190px minmax(0,1fr)}.rightbar{display:none}.topbar{padding:0 20px}}
        @media(max-width:720px){.topbar{height:62px;gap:12px;padding:0 14px}.brand{font-size:20px}.top-search{display:none}.page-shell{display:block;margin:16px auto;padding:0 12px}.sidebar{position:static;margin-bottom:14px;display:flex;overflow:auto;padding:7px;gap:3px}.side-label{display:none}.nav-link{white-space:nowrap;padding:9px}.nav-link span:last-child{display:none}.main-column{width:100%}.post-card{padding:15px}.page-heading h1{font-size:21px}.form-card{padding:18px}.choice-grid,.stats-grid{grid-template-columns:1fr 1fr}.composer{align-items:flex-start}.composer .btn{padding:8px}.auth-card{padding:23px}}
    </style>
</head>
<body>
<header class="topbar">
    <a class="brand" href="{{ auth()->check() ? auth()->user()->role->homePath() : route('login') }}"><span class="brand-mark">G</span>growly</a>
    {{-- 検索は一般ユーザー向けの機能のため、管理者には表示しない --}}
    @unless(auth()->user()?->isAdmin())
    <form class="top-search" method="GET" action="{{ \Illuminate\Support\Facades\Route::has('posts.search') ? route('posts.search') : url('/search') }}">
        <span class="search-icon">⌕</span>
        <input type="search" name="keyword" value="{{ request('keyword') }}" placeholder="気になる投稿やジャンルを検索">
    </form>
    @endunless
    <div class="top-actions">
        <a class="icon-link" href="#" aria-label="通知">♧</a>
        @auth
            @if(auth()->user()->isAdmin())
                <a class="btn btn-light" href="{{ route('admin.dashboard') }}">管理画面</a>
            @else
                <a href="{{ \Illuminate\Support\Facades\Route::has('profile.edit') ? route('profile.edit') : url('/profile/edit') }}" class="avatar" aria-label="プロフィール">{{ mb_substr(auth()->user()->name ?? 'U', 0, 1) }}</a>
            @endif
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-outline" type="submit">ログアウト</button>
            </form>
        @else
            <a class="btn btn-light" href="{{ \Illuminate\Support\Facades\Route::has('login') ? route('login') : url('/login') }}">ログイン</a>
        @endauth
    </div>
</header>
@yield('content')
<footer class="footer">© {{ date('Y') }} Growly — 小さな一歩を、みんなの成長に。</footer>
<script>
    // ブラウザ標準の入力チェック（required / type="email" / minlength）のメッセージを日本語で統一する
    document.addEventListener('invalid', (event) => {
        const input = event.target;
        const label = document.querySelector(`label[for="${input.id}"]`)?.textContent.trim() || 'この項目';
        const { validity } = input;

        let message = '';
        if (validity.valueMissing) {
            message = `${label}を入力してください。`;
        } else if (validity.typeMismatch && input.type === 'email') {
            message = 'メールアドレスの形式で入力してください。（例：name@example.com）';
        } else if (validity.tooShort) {
            message = `${label}は${input.minLength}文字以上で入力してください。`;
        }

        input.setCustomValidity(message);
    }, true);

    // 入力し直したらメッセージをリセットし、改めてチェックさせる
    document.addEventListener('input', (event) => {
        if (event.target.setCustomValidity) {
            event.target.setCustomValidity('');
        }
    });
</script>
</body>
</html>
