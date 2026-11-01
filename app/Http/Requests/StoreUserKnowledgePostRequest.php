<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserKnowledgePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
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
            'title' => ['required', 'max:255', 'string'],
            'body' => ['nullable', 'max:1000', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'タイトルを入力してください。',
            'title.string' => 'タイトルは文字で入力してください。',
            'title.max' => 'タイトルは255文字以内で入力してください。',

            'body.string' => '本文は文字で入力してください。',
            'body.max' => '本文は1000文字以内で入力してください。',

            'image.image' => '画像ファイルを選択してください。',
            'image.mimes' => '画像はjpg・png・webp形式でアップロードしてください。',
            'image.max' => '画像は2MB以下にしてください。',
        ];
    }
}
