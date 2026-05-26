<?php

namespace App\Jobs;

use App\Models\Article;
use App\Models\GeoOptimizationLog;
use App\Services\GeoFlow\AutoGeoIntegrationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessGeoOptimizationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly int $articleId,
        private readonly string $dataset = 'medical',
        private readonly string $engineLlm = 'openai',
    ) {}

    public function handle(AutoGeoIntegrationService $autoGeoService): void
    {
        $article = Article::query()->with('task')->find($this->articleId);
        if (!$article || $article->status !== 'draft') {
            return;
        }

        $startTime = microtime(true);

        $result = $autoGeoService->optimizeContent(
            content: $article->content,
            dataset: $this->dataset,
            engineLlm: $this->engineLlm,
            evaluate: true
        );

        $duration = round(microtime(true) - $startTime, 2);

        GeoOptimizationLog::query()->create([
            'task_id' => $article->task_id,
            'article_id' => $article->id,
            'dataset' => $this->dataset,
            'engine_llm' => $this->engineLlm,
            'original_content' => mb_substr($article->content, 0, 10000),
            'optimized_content' => mb_substr($result['content'] ?? $article->content, 0, 10000),
            'geo_scores' => $result['scores'],
            'status' => ($result['success'] ?? false) ? 'success' : 'failed',
            'error_message' => $result['error'] ?? null,
            'duration_seconds' => $duration,
        ]);

        if ($result['success']) {
            $article->update([
                'content' => $result['content'],
                'geo_scores' => $result['scores'],
                'geo_original_content' => $article->getRawOriginal('content'),
                'geo_optimized_at' => now(),
            ]);
        }
    }
}
