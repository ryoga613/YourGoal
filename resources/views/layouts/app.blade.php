<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'YourGoal') | YourGoal</title>
    <script>
        // 夜（19時〜翌6時）は背景を黒・文字を白にする（見ている人の端末の時刻で判断する）
        // 画面を描く前に決めておき、一瞬だけ白い画面が見えるのを防ぐため <head> の中で実行する
        (() => {
            const applyTheme = () => {
                const hour = new Date().getHours();
                document.documentElement.dataset.theme = (hour >= 19 || hour < 6) ? 'night' : 'day';
            };
            applyTheme();
            setInterval(applyTheme, 60 * 1000); // 開いたまま19時・6時をまたいでも、1分以内に切り替える
        })();
    </script>
    {{-- 柔らかい明朝体（Zen Old Mincho）を Google Fonts から読み込む。用意されている太さは 400 / 500 / 600 / 700 / 900 --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Zen+Old+Mincho:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    <style>
        /* 基本カラーは黒、アクセントカラーはオレンジ。グレーは黒とオレンジになじむよう少し暖かみのある色にする
           色はすべてここの変数で指定し、夜（html[data-theme="night"]）は変数の値だけを入れ替える */
        :root {
            --primary: #1c1b1a;        /* 基本の黒（文字・主ボタン） */
            --primary-hover: #3a3836;
            --on-primary: #fff;        /* 主ボタンの上の文字 */
            --accent: #d9620f;         /* アクセントのオレンジ（リンク・選択中・フォーカス） */
            --accent-soft: #fff1e6;    /* オレンジの薄い背景 */
            --ink: var(--primary);
            --text-soft: #4f4a45;      /* 投稿の本文 */
            --text-sub: #5a554f;       /* メニューの文字 */
            --muted: #6f6a64;          /* 補足の文字 */
            --faint: #a39d96;          /* さらに薄い文字（見出しラベル・フッター） */
            --disabled: #c4bfb9;
            --line: #ebe8e4;
            --line-strong: #e0dcd7;    /* 入力欄の枠 */
            --bg: #f7f6f4;             /* ページの背景 */
            --surface: #fff;           /* カード・ヘッダー・入力欄の背景 */
            --surface-sunken: #faf9f7; /* 一段沈んだ背景（検索欄・表の見出し） */
            --chip: #f0ede9;           /* アバター・タグの背景 */
            --image-from: #f3eee8;     /* 画像がない投稿のイメージ枠 */
            --image-to: #fbe7d8;
            --image-text: #8a7b6c;
            --white: #fff;
            --green: #16856b;
            --orange: #c97822;
            --pink: #b24b87;
        }

        /* 夜（19時〜翌6時）：背景を黒、文字を白にする。どの時間帯かは <head> のスクリプトが決める */
        html[data-theme="night"] {
            color-scheme: dark;        /* スクロールバーや入力欄の部品もブラウザの暗い見た目にする */
            --primary: #f3f0eb;
            --primary-hover: #d9d4cd;
            --on-primary: #1c1b1a;
            --accent: #f08a3e;         /* 黒い背景でも読めるよう、昼より明るいオレンジ */
            --accent-soft: rgba(240, 138, 62, .16);
            --text-soft: #d6d1ca;
            --text-sub: #c4bfb9;
            --muted: #a39d96;
            --faint: #7d7770;
            --disabled: #5a554f;
            --line: #34312e;
            --line-strong: #45413d;
            --bg: #121110;
            --surface: #1c1b1a;
            --surface-sunken: #24221f;
            --chip: #2c2a27;
            --image-from: #2a2622;
            --image-to: #3a2a1e;
            --image-text: #b8a894;
        }


        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--bg);
            color: var(--ink);
            /* 柔らかい明朝体。Zen Old Mincho を読み込めないときは、端末に入っている明朝体（Mac: ヒラギノ明朝、Windows: 游明朝）を使う */
            font-family: "Zen Old Mincho","Hiragino Mincho ProN","Yu Mincho",YuMincho,serif;
            font-size: 14px;
            line-height: 1.65;
        }


        a {
            color: inherit;
            text-decoration: none;
        }

        button,
        input,
        textarea,
        select {
            font: inherit;
        }

        button,
        .btn {
            cursor: pointer;
            border: 0;
            border-radius: 10px;
            padding: 10px 15px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
        }


        .btn-primary {
            background: var(--primary);
            color: var(--on-primary);
        }

        .btn-primary:hover {
            background: var(--primary-hover);
        }

        .btn-light {
            background: var(--accent-soft);
            color: var(--accent);
        }

        .btn-outline {
            border: 1px solid var(--line);
            background: var(--surface);
            color: var(--ink);
        }

        .btn-block {
            width: 100%;
        }


        .topbar {
            height: 72px;
            background: var(--surface);
            border-bottom: 1px solid var(--line);
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            align-items: center;
            padding: 0 max(24px,calc((100% - 1320px)/2));
            gap: 28px;
        }


        .brand {
            font-size: 24px;
            font-weight: 850;
            letter-spacing: -1px;
            color: var(--primary);
            white-space: nowrap;
        }

        .brand-mark {
            display: inline-grid;
            place-items: center;
            background: var(--accent);
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 10px;
            margin-right: 7px;
            font-size: 16px;
        }


        .top-search {
            max-width: 520px;
            flex: 1;
            position: relative;
        }

        .top-search input {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 12px 15px 12px 42px;
            background: var(--surface-sunken);
            outline: none;
        }

        .search-icon {
            position: absolute;
            left: 15px;
            top: 10px;
            color: var(--muted);
            font-size: 18px;
        }


        .top-actions {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--chip);
            color: var(--primary);
            display: inline-grid;
            place-items: center;
            font-weight: 800;
            flex-shrink: 0;
            /* プロフィール画像（img.avatar）を丸の中に切り抜いて収める */
            object-fit: cover;
        }


        .page-shell {
            max-width: 1320px;
            margin: 28px auto;
            padding: 0 24px;
            display: grid;
            grid-template-columns: 220px minmax(0,1fr) 260px;
            gap: 24px;
            align-items: start;
        }


        .sidebar,
        .rightbar,
        .panel,
        .post-card,
        .form-card,
        .auth-card {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 16px;
        }

        .sidebar {
            padding: 14px;
            position: sticky;
            top: 94px;
        }

        .side-label {
            font-size: 11px;
            font-weight: 800;
            color: var(--faint);
            letter-spacing: 1px;
            padding: 10px 10px 7px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 12px;
            border-radius: 10px;
            color: var(--text-sub);
            font-weight: 650;
            margin: 2px 0;
        }

        .nav-link.active,
        .nav-link:hover {
            background: var(--accent-soft);
            color: var(--accent);
        }

        .nav-icon {
            width: 22px;
            text-align: center;
            font-size: 17px;
        }


        .main-column {
            min-width: 0;
        }

        .rightbar {
            padding: 18px;
            position: sticky;
            top: 94px;
        }

        .rightbar h3 {
            font-size: 14px;
            margin: 0 0 14px;
        }

        .rightbar-section+.rightbar-section {
            border-top: 1px solid var(--line);
            margin-top: 20px;
            padding-top: 18px;
        }

        .tag {
            display: inline-flex;
            align-items: center;
            border-radius: 7px;
            padding: 4px 8px;
            font-size: 11px;
            font-weight: 800;
            background: var(--chip);
            color: var(--primary);
        }

        .tag.goal {
            background: #fff3df;
            color: #a96813;
        }

        .tag.progress {
            background: #e7f8f1;
            color: #16856b;
        }

        .tag.achievement {
            background: #fce9f4;
            color: #a63f7b;
        }

        .tag.knowledge {
            background: #eeeaff;
            color: #6650c9;
        }


        .page-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 0 0 18px;
            gap: 12px;
        }

        .page-heading h1 {
            font-size: 24px;
            line-height: 1.3;
            margin: 0;
            letter-spacing: -.5px;
        }

        .subheading {
            color: var(--muted);
            margin: 5px 0 0;
        }

        .tabs {
            display: flex;
            gap: 22px;
            border-bottom: 1px solid var(--line);
            margin-bottom: 16px;
        }

        .tab {
            padding: 13px 2px;
            color: var(--muted);
            font-weight: 750;
            border-bottom: 2px solid transparent;
        }

        .tab.active {
            color: var(--primary);
            border-color: var(--accent);
        }


        .composer {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 16px;
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .composer-prompt {
            flex: 1;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 12px 14px;
            color: var(--faint);
            background: var(--surface-sunken);
        }

        .composer-actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
            flex-wrap: wrap;
        }


        .post-card {
            padding: 20px;
            margin-bottom: 14px;
        }

        .post-head {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .post-author {
            font-weight: 800;
        }

        .post-meta {
            font-size: 12px;
            color: var(--muted);
        }

        .post-title {
            font-size: 17px;
            margin: 15px 0 7px;
            font-weight: 800;
            line-height: 1.5;
        }

        .post-body {
            color: var(--text-soft);
            white-space: pre-line;
        }

        .post-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            background: linear-gradient(135deg,var(--image-from),var(--image-to));
            border-radius: 12px;
            margin-top: 14px;
            display: grid;
            place-items: center;
            color: var(--image-text);
            font-weight: 700;
        }

        /* 投稿画像を切り取らず、縦横比のまま表示する（ホーム・検索・投稿詳細）
           縦長の画像も全体が画面に収まるよう、高さは画面の80%までにし、横幅が余るときは中央に置く */
        img.post-image-fit {
            display: block;
            width: auto;
            max-width: 100%;
            height: auto;
            max-height: 80vh;
            margin-inline: auto;
        }

        .post-footer {
            display: flex;
            gap: 22px;
            border-top: 1px solid var(--line);
            padding-top: 13px;
            margin-top: 16px;
            color: var(--muted);
        }

        .post-action {
            background: transparent;
            padding: 4px 0;
            color: inherit;
            font-weight: 650;
        }

        .post-action:hover {
            color: var(--accent);
        }


        .form-card {
            padding: 24px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-weight: 750;
            margin-bottom: 7px;
        }

        .form-control {
            width: 100%;
            border: 1px solid var(--line-strong);
            border-radius: 10px;
            padding: 12px 13px;
            background: var(--surface);
            outline: none;
        }

        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px #d9620f26;
        }

        .form-help {
            font-size: 12px;
            color: var(--muted);
            margin-top: 5px;
        }

        .form-error {
            color: #c0394c;
            font-size: 12px;
            margin-top: 4px;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 24px;
        }

        .choice-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .choice-card {
            padding: 18px;
            border: 1px solid var(--line);
            border-radius: 13px;
            background: var(--surface);
            display: block;
        }

        .choice-card:hover {
            border-color: var(--accent);
            background: var(--accent-soft);
        }

        .choice-card strong {
            display: block;
            font-size: 15px;
            margin: 7px 0;
        }

        .choice-card span {
            color: var(--muted);
            font-size: 12px;
        }


        .auth-wrap {
            min-height: calc(100vh - 72px);
            display: grid;
            place-items: center;
            padding: 35px 20px;
        }

        .auth-card {
            width: min(100%,440px);
            padding: 32px;
        }

        .auth-card h1 {
            font-size: 25px;
            margin: 0;
        }

        .auth-card .subheading {
            margin-bottom: 25px;
        }

        .auth-foot {
            text-align: center;
            color: var(--muted);
            margin-top: 20px;
        }

        /* ゲスト向けのログインモーダル（auth/_login-modal） */
        .login-modal {
            border: 0;
            padding: 0;
            background: transparent;
            width: min(calc(100% - 32px), 440px);
            max-width: none;
        }

        .login-modal::backdrop {
            background: rgba(28, 27, 26, .5);
        }

        .login-modal .auth-card {
            position: relative;
            width: 100%;
        }

        .login-modal-close {
            position: absolute;
            top: 12px;
            right: 12px;
            padding: 4px 10px;
            background: transparent;
            color: var(--muted);
            font-size: 20px;
            line-height: 1;
        }

        .alert {
            padding: 12px 14px;
            background: #fff0f0;
            color: #b42332;
            border-radius: 10px;
            margin-bottom: 16px;
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: var(--muted);
            background: var(--surface);
            border: 1px dashed var(--line-strong);
            border-radius: 16px;
        }


        .admin-shell {
            max-width: 1180px;
            margin: 28px auto;
            padding: 0 24px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4,1fr);
            gap: 14px;
        }

        .stat-card {
            padding: 20px;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 15px;
        }

        .stat-label {
            color: var(--muted);
            font-weight: 650;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 850;
            margin-top: 8px;
        }


        .btn-danger {
            background: #c0394c;
            color: #fff;
        }

        .btn-danger:hover {
            background: #a12d3e;
        }

        .btn-sm {
            padding: 6px 11px;
            font-size: 12px;
            border-radius: 8px;
        }

        .flash {
            padding: 12px 14px;
            background: #e7f8f1;
            color: var(--green);
            border-radius: 10px;
            margin-bottom: 16px;
            font-weight: 700;
        }


        .filter-bar {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }

        .filter-bar .form-control {
            width: auto;
            flex: 1;
            min-width: 180px;
        }

        .filter-bar select.form-control {
            flex: 0 0 160px;
        }


        .table-wrap {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 16px;
            overflow-x: auto;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 760px;
        }

        .admin-table th,
        .admin-table td {
            padding: 12px 14px;
            border-bottom: 1px solid var(--line);
            text-align: left;
            vertical-align: top;
        }

        .admin-table th {
            font-size: 12px;
            color: var(--muted);
            background: var(--surface-sunken);
            white-space: nowrap;
        }

        .admin-table tr:last-child td {
            border-bottom: 0;
        }

        .admin-table .nowrap {
            white-space: nowrap;
        }

        .text-muted {
            color: var(--muted);
        }

        .truncate {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }


        .pagination {
            display: flex;
            gap: 6px;
            list-style: none;
            padding: 0;
            margin: 18px 0 0;
            flex-wrap: wrap;
        }

        .pagination .page-link {
            display: inline-block;
            min-width: 36px;
            text-align: center;
            padding: 7px 10px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--surface);
        }

        .pagination .active>span,
        .pagination .active .page-link {
            background: var(--primary);
            border-color: var(--primary);
            color: var(--on-primary);
        }

        .pagination .disabled .page-link {
            color: var(--disabled);
        }


        .detail-list {
            display: grid;
            grid-template-columns: 140px 1fr;
            gap: 10px 16px;
            margin: 0;
        }

        .detail-list dt {
            color: var(--muted);
            font-weight: 700;
        }

        .detail-list dd {
            margin: 0;
        }

        .reply-item {
            padding: 12px 0;
            border-top: 1px solid var(--line);
        }


        .footer {
            text-align: center;
            color: var(--faint);
            padding: 28px;
            font-size: 12px;
        }


        @media (max-width:1100px) {
            .page-shell {
                grid-template-columns: 190px minmax(0,1fr);
            }

            .rightbar {
                display: none;
            }

            .topbar {
                padding: 0 20px;
            }
        }


        @media (max-width:720px) {
            .topbar {
                height: 62px;
                gap: 12px;
                padding: 0 14px;
            }

            .brand {
                font-size: 20px;
            }

            .top-search {
                display: none;
            }

            .page-shell {
                display: block;
                margin: 16px auto;
                padding: 0 12px;
            }

            .sidebar {
                position: static;
                margin-bottom: 14px;
                display: flex;
                overflow: auto;
                padding: 7px;
                gap: 3px;
            }

            .side-label {
                display: none;
            }

            .nav-link {
                white-space: nowrap;
                padding: 9px;
            }

            .nav-link span:last-child {
                display: none;
            }

            .main-column {
                width: 100%;
            }

            .post-card {
                padding: 15px;
            }

            .page-heading h1 {
                font-size: 21px;
            }

            .form-card {
                padding: 18px;
            }

            .choice-grid,
            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }

            .composer {
                align-items: flex-start;
            }

            .composer .btn {
                padding: 8px;
            }

            .auth-card {
                padding: 23px;
            }
        }
    </style>
