<?php

namespace App\Console\Commands;

use App\Models\Admin;
use App\Models\Article;
use App\Models\BrowserProfile;
use App\Models\PublishTask;
use Illuminate\Console\Command;

class CreateTestPublishTask extends Command
{
    protected $signature = 'test:create-publish-task';
    protected $description = 'Create a test publish task with a long article';

    public function handle()
    {
        $user = Admin::first();
        if (!$user) {
            $this->error('No admin found');
            return;
        }

        $article = Article::find(11);
        if (!$article) {
            $this->error('Article 11 not found');
            return;
        }

        $profile = BrowserProfile::firstOrCreate(
            [
                'user_id' => $user->id,
                'profile_id' => '1b1815e660aa4304b02a22698a2695fa',
            ],
            [
                'user_id' => $user->id,
                'platform' => 'toutiao',
                'profile_id' => '1b1815e660aa4304b02a22698a2695fa',
                'profile_name' => '测试账号',
                'account_name' => '1',
                'status' => 'authorized',
            ]
        );

        $task = PublishTask::create([
            'user_id' => $user->id,
            'article_id' => $article->id,
            'platform' => 'toutiao',
            'publish_type' => 'personal',
            'profile_id' => $profile->id,
            'status' => 'pending',
        ]);

        $this->info("Created task ID: {$task->id}");
        $this->info("Article content len: " . strlen($article->content));
        $this->info("Plain text len: " . strlen(strip_tags($article->content)));
    }
}