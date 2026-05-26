<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('browser_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('platform', 50);
            $table->string('profile_id', 100);
            $table->string('profile_name', 200)->nullable();
            $table->string('account_name', 200)->nullable();
            $table->string('status', 20)->default('authorized');
            $table->integer('daily_limit')->default(3);
            $table->integer('today_published')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('browser_profiles');
    }
};
