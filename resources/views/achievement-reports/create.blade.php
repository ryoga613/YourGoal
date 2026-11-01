@extends('layouts.app')
@section('title', '達成を報告する')
@section('content')
<div class="page-shell" style="grid-template-columns:220px minmax(0,760px)">
    <aside class="sidebar"><a class="nav-link" href="{{ url('/') }}"><span class="nav-icon">⌂</span><span>ホーム</span></a><a class="nav-link active" href="#"><span class="nav-icon">✎</span><span>達成を報告する</span></a></aside>
    <main>
        <div class="page-heading"><div><h1>達成を報告する</h1><p class="subheading">目標に向けた努力と、達成できたことをシェアしましょう。</p></div></div>
        <form class="form-card" method="POST" action="{{ route('achievement-reports.store', $goal) }}" enctype="multipart/form-data">
            @csrf
            <div class="form-help" style="margin:0 0 18px"><span class="tag goal">目標</span> {{ $goal->title }}</div>
            <div class="form-group"><label for="title">タイトル <span style="color:#d34b5a">*</span></label><input class="form-control" id="title" name="title" value="{{ old('title') }}" placeholder="報告のタイトル" required>@error('title')<div class="form-error">{{ $message }}</div>@enderror</div>
            <div class="form-group"><label for="body">本文（任意）</label><textarea class="form-control" id="body" name="body" rows="8" placeholder="取り組んだこと、気づいたこと、次にやることなどを書いてみましょう。">{{ old('body') }}</textarea>@error('body')<div class="form-error">{{ $message }}</div>@enderror</div>
            <div class="form-group"><label for="image">画像（任意）</label><input class="form-control" id="image" type="file" name="image" accept="image/*"><div class="form-help">画像は1枚まで添付できます。</div>@error('image')<div class="form-error">{{ $message }}</div>@enderror</div>
            <div class="form-help">投稿が保存されると、目標は「達成済み」になります。</div>
            <div class="form-actions"><a class="btn btn-outline" href="{{ url('/') }}">キャンセル</a><button class="btn btn-primary" type="submit">達成を報告する →</button></div>
        </form>
    </main>
</div>
@endsection
