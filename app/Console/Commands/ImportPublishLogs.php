<?php

namespace App\Console\Commands;

use App\Models\BatchPublishLog;
use Illuminate\Console\Command;

class ImportPublishLogs extends Command
{
    protected $signature = 'publish:import-jsonl {file? : Path to published.jsonl}';

    protected $description = 'Import published.jsonl records into batch_publish_logs table';

    public function handle(): int
    {
        $path = $this->argument('file') ?: '/opt/toutiao-publisher/logs/published.jsonl';

        if (!file_exists($path)) {
            $this->error("File not found: {$path}");
            return self::FAILURE;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $this->info("Found " . count($lines) . " lines in {$path}");

        $imported = 0;
        $skipped = 0;

        foreach ($lines as $line) {
            $data = json_decode($line, true);
            if (!$data || !isset($data['status'])) {
                $skipped++;
                continue;
            }

            if ($data['status'] !== 'geoflow_batch_submitted') {
                $skipped++;
                continue;
            }

            $articleId = $data['articleId'] ?? null;
            if (!$articleId) {
                $skipped++;
                continue;
            }

            $exists = BatchPublishLog::query()
                ->where('article_id', $articleId)
                ->where('status', 'submitted')
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            BatchPublishLog::query()->create([
                'article_id' => $articleId,
                'account_name' => $data['accountName'] ?? null,
                'platform' => $data['platform'] ?? 'toutiao',
                'task_id' => $data['taskId'] ?? null,
                'title' => $data['title'] ?? null,
                'status' => 'submitted',
                'verify_status' => $data['verifyStatus'] ?? null,
                'source' => 'batch_script',
                'raw_data' => $data,
                'published_at' => isset($data['time']) ? date('Y-m-d H:i:s', strtotime($data['time'])) : now(),
            ]);

            $imported++;
        }

        $this->info("Imported: {$imported}, Skipped: {$skipped}");
        return self::SUCCESS;
    }
}
