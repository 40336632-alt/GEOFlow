<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diagnosis_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('diagnosis_tasks')->cascadeOnDelete();
            $table->text('query');
            $table->string('platform', 50);
            $table->text('answer')->nullable();
            $table->boolean('brand_mentioned')->default(false);
            $table->integer('mention_position')->nullable();
            $table->string('screenshot_url', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diagnosis_results');
    }
};
