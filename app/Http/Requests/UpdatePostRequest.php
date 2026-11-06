<?php

namespace App\Http\Requests;

use App\Enums\PostType;
use App\Models\AchievementReport;
use App\Models\GoalPost;
use App\Models\KnowledgePost;
use App\Models\ProgressReport;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 自分の投稿のタイトル・本文・画像の更新（入力ルールは、種類ごとの新規投稿と同じ）
 */
class UpdatePostRequest extends FormRequest
{
    private GoalPost|ProgressReport|AchievementReport|KnowledgePost|null $target = null;

    /**
     * 投稿した本人だけが更新できる（各投稿の Policy@update）
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->target());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = $this->storeRequest()->rules();

        // 進捗報告は「タイトル・本文・画像のいずれか」が必要だが、すでに画像があれば、タイトル・本文を空にしてもよい
        if ($this->postType() === PostType::Progress && $this->target()->image_path) {
            $rules = array_map(
                fn (array $fieldRules) => array_values(array_filter($fieldRules, fn ($rule) => ! str_starts_with($rule, 'required_without_all'))),
                $rules,
            );
        }

        return $rules;
    }

    public function messages(): array
    {
        return $this->storeRequest()->messages();
    }

    /**
     * 更新する投稿（存在しなければ404）
     * ※ FormRequest には入力値を取り出す post() があるので、別の名前にしている
     */
    public function target(): GoalPost|ProgressReport|AchievementReport|KnowledgePost
    {
        return $this->target ??= $this->postType()->modelClass()::findOrFail($this->route('id'));
    }

    private function postType(): PostType
    {
        $type = $this->route('type');

        return $type instanceof PostType ? $type : PostType::from($type);
    }

    /**
     * 種類ごとの新規投稿の FormRequest（入力ルールとメッセージを借りる）
     */
    private function storeRequest(): FormRequest
    {
        return match ($this->postType()) {
            PostType::Goal => new StoreUserGoalPostRequest,
            PostType::Progress => new StoreProgressReportRequest,
            PostType::Achievement => new StoreAchievementReportRequest,
            PostType::Knowledge => new StoreUserKnowledgePostRequest,
        };
    }
}
