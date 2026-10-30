<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

/**
 * ログイン後、ロールに応じて遷移先を切り替える
 */
class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): Response
    {
        if ($request->wantsJson()) {
            return new JsonResponse(['two_factor' => false]);
        }

        // 管理者と一般ユーザーで操作できる画面が異なるため、intended は使わずロールのホームへ固定する
        return redirect($request->user()->role->homePath());
    }
}
