<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProgressReportRequest extends FormRequest
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
            'title' => ['required_without_all:body,image', 'nullable', 'string', 'max:255'],
            'body' => ['required_without_all:title,image', 'nullable', 'string', 'max:1000'],
            'image' => ['required_without_all:body,title', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.string' => 'タイトルは文字で入力してください。',
            'title.max' => 'タイトルは255文字以内で入力してください。',

            'body.string' => '本文は文字で入力してください。',
            'body.max' => '本文は1000文字以内で入力してください。',

            'image.image' => '画像ファイルを選択してください。',
            'image.mimes' => '画像はjpg・png・webp形式でアップロードしてください。',
            'image.max' => '画像は2MB以下にしてください。',

            'title.required_without_all' => 'タイトル・本文・画像のいずれかを入力してください。',
            'body.required_without_all' => 'タイトル・本文・画像のいずれかを入力してください。',
            'image.required_without_all' => 'タイトル・本文・画像のいずれかを入力してください。',
        ];
    }
}
