<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixAllUserForeignKeys extends Command
{
    protected $signature = 'fix:user-fk';
    protected $description = 'Fix all user_id FK constraints to point to admins table';

    public function handle()
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
            $constraintName = "{$table}_user_id_foreign";
            DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$constraintName}");
            DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$constraintName} FOREIGN KEY (user_id) REFERENCES admins(id) ON DELETE CASCADE");
            $this->info("Fixed: {$table}");
        }

        // Also fix child tables
        DB::statement('ALTER TABLE diagnosis_results DROP CONSTRAINT IF EXISTS diagnosis_results_task_id_foreign');
        DB::statement('ALTER TABLE diagnosis_results ADD CONSTRAINT diagnosis_results_task_id_foreign FOREIGN KEY (task_id) REFERENCES diagnosis_tasks(id) ON DELETE CASCADE');
        $this->info("Fixed: diagnosis_results");

        DB::statement('ALTER TABLE index_check_details DROP CONSTRAINT IF EXISTS index_check_details_check_id_foreign');
        DB::statement('ALTER TABLE index_check_details ADD CONSTRAINT index_check_details_check_id_foreign FOREIGN KEY (check_id) REFERENCES index_checks(id) ON DELETE CASCADE');
        $this->info("Fixed: index_check_details");

        $this->info("All done!");
    }
}