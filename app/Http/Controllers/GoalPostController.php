<?php

namespace App\Http\Controllers;

use App\Models\GoalPost;
use Illuminate\Http\Request;

class GoalPostController extends Controller
{
    /**
     * 目標宣言フォームを表示
     */
    public function create()
    {
        //
    }

    /**
     * 目標を作成し初期状態を挑戦中にする
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * 目標と関連する進捗・達成報告を表示
     */
    public function show(GoalPost $goal)
    {
        //
    }
}
