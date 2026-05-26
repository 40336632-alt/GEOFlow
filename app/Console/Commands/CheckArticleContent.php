<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;

class CheckArticleContent extends Command
{
    protected $signature = 'test:check-article {id}';
    protected $description = 'Check article content format';

    public function handle()
    {
        $id = (int) $this->argument('id', 11);
        $article = Article::find($id);

        if (!$article) {
            $this->error("Article $id not found");
            return;
        }

        $content = $article->content;
        $this->info("Article ID: $article->id");
        $this->info("Title: $article->title");
        $this->info("Content length: " . strlen($content));
        $this->info("Has HTML tags: " . (strip_tags($content) !== $content ? 'YES' : 'NO'));
        $this->info("First 200 chars: " . substr($content, 0, 200));
        $this->info("Plain text length: " . strlen(strip_tags($content)));
    }
}