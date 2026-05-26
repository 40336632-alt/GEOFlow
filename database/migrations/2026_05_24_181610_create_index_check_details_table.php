<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('index_check_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('check_id')->constrained('index_checks')->cascadeOnDelete();
            $table->string('platform', 50);
            $table->boolean('is_indexed')->default(false);
            $table->text('answer_text')->nullable();
            $table->string('screenshot_url', 500)->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('checked_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('index_check_details');
    }
};
