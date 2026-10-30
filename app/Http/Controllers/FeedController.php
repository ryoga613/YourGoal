<?php

namespace App\Http\Controllers;

class FeedController extends Controller
{
    /**
     * 投稿一覧を新着順または人気順で表示
     */
    public function index()
    {
        return view('home');
    }
}
