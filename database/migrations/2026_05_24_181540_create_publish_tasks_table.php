<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publish_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
            $table->foreignId('profile_id')->nullable()->constrained('browser_profiles')->nullOnDelete();
            $table->bigInteger('media_id')->nullable();
            $table->string('platform', 50);
            $table->string('publish_type', 20);  // personal/kol/webmedia
            $table->string('status', 20)->default('pending');
            $table->text('error_message')->nullable();
            $table->string('published_url', 500)->nullable();
            $table->timestamp('published_at')->nullable();
            $table->integer('points_consumed')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publish_tasks');
    }
};
