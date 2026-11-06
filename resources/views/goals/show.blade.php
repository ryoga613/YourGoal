@extends('layouts.app')
@section('title', $goal->title)
@section('content')
<div class="page-shell" style="grid-template-columns:220px minmax(0,760px)">
    <aside class="sidebar">
        <a class="nav-link" href="{{ route('home') }}"><span class="nav-icon">⌂</span><span>ホーム</span></a>
        <a class="nav-link" href="{{ route('posts.create') }}"><span class="nav-icon">＋</span><span>投稿を作成</span></a>
        <a class="nav-link" href="{{ route('posts.search') }}"><span class="nav-icon">⌕</span><span>投稿を探す</span></a>
    </aside>

    <main>
        <div class="page-heading">
            <div>
                <span class="tag goal">目標宣言</span>
                <h1 style="margin-top:10px">{{ $goal->title }}</h1>
                <p class="subheading">目標と、ここまでの歩み。</p>
            </div>
        </div>

        @if (session('status'))<div class="flash">{{ session('status') }}</div>@endif

        <article class="post-card">
            <div class="post-head">
                @if($goal->user->profile_image_path)
                    <img class="avatar" src="{{ asset('storage/'.$goal->user->profile_image_path) }}" alt="">
                @else
                    <div class="avatar">{{ mb_substr($goal->user->name, 0, 1) }}</div>
                @endif
                <div>
                    <div class="post-author">{{ $goal->user->name }}</div>
                    <div class="post-meta">{{ $goal->created_at->format('Y年n月j日') }}</div>
                </div>
                <span class="tag" style="margin-left:auto">{{ $goal->status->label() }}</span>
            </div>

            <p class="post-body" style="margin-top:18px">{{ $goal->body }}</p>

            @if($goal->image_path)
                <img class="post-image" src="{{ asset('storage/'.$goal->image_path) }}" alt="目標画像">
            @endif

            <div class="post-footer">
                <span>♡ {{ $goal->post_likes_count }} いいね</span>
                <span>♧ {{ $goal->comments_count }} コメント</span>
            </div>
        </article>

        {{-- 目標を宣言した本人だけが、進捗・達成を報告できる --}}
        @if(auth()->id() === $goal->user_id)
            <div class="composer" style="flex-wrap:wrap">
                <strong style="width:100%">次の一歩を記録しよう</strong>
                <a class="btn btn-light" href="{{ route('progress-reports.create', $goal) }}">＋ 進捗を報告</a>
                {{-- 達成済みの目標には、もう達成報告できないようにする --}}
                @if($goal->status !== \App\Enums\GoalPostStatus::Achieved)
                    <a class="btn btn-primary" href="{{ route('achievement-reports.create', $goal) }}">✓ 達成を報告</a>
                @endif
            </div>

            {{-- 達成済みは達成報告でのみ切り替わるので、達成前の目標だけステータスを変更できる --}}
            @if($goal->status !== \App\Enums\GoalPostStatus::Achieved)
                <form class="composer" style="flex-wrap:wrap" method="POST" action="{{ route('goals.status.update', $goal) }}">
                    @csrf
                    @method('PATCH')
                    <label for="status" style="font-weight:700">ステータス</label>
                    <select class="form-control" id="status" name="status" style="width:auto">
                        @foreach([\App\Enums\GoalPostStatus::Considering, \App\Enums\GoalPostStatus::InProgress, \App\Enums\GoalPostStatus::Cancelled] as $status)
                            <option value="{{ $status->value }}" @selected($goal->status === $status)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-light" type="submit">変更する</button>
                    @error('status')<div class="form-error" style="width:100%">{{ $message }}</div>@enderror
                </form>
            @endif
        @endif

        <h2 style="font-size:18px;margin:24px 0 14px">進捗の記録</h2>
        @forelse($goal->progress_reports as $report)
            <article class="post-card">
                <div class="post-head">
                    <span class="tag progress">進捗報告</span>
                    <span class="post-meta">{{ $report->created_at->format('Y年n月j日') }}</span>
                </div>
                <h3 class="post-title">{{ $report->title ?? '進捗報告' }}</h3>
                <p class="post-body">{{ $report->body }}</p>
            </article>
        @empty
            <div class="empty-state">まだ進捗報告はありません。最初の記録を残してみましょう。</div>
        @endforelse
    </main>
</div>
@endsection
