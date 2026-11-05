<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ページ送り（$paginator->links()）を Bootstrap 4 の HTML で出力する。
        // 標準は Tailwind 用の HTML だが、このアプリは Tailwind を使っていないので見た目が崩れる。
        // Bootstrap 4 の HTML（.pagination / .page-item / .page-link）には layouts/app.blade.php の CSS が当たる。
        Paginator::useBootstrapFour();
    }
}
