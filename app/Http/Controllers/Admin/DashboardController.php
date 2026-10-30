<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * ユーザー数・投稿数・コメント数・ジャンル数を表示
     */
    public function index()
    {
        return view('admin.dashboard');
    }
}
