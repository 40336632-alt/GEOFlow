<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('viral_replications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('source_url', 500);
            $table->string('source_title', 500)->nullable();
            $table->text('source_content')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('image_library_id')->nullable()->constrained('image_libraries')->nullOnDelete();
            $table->foreignId('instruction_id')->nullable()->constrained('writing_instructions')->nullOnDelete();
            $table->string('rewritten_title', 500)->nullable();
            $table->text('rewritten_content')->nullable();
            $table->string('status', 20)->default('pending');
            $table->integer('points_consumed')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('viral_replications');
    }
};
