{{--
    コメント1件と、その返信（入れ子）を表示する
    @param Comment $comment
    @param int $depth 何階層目の返信か（0 = 投稿への直接のコメント）
    @param Collection $commentsByParent 親コメントIDごとの返信一覧
--}}
@php
    $isReplyTarget = (int) old('reply_to') === $comment->id;
    $isEditTarget = (int) old('editing') === $comment->id;
@endphp
{{-- 返信は親の中に入れ子で描画されるので、字下げは1階層ぶん（28px）ずつ積み重なる。深くなりすぎないよう3階層目まで --}}
<div id="comment-{{ $comment->id }}" style="padding:14px 0 0 {{ $depth >= 1 && $depth <= 3 ? 28 : 0 }}px;border-top:1px solid var(--line)">
    <div style="display:flex;gap:10px">
        <x-avatar :user="$comment->user" />
        <div style="flex:1;min-width:0">
            <strong>{{ $comment->user?->name ?? '退会済みユーザー' }}</strong>
            <div class="post-meta">
                {{ $comment->created_at?->format('Y/m/d H:i') }}
                @if ($comment->updated_at?->gt($comment->created_at)) （編集済み）@endif
            </div>
            <p style="margin:7px 0;white-space:pre-wrap;overflow-wrap:anywhere">{{ $comment->body }}</p>

            <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:flex-start;margin-bottom:12px">
                {{-- いいね済みなら解除（DELETE）、まだなら登録（POST）。同じURLでメソッドだけ切り替える --}}
                <form method="POST" action="{{ route($comment->liked ? 'comment-likes.destroy' : 'comment-likes.store', $comment) }}">
                    @csrf
                    @if ($comment->liked)
                        @method('DELETE')
                    @endif
                    <button class="post-action post-meta" type="submit" @if ($comment->liked) style="color:#e0245e" @endif
                            aria-pressed="{{ $comment->liked ? 'true' : 'false' }}" title="{{ $comment->liked ? 'いいねを取り消す' : 'いいねする' }}">
                        {{ $comment->liked ? '♥' : '♡' }} {{ $comment->comment_likes_count }}
                    </button>
                </form>

                <details @if ($isReplyTarget) open @endif>
                    <summary class="post-meta" style="cursor:pointer">返信する</summary>
                    <form method="POST" action="{{ route('comments.reply', $comment) }}" style="margin-top:8px">
                        @csrf
                        <input type="hidden" name="reply_to" value="{{ $comment->id }}">
                        <textarea class="form-control" name="body" rows="2" maxlength="1000" placeholder="{{ $comment->user?->name ?? 'このコメント' }}さんに返信" required>{{ $isReplyTarget ? old('body') : '' }}</textarea>
                        @if ($isReplyTarget)
                            @error('body', 'reply')<div class="form-error">{{ $message }}</div>@enderror
                        @endif
                        <div class="form-actions"><button class="btn btn-primary btn-sm" type="submit">返信を投稿</button></div>
                    </form>
                </details>

                @can('update', $comment)
                    <details @if ($isEditTarget) open @endif>
                        <summary class="post-meta" style="cursor:pointer">編集</summary>
                        <form method="POST" action="{{ route('comments.update', $comment) }}" style="margin-top:8px">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="editing" value="{{ $comment->id }}">
                            <textarea class="form-control" name="body" rows="2" maxlength="1000" required>{{ $isEditTarget ? old('body') : $comment->body }}</textarea>
                            @if ($isEditTarget)
                                @error('body', 'commentUpdate')<div class="form-error">{{ $message }}</div>@enderror
                            @endif
                            <div class="form-actions"><button class="btn btn-primary btn-sm" type="submit">更新する</button></div>
                        </form>
                    </details>
                @endcan

                @can('delete', $comment)
                    <form method="POST" action="{{ route('comments.destroy', $comment) }}"
                          onsubmit="return confirm('このコメントを削除しますか？\n返信がある場合は、返信も一緒に削除されます。')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm" type="submit">削除</button>
                    </form>
                @endcan
            </div>
        </div>
    </div>

    @foreach ($commentsByParent->get($comment->id, collect()) as $reply)
        @include('posts._comment', ['comment' => $reply, 'depth' => $depth + 1])
    @endforeach
</div>
