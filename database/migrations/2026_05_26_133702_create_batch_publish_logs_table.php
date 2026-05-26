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
        Schema::create('batch_publish_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('article_id')->nullable()->index();
            $table->string('account_name', 100)->nullable();
            $table->string('account_id', 100)->nullable();
            $table->string('platform', 50)->nullable();
            $table->unsignedBigInteger('task_id')->nullable()->index();
            $table->string('title', 500)->nullable();
            $table->string('status', 50)->nullable()->comment('submitted/verified/failed');
            $table->string('verify_status', 100)->nullable()->comment('审核中/已发布/等');
            $table->string('source', 50)->default('batch_script');
            $table->text('raw_data')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['account_name', 'created_at']);
            $table->index(['task_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_publish_logs');
    }
};
