<?php

use App\Http\Controllers\AchievementReportController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CommentLikeController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\GoalPostController;
use App\Http\Controllers\GoalStatusController;
use App\Http\Controllers\KnowledgePostController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgressReportController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

// 認証（登録・ログイン・ログアウト）のルートは Fortify が登録する

// 投稿種類（/posts/{type}/{id}）は4種類に限定し、それ以外は404にする
Route::pattern('type', 'goal|progress|achievement|knowledge');
Route::pattern('id', '[0-9]+');

// 一般ユーザー向け（管理者はアクセス不可）
Route::middleware(['auth', 'general'])->group(function () {
    // ホーム・検索
    Route::get('/', [FeedController::class, 'index'])->name('home');
    Route::get('/search', [SearchController::class, 'index'])->name('posts.search');

    // 投稿共通
    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::get('/posts/{type}/{id}', [PostController::class, 'show'])->name('posts.show');
    Route::get('/posts/{type}/{id}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::patch('/posts/{type}/{id}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{type}/{id}', [PostController::class, 'destroy'])->name('posts.destroy');

    // 目標宣言
    Route::get('/goals/create', [GoalPostController::class, 'create'])->name('goals.create');
    Route::post('/goals', [GoalPostController::class, 'store'])->name('goals.store');
    Route::get('/goals/{goal}', [GoalPostController::class, 'show'])->name('goals.show');
    Route::patch('/goals/{goal}/status', [GoalStatusController::class, 'update'])->name('goals.status.update');

    // 進捗報告
    Route::get('/goals/{goal}/progress-reports/create', [ProgressReportController::class, 'create'])->name('progress-reports.create');
    Route::post('/goals/{goal}/progress-reports', [ProgressReportController::class, 'store'])->name('progress-reports.store');
    Route::patch('/progress-reports/{progressReport}', [ProgressReportController::class, 'update'])->name('progress-reports.update');
    Route::delete('/progress-reports/{progressReport}', [ProgressReportController::class, 'destroy'])->name('progress-reports.destroy');

    // 達成報告
    Route::get('/goals/{goal}/achievement-reports/create', [AchievementReportController::class, 'create'])->name('achievement-reports.create');
    Route::post('/goals/{goal}/achievement-reports', [AchievementReportController::class, 'store'])->name('achievement-reports.store');
    Route::patch('/achievement-reports/{achievementReport}', [AchievementReportController::class, 'update'])->name('achievement-reports.update');
    Route::delete('/achievement-reports/{achievementReport}', [AchievementReportController::class, 'destroy'])->name('achievement-reports.destroy');

    // 学び・知識共有
    Route::get('/knowledge-posts/create', [KnowledgePostController::class, 'create'])->name('knowledge-posts.create');
    Route::post('/knowledge-posts', [KnowledgePostController::class, 'store'])->name('knowledge-posts.store');

    // いいね
    Route::post('/posts/{type}/{id}/likes', [LikeController::class, 'store'])->name('likes.store');
    Route::delete('/posts/{type}/{id}/likes', [LikeController::class, 'destroy'])->name('likes.destroy');

    // コメント
    Route::post('/posts/{type}/{id}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::post('/comments/{comment}/replies', [CommentController::class, 'reply'])->name('comments.reply');
    Route::patch('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    // コメントいいね
    Route::post('/comments/{comment}/likes', [CommentLikeController::class, 'store'])->name('comment-likes.store');
    Route::delete('/comments/{comment}/likes', [CommentLikeController::class, 'destroy'])->name('comment-likes.destroy');

    // プロフィール
    Route::get('/users/{user}', [ProfileController::class, 'show'])->name('users.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

// 管理者向け（一般ユーザーはアクセス不可）
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    // ユーザー管理
    Route::get('/users', [Admin\UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [Admin\UserController::class, 'show'])->name('users.show');
    Route::delete('/users/{user}', [Admin\UserController::class, 'destroy'])->name('users.destroy');

    // 投稿管理
    Route::get('/posts', [Admin\PostController::class, 'index'])->name('posts.index');
    Route::get('/posts/{type}/{id}', [Admin\PostController::class, 'show'])->name('posts.show');
    Route::delete('/posts/{type}/{id}', [Admin\PostController::class, 'destroy'])->name('posts.destroy');

    // コメント管理
    Route::get('/comments', [Admin\CommentController::class, 'index'])->name('comments.index');
    Route::get('/comments/{comment}', [Admin\CommentController::class, 'show'])->name('comments.show');
    Route::delete('/comments/{comment}', [Admin\CommentController::class, 'destroy'])->name('comments.destroy');
});
