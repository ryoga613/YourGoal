@extends('layouts.app')
@section('title', '進捗を報告する')
@section('content')
<div class="page-shell" style="grid-template-columns:220px minmax(0,760px)">
    <aside class="sidebar"><a class="nav-link" href="{{ url('/') }}"><span class="nav-icon">⌂</span><span>ホーム</span></a><a class="nav-link active" href="#"><span class="nav-icon">✎</span><span>進捗を報告する</span></a></aside>
    <main>
        <div class="page-heading"><div><h1>進捗を報告する</h1><p class="subheading">目標に向けた一歩や、途中で得た気づきを記録しましょう。</p></div></div>
        <form class="form-card" method="POST" action="{{ \\Illuminate\\Support\\Facades\\Route::has('progress-reports.store') ? route('progress-reports.store', ['goal' => $goal->id ?? request()->route('goal')]) : url('/goals') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group"><label for="title">タイトル（任意）</label><input class="form-control" id="title" name="title" value="{{ old('title') }}" placeholder="報告のタイトル"></div>
            <div class="form-group"><label for="body">本文 <span style="color:#d34b5a">*</span></label><textarea class="form-control" id="body" name="body" rows="8" required placeholder="取り組んだこと、気づいたこと、次にやることなどを書いてみましょう。">{{ old('body') }}</textarea>@error('body')<div class="form-error">{{ $message }}</div>@enderror</div>
            <div class="form-group"><label for="image">画像（任意）</label><input class="form-control" id="image" type="file" name="image" accept="image/*"><div class="form-help">画像は1枚まで添付できます。</div></div>
            <div class="form-help">この報告は対象の目標に紐づきます。</div>
            <div class="form-actions"><a class="btn btn-outline" href="{{ url('/') }}">キャンセル</a><button class="btn btn-primary" type="submit">進捗を投稿 →</button></div>
        </form>
    </main>
</div>
@endsection
