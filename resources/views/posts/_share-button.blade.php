{{--
    投稿のシェアボタン（ホーム・投稿詳細で共通）
    押したときの処理は layouts/app.blade.php の <script>（.share-btn のクリック）
    @param string $type  投稿の種類（goal / progress / achievement / knowledge）
    @param int|null $id  投稿ID（ホームのデモ投稿など、IDがないときは表示しない）
    @param string $title  共有メニューに出す投稿のタイトル
--}}
@if ($id)
    <button type="button" class="post-action share-btn"
            data-share-url="{{ route('posts.show', ['type' => $type, 'id' => $id]) }}"
            data-share-title="{{ $title }}">
        ↗ シェア
    </button>
@endif
