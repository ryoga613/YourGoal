<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('post_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('knowledge_post_id')->nullable()->constrained('knowledge_posts')->cascadeOnDelete();
            $table->foreignId('goal_post_id')->nullable()->constrained('goal_posts')->cascadeOnDelete();
            $table->foreignId('progress_report_id')->nullable()->constrained('progress_reports')->cascadeOnDelete();
            $table->foreignId('achievement_report_id')->nullable()->constrained('achievement_reports')->cascadeOnDelete();
            $table->timestamps();

            // 同じ投稿には複数回いいねできない
            $table->unique(['user_id', 'knowledge_post_id']);
            $table->unique(['user_id', 'goal_post_id']);
            $table->unique(['user_id', 'progress_report_id']);
            $table->unique(['user_id', 'achievement_report_id']);
        });

        // "knowledge_post_id" or "goal_post_id" or "progress_report_id" or "achievement_report_id"
        // 上記のいずれかに合計１つ値が入っている必要があることはformRequestで確認
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_likes');
    }
};
