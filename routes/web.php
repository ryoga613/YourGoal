<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect(auth()->user()->role->homePath())
        : redirect()->route('login');
});

// 一般ユーザー向け（管理者はアクセス不可）
Route::middleware(['auth', 'general'])->group(function () {
    Route::view('/home', 'home')->name('home');
});

// 管理者向け（一般ユーザーはアクセス不可）
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::view('/dashboard', 'admin.dashboard')->name('dashboard');
});
