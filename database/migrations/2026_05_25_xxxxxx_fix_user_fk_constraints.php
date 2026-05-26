<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'diagnosis_tasks',
            'writing_instructions',
            'writing_tasks',
            'viral_replications',
            'browser_profiles',
            'publish_tasks',
            'seo_sites',
            'seo_publish_tasks',
            'index_checks',
        ];

        foreach ($tables as $table) {
            DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$table}_user_id_foreign");
            DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$table}_user_id_foreign FOREIGN KEY (user_id) REFERENCES admins(id) ON DELETE CASCADE");
        }

        DB::statement('ALTER TABLE diagnosis_results DROP CONSTRAINT IF EXISTS diagnosis_results_task_id_foreign');
        DB::statement('ALTER TABLE diagnosis_results ADD CONSTRAINT diagnosis_results_task_id_foreign FOREIGN KEY (task_id) REFERENCES diagnosis_tasks(id) ON DELETE CASCADE');

        DB::statement('ALTER TABLE index_check_details DROP CONSTRAINT IF EXISTS index_check_details_check_id_foreign');
        DB::statement('ALTER TABLE index_check_details ADD CONSTRAINT index_check_details_check_id_foreign FOREIGN KEY (check_id) REFERENCES index_checks(id) ON DELETE CASCADE');
    }

    public function down(): void
    {
        $tables = [
            'diagnosis_tasks',
            'writing_instructions',
            'writing_tasks',
            'viral_replications',
            'browser_profiles',
            'publish_tasks',
            'seo_sites',
            'seo_publish_tasks',
            'index_checks',
        ];

        foreach ($tables as $table) {
            DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$table}_user_id_foreign");
        }

        DB::statement('ALTER TABLE diagnosis_results DROP CONSTRAINT IF EXISTS diagnosis_results_task_id_foreign');
        DB::statement('ALTER TABLE index_check_details DROP CONSTRAINT IF EXISTS index_check_details_check_id_foreign');
    }
};
