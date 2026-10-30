<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 一般ユーザーのみ通過させる（管理者は一般ユーザーの操作ができない）
 */
class EnsureUserIsGeneral
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->isGeneral(), Response::HTTP_FORBIDDEN);

        return $next($request);
    }
}
