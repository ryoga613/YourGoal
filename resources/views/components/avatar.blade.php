{{--
    ユーザーのアイコン。プロフィール画像があれば画像、なければ名前の頭文字を出す。
    使い方：<x-avatar :user="$post->user" />
            <x-avatar :user="$user" :size="72" />          … 大きさ（px）を変える
            <x-avatar name="Mika S." />                     … ユーザーがいない見本の投稿など
--}}
@props(['user' => null, 'name' => null, 'size' => null])

@php
    $name = $user?->name ?? $name ?? 'U';
    // 大きさを変えるときは、頭文字の文字の大きさも合わせる
    $sizeStyle = $size ? "width:{$size}px;height:{$size}px;font-size:".round($size * 0.4).'px' : null;
@endphp

@if ($user?->profile_image_path)
    <img {{ $attributes->class('avatar')->merge(['style' => $sizeStyle]) }}
         src="{{ asset('storage/'.$user->profile_image_path) }}"
         alt="{{ $name }}さんのプロフィール画像">
@else
    <div {{ $attributes->class('avatar')->merge(['style' => $sizeStyle]) }}>{{ mb_substr($name, 0, 1) }}</div>
@endif