</head>
<body>
<header class="topbar">
    <a class="brand" href="{{ auth()->check() ? auth()->user()->role->homePath() : route('home') }}"><span class="brand-mark">Y</span>YourGoal</a>
    {{-- 検索は一般ユーザー向けの機能のため、管理者には表示しない --}}
    @unless(auth()->user()?->isAdmin())
    <form class="top-search" method="GET" action="{{ \Illuminate\Support\Facades\Route::has('posts.search') ? route('posts.search') : url('/search') }}">
        <span class="search-icon">⌕</span>
        <input type="search" name="keyword" value="{{ request('keyword') }}" placeholder="気になる投稿を検索">
    </form>
    @endunless
    <div class="top-actions">
        @auth
            @if(auth()->user()->isAdmin())
                <a class="btn btn-light" href="{{ route('admin.dashboard') }}">管理画面</a>
            @else
                <a href="{{ \Illuminate\Support\Facades\Route::has('profile.edit') ? route('profile.edit') : url('/profile/edit') }}" style="display:inline-flex" aria-label="プロフィール">
                    {{-- ログイン中のユーザーは auth()->user() で取る（画面ごとに $user を渡さなくてよい） --}}
                    <x-avatar :user="auth()->user()" />
                </a>
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
{{-- ゲストがログインの必要な機能を使おうとしたときに開くモーダル（ログイン・新規登録の画面では出さない） --}}
@guest
    @unless(request()->routeIs('login', 'register', 'admin.login', 'admin.register'))
        @include('auth._login-modal')
    @endunless
