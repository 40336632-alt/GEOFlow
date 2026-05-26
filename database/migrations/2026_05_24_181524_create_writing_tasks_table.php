<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('writing_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('admin_users')->cascadeOnDelete();
            $table->string('name', 200);
            $table->foreignId('keyword_library_id')->nullable()->constrained('keyword_libraries')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('image_library_id')->nullable()->constrained('image_libraries')->nullOnDelete();
            $table->integer('image_count')->default(2);
            $table->foreignId('knowledge_base_id')->nullable()->constrained('knowledge_bases')->nullOnDelete();
            $table->foreignId('instruction_id')->nullable()->constrained('writing_instructions')->nullOnDelete();
            $table->integer('max_articles')->default(1);
            $table->integer('created_count')->default(0);
            $table->string('status', 20)->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('last_written_at')->nullable();
            $table->integer('points_consumed')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('writing_tasks');
    }
};
