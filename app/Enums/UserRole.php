<?php

namespace App\Enums;

enum UserRole: string
{
    case User = 'user';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::User => '一般ユーザー',
            self::Admin => '管理者',
        };
    }

    /**
     * ログイン後に遷移させるパス
     */
    public function homePath(): string
    {
        return match ($this) {
            self::User => route('home', absolute: false),
            self::Admin => route('admin.dashboard', absolute: false),
        };
    }
}
