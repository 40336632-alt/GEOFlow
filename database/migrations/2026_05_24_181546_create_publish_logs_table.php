<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publish_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('publish_tasks')->cascadeOnDelete();
            $table->string('action', 50);
            $table->string('status', 20)->nullable();
            $table->text('detail')->nullable();
            $table->string('screenshot_url', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publish_logs');
    }
};
