<?php

namespace App\Console\Commands;

use App\Models\BrowserProfile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetDailyPublishLimit extends Command
{
    protected $signature = 'profiles:reset-daily-limit';

    protected $description = 'Reset today_published counter for all browser profiles at midnight';

    public function handle(): int
    {
        $count = BrowserProfile::query()
            ->where('today_published', '>', 0)
            ->where(function ($q) {
                $q->whereNull('last_used_at')
                  ->orWhereDate('last_used_at', '<', today());
            })
            ->update(['today_published' => 0]);

        $this->info("Reset today_published for {$count} profiles");

        return self::SUCCESS;
    }
}
