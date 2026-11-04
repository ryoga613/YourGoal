<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 投稿へのコメント・コメントへの返信
 */
class StoreCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // ログインしている一般ユーザーなら誰でもコメントできる（ルートの auth / general ミドルウェアで確認済み）
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'body.required' => 'コメントを入力してください。',
            'body.string' => 'コメントは文字で入力してください。',
            'body.max' => 'コメントは1000文字以内で入力してください。',
        ];
    }

    /**
     * 詳細画面にはコメント・返信・編集のフォームが並ぶので、エラーをどのフォームの下に出すか区別できるようにする
     */
    protected function prepareForValidation(): void
    {
        $this->errorBag = match ($this->route()?->getName()) {
            'comments.reply' => 'reply',
            'comments.update' => 'commentUpdate',
            default => 'default',
        };
    }
}
