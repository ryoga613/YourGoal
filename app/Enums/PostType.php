<?php

namespace App\Enums;

/**
 * 投稿の種類（/posts/{type}/{id} の type の値）
 */
enum PostType: string
{
    case Goal = 'goal';
    case Progress = 'progress';
    case Achievement = 'achievement';
    case Knowledge = 'knowledge';

    public function label(): string
    {
        return match ($this) {
            self::Goal => '目標宣言',
            self::Progress => '進捗報告',
            self::Achievement => '達成報告',
            self::Knowledge => '学び共有',
        };
    }

    /**
     * コメント・いいねから投稿へ紐づく外部キー名
     */
    public function foreignKey(): string
    {
        return match ($this) {
            self::Goal => 'goal_post_id',
            self::Progress => 'progress_report_id',
            self::Achievement => 'achievement_report_id',
            self::Knowledge => 'knowledge_post_id',
        };
    }

    /**
     * コメント・いいねから投稿へのリレーション名
     */
    public function relation(): string
    {
        return match ($this) {
            self::Goal => 'goal_post',
            self::Progress => 'progress_report',
            self::Achievement => 'achievement_report',
            self::Knowledge => 'knowledge_post',
        };
    }

    /**
     * 画面のタグ表示に使うCSSクラス
     */
    public function tagClass(): string
    {
        return $this->value;
    }
}
