<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('publish_tasks', function (Blueprint $table) {
            $table->string('sync_status', 20)->default('pending')->after('status');
            $table->string('remote_article_id', 100)->nullable()->after('sync_status');
            $table->text('sync_error_message')->nullable()->after('error_message');
            $table->timestamp('synced_at')->nullable()->after('published_at');
        });
    }

    public function down(): void
    {
        Schema::table('publish_tasks', function (Blueprint $table) {
            $table->dropColumn([
                'sync_status',
                'remote_article_id',
                'sync_error_message',
                'synced_at',
            ]);
        });
    }
};
