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

        // ログインモーダル（auth/_login-modal）から一般ユーザーがログインしたときは、モーダルを開いたページに戻す
        if ($request->user()->isGeneral() && $this->isSafeRedirectPath($request->input('redirect_to'))) {
            return redirect($request->input('redirect_to'));
        }

        // 管理者と一般ユーザーで操作できる画面が異なるため、intended は使わずロールのホームへ固定する
        return redirect($request->user()->role->homePath());
    }

    /**
     * 同じサイト内のパス（/ から始まる）だけを戻り先として受け付ける
     * 「//evil.example」や「/\\evil.example」は別サイトへ飛ばされるので受け付けない
     */
    private function isSafeRedirectPath(mixed $path): bool
    {
        return is_string($path)
            && str_starts_with($path, '/')
            && ! str_starts_with($path, '//')
            && ! str_contains($path, '\\');
    }
}
