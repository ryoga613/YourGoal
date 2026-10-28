<?php

namespace App\Enums;

enum GoalPostStatus: string
{
    case Considering = 'considering';
    case InProgress = 'in_progress';
    case Achieved = 'achieved';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Considering => '検討中',
            self::InProgress => '挑戦中',
            self::Achieved => '達成済み',
            self::Cancelled => '中止',
        };
    }
}
