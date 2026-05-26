<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('geo_optimization_logs')) {
            return;
        }

        Schema::create('geo_optimization_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('task_id')->nullable()->constrained('tasks')->nullOnDelete();
            $table->foreignId('article_id')->nullable()->constrained('articles')->nullOnDelete();
            $table->string('dataset', 30)->default('default');
            $table->string('engine_llm', 30)->default('gemini');
            $table->text('original_content');
            $table->text('optimized_content');
            $table->json('geo_scores')->nullable();
            $table->string('status', 20)->default('success')->index();
            $table->text('error_message')->nullable();
            $table->float('duration_seconds', 8, 2)->nullable();
            $table->timestamps();

            $table->index('task_id');
            $table->index('article_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('geo_optimization_logs');
    }
};
