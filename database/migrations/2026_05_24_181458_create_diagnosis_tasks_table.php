<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diagnosis_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('main_keyword', 100);
            $table->string('brand_name', 200)->nullable();
            $table->json('column_a')->nullable();     // 前缀/地域
            $table->json('column_b')->nullable();     // 形容词
            $table->string('column_c', 100);           // 主词(必填)
            $table->string('column_d', 100);           // 目标词(必填)
            $table->json('column_e')->nullable();     // 推荐词
            $table->json('platforms');                  // 检测平台
            $table->string('status', 20)->default('pending');
            $table->integer('total_queries')->default(0);
            $table->integer('brand_mentioned')->default(0);
            $table->decimal('visibility_score', 5, 2)->nullable();
            $table->integer('points_consumed')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diagnosis_tasks');
    }
};
