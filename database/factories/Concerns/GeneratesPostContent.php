<?php

namespace Database\Factories\Concerns;

use App\Models\GoalPost;
use DateTimeInterface;

/**
 * 4種類の投稿（目標・進捗・達成・学び）の Factory で共通の、本文と投稿日時の作り方
 */
trait GeneratesPostContent
{
    /**
     * 文例からランダムに選んだ文をつなげて本文にする
     *
     * @param  list<string>  $sentences
     */
    protected function bodyFrom(array $sentences, int $count = 2): string
    {
        return implode('', fake()->randomElements($sentences, $count));
    }

    /**
     * 投稿日時（ホームの新着順や検索結果の並びを確認できるよう、過去30日の間でばらけさせる）
     */
    protected function postedAt(): DateTimeInterface
    {
        return fake()->dateTimeBetween('-30 days', 'now');
    }

    /**
     * 目標に紐づく報告（進捗・達成）の投稿日時は、目標を投稿したあとにする
     *
     * @param  array<string, mixed>  $attributes
     */
    protected function postedAfterGoal(array $attributes): DateTimeInterface
    {
        return fake()->dateTimeBetween(GoalPost::findOrFail($attributes['goal_post_id'])->created_at, 'now');
    }
}
