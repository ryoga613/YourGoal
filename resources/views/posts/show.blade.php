@extends('layouts.app')
@section('title', data_get($post,'title','投稿詳細'))
@section('content')
<div class="page-shell" style="grid-template-columns:220px minmax(0,760px)">
    <aside class="sidebar"><a class="nav-link" href="{{ url('/') }}"><span class="nav-icon">⌂</span><span>ホーム</span></a><a class="nav-link" href="{{ url('/search') }}"><span class="nav-icon">⌕</span><span>投稿を探す</span></a></aside>
    <main>
        <a href="{{ url('/') }}" class="post-meta">← ホームに戻る</a>
        <article class="post-card" style="margin-top:15px">
            <div class="post-head">
                <div class="avatar">{{ mb_substr(data_get($post,'user.name','U'),0,1) }}</div>
                <div>
                    <div class="post-author">{{ data_get($post,'user.name','ユーザー') }}</div>
                    <div class="post-meta">{{ data_get($post,'created_at') }}</div>
                </div>

                {{-- 右上：投稿の種類（目標宣言はステータスも）と、本人だけの編集・削除ボタン --}}
                <div style="margin-left:auto;display:flex;flex-wrap:wrap;align-items:center;justify-content:flex-end;gap:8px">
                    <span class="tag {{ $post->postType()->tagClass() }}">{{ $post->postType()->label() }}</span>
                    {{-- ステータスがあるのは目標宣言だけ --}}
                    @if($post->postType() === \App\Enums\PostType::Goal)
                        <span class="tag">{{ $post->status->label() }}</span>
                    @endif

                    @can('update', $post)
                        <a class="btn btn-light btn-sm" href="{{ route('posts.edit', ['type' => request()->route('type'), 'id' => $post->id]) }}">編集</a>
                    @endcan
                    @can('delete', $post)
                        <form action="{{ route('posts.destroy', ['type' => request()->route('type'), 'id' => $post->id]) }}" method="POST" style="margin:0" onsubmit="return confirm('この投稿を削除しますか？\nコメント・いいねも一緒に削除されます。')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">削除</button>
                        </form>
                    @endcan
                </div>
            </div>
            <h1 class="post-title" style="font-size:24px">{{ data_get($post,'title','投稿タイトル') }}</h1>
            <p class="post-body">{{ data_get($post,'body',data_get($post,'content','')) }}</p>
            @if(data_get($post,'image_path'))
                <img class="post-image" src="{{ asset('storage/'.data_get($post,'image_path')) }}" alt="投稿画像">
            @endif
            <div class="post-footer">
                @include('posts._like-button', [
                    'type' => request()->route('type'),
                    'id' => $post->id,
                    'liked' => (bool) $post->liked,
                    'count' => $post->post_likes_count,
                ])
                <span>♧ {{ data_get($post,'comments_count',0) }} コメント</span>
                @include('posts._share-button', [
                    'type' => request()->route('type'),
                    'id' => $post->id,
                    'title' => data_get($post, 'title') ?? $post->postType()->label(),
                ])
            </div>
        </article>

        {{-- 目標宣言だけ：報告ボタン・ステータス変更（本人のみ）と、進捗の記録 --}}
        @if($post->postType() === \App\Enums\PostType::Goal)
            <section id="goal-actions">
                @if (session('goalStatus'))<div class="flash">{{ session('goalStatus') }}</div>@endif

                {{-- 目標を宣言した本人だけが、進捗・達成を報告できる --}}
                @can('create', [\App\Models\ProgressReport::class, $post])
                    <div class="composer" style="flex-wrap:wrap">
                        <strong style="width:100%">次の一歩を記録しよう</strong>
                        <a class="btn btn-light" href="{{ route('progress-reports.create', $post) }}">＋ 進捗を報告</a>
                        {{-- 達成済み・中止の目標には、達成報告できないようにする（AchievementReportPolicy@create） --}}
                        @can('create', [\App\Models\AchievementReport::class, $post])
                            <a class="btn btn-primary" href="{{ route('achievement-reports.create', $post) }}">✓ 達成を報告</a>
                        @endcan
                    </div>

                    {{-- 達成済みは達成報告でのみ切り替わるので、達成前の目標だけステータスを変更できる --}}
                    @if($post->status !== \App\Enums\GoalPostStatus::Achieved)
                        <form class="composer" style="flex-wrap:wrap" method="POST" action="{{ route('goals.status.update', $post) }}">
                            @csrf
                            @method('PATCH')
                            <label for="status" style="font-weight:700">ステータス</label>
                            <select class="form-control" id="status" name="status" style="width:auto">
                                @foreach([\App\Enums\GoalPostStatus::Considering, \App\Enums\GoalPostStatus::InProgress, \App\Enums\GoalPostStatus::Cancelled] as $status)
                                    <option value="{{ $status->value }}" @selected($post->status === $status)>{{ $status->label() }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-light" type="submit">変更する</button>
                            @error('status')<div class="form-error" style="width:100%">{{ $message }}</div>@enderror
                        </form>
                    @endif
                @endcan

                <h2 style="font-size:18px;margin:24px 0 14px">進捗の記録</h2>
                @forelse($post->progress_reports as $report)
                    <a class="post-card" style="display:block" href="{{ route('posts.show', ['type' => 'progress', 'id' => $report->id]) }}">
                        <div class="post-head">
                            <span class="tag progress">進捗報告</span>
                            <span class="post-meta">{{ $report->created_at->format('Y年n月j日') }}</span>
                        </div>
                        <h3 class="post-title">{{ $report->title ?? '進捗報告' }}</h3>
                        <p class="post-body">{{ $report->body }}</p>
                    </a>
                @empty
                    <div class="empty-state">まだ進捗報告はありません。最初の記録を残してみましょう。</div>
                @endforelse
            </section>
        @endif

        <section class="form-card" id="comments">
            @if (session('status'))<div class="flash">{{ session('status') }}</div>@endif
            <h2 style="font-size:17px;margin-top:0">コメントする</h2>
            <form method="POST" action="{{ route('comments.store', ['type' => request()->route('type'), 'id' => $post->id]) }}">
                @csrf
                <div class="form-group">
                    {{-- 返信・編集フォームで失敗したときの入力値は、そちらのフォームに戻す --}}
                    <textarea class="form-control" name="body" rows="3" maxlength="1000" placeholder="応援や感想をコメントしよう" required>{{ old('reply_to') || old('editing') ? '' : old('body') }}</textarea>
                    @error('body')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                <div class="form-actions"><button class="btn btn-primary" type="submit">コメントを投稿</button></div>
            </form>
            <h3 style="font-size:15px;margin-top:26px">みんなのコメント</h3>
            @forelse ($commentsByParent->get(0, collect()) as $comment)
                @include('posts._comment', ['comment' => $comment, 'depth' => 0])
            @empty
                <p class="post-meta">まだコメントはありません。最初のコメントを投稿してみましょう。</p>
            @endforelse
        </section>
    </main>
</div>
@endsection
