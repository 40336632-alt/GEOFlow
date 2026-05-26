<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('geo_optimization_rules')) {
            return;
        }

        Schema::create('geo_optimization_rules', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100);
            $table->string('dataset', 30)->default('default')->index();
            $table->json('rules');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('geo_optimization_rules');
    }
};
