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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('knowledge_post_id')->nullable()->constrained('knowledge_posts')->cascadeOnDelete();
            $table->foreignId('goal_post_id')->nullable()->constrained('goal_posts')->cascadeOnDelete();
            $table->foreignId('progress_report_id')->nullable()->constrained('progress_reports')->cascadeOnDelete();
            $table->foreignId('achievement_report_id')->nullable()->constrained('achievement_reports')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('comments')->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
        });
        // "knowledge_post_id" or "goal_post_id" or "progress_report_id" or "achievement_report_id"
        // 上記のいずれかに値が入っている必要があることをformRequestで確認

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
