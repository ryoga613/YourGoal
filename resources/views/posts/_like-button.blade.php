{{--
    投稿のいいねボタン（ホーム・投稿詳細で共通）
    @param string $type  投稿の種類（goal / progress / achievement / knowledge）
    @param int|null $id  投稿ID（ホームのデモ投稿など、IDがないときはボタンだけ表示する）
    @param bool $liked  ログイン中のユーザーがいいね済みか
    @param int $count  いいね数
--}}
@if ($id)
    {{-- いいね済みなら解除（DELETE）、まだなら登録（POST）。同じURLでメソッドだけ切り替える --}}
    <form method="POST" action="{{ route($liked ? 'likes.destroy' : 'likes.store', ['type' => $type, 'id' => $id]) }}" style="display:inline">
        @csrf
        @if ($liked)
            @method('DELETE')
        @endif
        <button class="post-action" type="submit" @if ($liked) style="color:#e0245e" @endif
                aria-pressed="{{ $liked ? 'true' : 'false' }}" title="{{ $liked ? 'いいねを取り消す' : 'いいねする' }}">
            {{ $liked ? '♥' : '♡' }} {{ $count }} いいね
        </button>
    </form>
@else
    <span class="post-action">♡ {{ $count }} いいね</span>
@endif
