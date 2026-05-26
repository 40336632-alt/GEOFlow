<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tasks')) {
            return;
        }

        Schema::table('tasks', function (Blueprint $table): void {
            if (! Schema::hasColumn('tasks', 'enable_geo_optimization')) {
                $table->boolean('enable_geo_optimization')->default(false)->after('publish_scope');
            }
            if (! Schema::hasColumn('tasks', 'geo_dataset')) {
                $table->string('geo_dataset', 30)->default('default')->after('enable_geo_optimization');
            }
            if (! Schema::hasColumn('tasks', 'geo_engine_llm')) {
                $table->string('geo_engine_llm', 30)->default('gemini')->after('geo_dataset');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('tasks')) {
            return;
        }

        $columns = ['enable_geo_optimization', 'geo_dataset', 'geo_engine_llm'];
        Schema::table('tasks', function (Blueprint $table) use ($columns): void {
            foreach ($columns as $col) {
                if (Schema::hasColumn('tasks', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
