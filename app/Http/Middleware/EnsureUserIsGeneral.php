<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 管理者を通さない（管理者は一般ユーザーの操作ができない）
 *
 * ゲストは通す。ログインを必須にする画面は auth と組み合わせる
 */
class EnsureUserIsGeneral
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_if($request->user()?->isAdmin(), Response::HTTP_FORBIDDEN);

        return $next($request);
    }
}
