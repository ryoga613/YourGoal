<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * ユーザー一覧・検索
     */
    public function index(Request $request)
    {
        //
    }

    /**
     * ユーザー情報と関連投稿を表示
     */
    public function show(User $user)
    {
        //
    }

    /**
     * ユーザーを削除
     */
    public function destroy(User $user)
    {
        //
    }
}
