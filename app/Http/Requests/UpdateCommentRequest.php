<?php

namespace App\Http\Requests;

/**
 * 自分のコメントの編集（入力ルールはコメント投稿と同じ）
 */
class UpdateCommentRequest extends StoreCommentRequest
{
    /**
     * 書いた本人だけが編集できる（CommentPolicy@update）
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('comment'));
    }
}