@endguest
<footer class="footer">© {{ date('Y') }} YourGoal — 小さな一歩を、みんなの成長に。</footer>
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

    // ゲスト向けログインモーダル（auth/_login-modal）
    // data-requires-login を付けたリンク・フォームは、ゲストのときだけ画面遷移を止めてモーダルを開く
    const loginModal = document.getElementById('login-modal');
    if (loginModal) {
        const openLoginModal = () => {
            loginModal.showModal();
            loginModal.querySelector('input[name="email"]').focus();
        };

        document.addEventListener('click', (event) => {
            if (event.target.closest('a[data-requires-login]')) {
                event.preventDefault();
                openLoginModal();
            }
            // ×ボタン、またはモーダルの外側（背景）を押したら閉じる
            if (event.target.closest('[data-close-login-modal]') || event.target === loginModal) {
                loginModal.close();
            }
        });

        document.addEventListener('submit', (event) => {
            if (event.target.matches('form[data-requires-login]')) {
                event.preventDefault();
                openLoginModal();
            }
        });

        // ログインに失敗して戻ってきたときは、エラーを見せるために開き直す
        @if(old('login_modal'))
            openLoginModal();
        @endif
    }

    // シェアボタン（posts/_share-button）：端末の共有メニューを開く（使えないブラウザでは URL をコピーする）
    document.addEventListener('click', async (event) => {
        // 押された場所から、いちばん近いシェアボタンを探す（ボタンの中の文字を押しても拾えるように）
        const button = event.target.closest('.share-btn');
        if (!button) return; // シェアボタン以外のクリックは何もしない

        const url = button.dataset.shareUrl;     // data-share-url の値
        const title = button.dataset.shareTitle; // data-share-title の値

        // 1) スマホなど、共有メニューが使える端末
        if (navigator.share) {
            try {
                await navigator.share({ title, url });
            } catch (error) {
                // 共有メニューを閉じただけのとき（AbortError）は何もしない
            }
            return;
        }

        // 2) 使えないブラウザ：URL をクリップボードにコピーする
        try {
            await navigator.clipboard.writeText(url);
            alert('リンクをコピーしました');
        } catch (error) {
            alert('リンクをコピーできませんでした');
        }
    });
</script>
</body>
</html>
