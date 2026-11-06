<?php

namespace App\Http\Requests;

use App\Enums\PostType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 自分の投稿のタイトル・本文・画像の更新（入力ルールは投稿の種類ごとの新規投稿と同じ）
 */
class UpdatePostRequest extends FormRequest
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
        $type = PostType::from($this->route('type'));

        $rules = match ($type) {
            PostType::Progress => (new StoreProgressReportRequest)->rules(),
            PostType::Goal => (new StoreUserGoalPostRequest)->rules(),
            PostType::Achievement => (new StoreAchievementReportRequest)->rules(),
            PostType::Knowledge => (new StoreUserKnowledgePostRequest)->rules(),
        };

        // 進捗報告は「タイトル・本文・画像のいずれか」が必要だが、すでに画像があれば、タイトル・本文を空にしてもよい
        if ($type === PostType::Progress && $this->targetPost()->image_path) {
            $rules = array_map(
                fn (array $fieldRules) => array_values(array_filter($fieldRules, fn ($rule) => ! str_starts_with($rule, 'required_without_all'))),
                $rules,
            );
        }

        return $rules;
    }

    public function messages(): array
    {
        return match (PostType::from($this->route('type'))) {
            PostType::Progress => (new StoreProgressReportRequest)->messages(),
            PostType::Goal => (new StoreUserGoalPostRequest)->messages(),
            PostType::Achievement => (new StoreAchievementReportRequest)->messages(),
            PostType::Knowledge => (new StoreUserKnowledgePostRequest)->messages(),
        };
    }

    /**
     * 更新対象の投稿（存在しなければ404）
     */
    private function targetPost(): Model
    {
        return PostType::from($this->route('type'))->modelClass()::findOrFail($this->route('id'));
    }
}
