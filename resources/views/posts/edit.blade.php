@extends('layouts.app')
@section('title', $type->label().'を編集')
@section('content')
<div class="page-shell" style="grid-template-columns:220px minmax(0,760px)">
    <aside class="sidebar">
        <a class="nav-link" href="{{ route('home') }}"><span class="nav-icon">⌂</span><span>ホーム</span></a>
        <a class="nav-link" href="{{ route('posts.search') }}"><span class="nav-icon">⌕</span><span>投稿を探す</span></a>
    </aside>

    <main>
        <div class="page-heading">
            <div>
                <span class="tag {{ $type->tagClass() }}">{{ $type->label() }}</span>
                <h1 style="margin-top:10px">投稿を編集</h1>
            </div>
        </div>

        <form class="form-card" method="POST" action="{{ route('posts.update', ['type' => $type->value, 'id' => $post->id]) }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            {{-- 進捗報告はタイトル・本文・画像のいずれかがあればよく、それ以外はタイトルが必須 --}}
            @php($titleRequired = $type !== \App\Enums\PostType::Progress)

            <div class="form-group">
                <label for="title">タイトル @if($titleRequired)<span style="color:#d34b5a">*</span>@else（任意）@endif</label>
                <input class="form-control" id="title" name="title" value="{{ old('title', $post->title) }}" maxlength="255" @required($titleRequired)>
                @error('title')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="body">本文（任意）</label>
                <textarea class="form-control" id="body" name="body" rows="6" maxlength="1000">{{ old('body', $post->body) }}</textarea>
                @error('body')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="image">画像（任意）</label>
                @if($post->image_path)
                    <img class="post-image" src="{{ asset('storage/'.$post->image_path) }}" alt="現在の画像" style="margin-bottom:10px">
                @endif
                <input class="form-control" id="image" type="file" name="image" accept="image/*">
                <div class="form-help">新しい画像を選ぶと、今の画像と差し替わります。</div>
                @error('image')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-actions">
                <a class="btn btn-outline" href="{{ route('posts.show', ['type' => $type->value, 'id' => $post->id]) }}">キャンセル</a>
                <button class="btn btn-primary" type="submit">更新する</button>
            </div>
        </form>
    </main>
</div>
@endsection
