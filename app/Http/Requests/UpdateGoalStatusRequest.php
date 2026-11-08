<?php

namespace App\Http\Requests;

use App\Enums\GoalPostStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 目標ステータスの変更
 */
class UpdateGoalStatusRequest extends FormRequest
{
    /**
     * 目標を宣言した本人だけが、達成済みでない目標のステータスを変更できる
     * （達成済みは達成報告によってのみ切り替わり、達成報告を削除したときに挑戦中へ戻る）
     */
    public function authorize(): bool
    {

        return $this->user()->id === $this->goal->user_id && $this->goal->status !== GoalPostStatus::Achieved;
    }

    /**
     * 達成済みへは、ここでは変更できない（達成報告から行う）
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(GoalPostStatus::class)->except(GoalPostStatus::Achieved)],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'ステータスを選択してください。',
            'status.enum' => '選択したステータスには変更できません。',
        ];
    }
}
