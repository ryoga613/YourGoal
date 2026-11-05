<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:500'],
            'profile_image_path' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ];

    }

    public function messages(): array
    {
        return [
            'name.required' => '名前は必須です。',
            'name.string' => '名前は文字列である必要があります。',
            'name.max' => '名前は255文字以内である必要があります。',
            'bio.nullable' => '自己紹介はnullableです。',
            'bio.string' => '自己紹介は文字列である必要があります。',
            'bio.max' => '自己紹介は500文字以内である必要があります。',
            'profile_image_path.image' => 'プロフィール画像は画像ファイルである必要があります。',
            'profile_image_path.mimes' => 'プロフィール画像はjpeg, png, jpg, gif, svg形式のいずれかである必要があります。',
            'profile_image_path.max' => 'プロフィール画像は2MB以下である必要があります。',

        ];
    }
}
