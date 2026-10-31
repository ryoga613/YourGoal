<?php

namespace App\Support;

/**
 * LIKE検索のキーワードをエスケープする処理（複数のコントローラーで使う）
 */
trait EscapeLike
{
    /**
     * LIKE検索で % と _ をワイルドカードではなく文字として扱うようにエスケープする
     */
    private function escapeLike(string $value): string
    {
        return addcslashes($value, '\\%_');
    }
}
