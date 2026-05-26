<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('articles')) {
            return;
        }

        Schema::table('articles', function (Blueprint $table): void {
            if (! Schema::hasColumn('articles', 'geo_scores')) {
                $table->json('geo_scores')->nullable()->after('is_featured');
            }
            if (! Schema::hasColumn('articles', 'geo_original_content')) {
                $table->text('geo_original_content')->nullable()->after('geo_scores');
            }
            if (! Schema::hasColumn('articles', 'geo_optimized_at')) {
                $table->timestamp('geo_optimized_at')->nullable()->after('geo_original_content');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('articles')) {
            return;
        }

        $columns = ['geo_scores', 'geo_original_content', 'geo_optimized_at'];
        Schema::table('articles', function (Blueprint $table) use ($columns): void {
            foreach ($columns as $col) {
                if (Schema::hasColumn('articles', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
