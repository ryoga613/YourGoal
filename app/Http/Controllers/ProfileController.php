<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * プロフィール・投稿・目標・達成履歴を表示
     */
    public function show(User $user)
    {
        //
    }

    /**
     * 自分のプロフィール編集フォームを表示
     */
    public function edit()
    {
        //
    }

    /**
     * 表示名・自己紹介・プロフィール画像を更新
     */
    public function update(Request $request)
    {
        //
    }
}
