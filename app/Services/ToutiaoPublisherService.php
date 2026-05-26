<?php

namespace App\Services;

use App\Models\Article;
use App\Models\BrowserProfile;
use App\Models\PublishTask;
use App\Support\GeoFlow\ImageUrlNormalizer;
use App\Support\Site\ArticleHtmlPresenter;
use Illuminate\Support\Facades\Http;

class ToutiaoPublisherService
{
    protected string $host;
    protected int $port;

    public function __construct()
    {
        $this->host = config('services.toutiao_publisher.http_host', '127.0.0.1');
        $this->port = (int) config('services.toutiao_publisher.http_port', 18432);
    }

    public function publish(PublishTask $task, Article $article, BrowserProfile $profile): array
    {
        $url = "http://{$this->host}:{$this->port}/publish-single";

        // 使用 ArticleHtmlPresenter 将 Markdown 转 HTML，并修复图片路径为公开 URL
        $htmlContent = ArticleHtmlPresenter::markdownToHtml($article->content);

        // 封面图路径转成可公开访问的完整 URL（Node.js downloadImage 需要完整 URL）
        $coverPath = $this->resolveCoverPath($article);
        if ($coverPath) {
            $coverPath = ImageUrlNormalizer::toPublicUrl($coverPath);
            if ($coverPath && !str_starts_with($coverPath, 'http://') && !str_starts_with($coverPath, 'https://')) {
                $baseUrl = rtrim(config('app.url'), '/');
                $coverPath = $baseUrl . $coverPath;
            }
        }

        $payload = [
            'platform' => $task->platform,
            'account_name' => $profile->account_name ?: $profile->profile_name,
            'bit_browser_id' => $profile->profile_id,
            'title' => $article->title,
            'source_title' => $article->title,
            'content' => $htmlContent,
            'cover_path' => $coverPath,
            'source_article_id' => $article->id,
            'source_task_id' => $task->id,
            'author' => $profile->account_name ?: $profile->profile_name,
            'skip_pre_publish_check' => env('TOUTIAO_SKIP_PRE_PUBLISH_CHECK', false),
            'content_is_html' => true,
        ];

        try {
            $response = Http::timeout(300)->post($url, $payload);
            $decoded = $response->json();
        } catch (\Exception $e) {
            return [
                'success' => false,
                'status' => 'failed',
                'error' => '无法连接到发布服务：' . $e->getMessage(),
            ];
        }

        if (!is_array($decoded)) {
            return [
                'success' => false,
                'status' => 'failed',
                'error' => '发布服务返回无效响应',
            ];
        }

        if (empty($decoded['success'])) {
            return [
                'success' => false,
                'status' => $decoded['status'] ?? 'failed',
                'error' => $decoded['error'] ?? '发布失败',
                'published_url' => $decoded['published_url'] ?? null,
                'remote_article_id' => $decoded['remote_article_id'] ?? null,
                'message' => $decoded['message'] ?? null,
                'title' => $decoded['title'] ?? $article->title,
            ];
        }

        return [
            'success' => true,
            'status' => $decoded['status'] ?? 'submitted',
            'title' => $decoded['title'] ?? $article->title,
            'published_url' => $decoded['published_url'] ?? null,
            'remote_article_id' => $decoded['remote_article_id'] ?? null,
            'message' => $decoded['message'] ?? null,
            'error' => null,
        ];
    }

    protected function resolveCoverPath(Article $article): ?string
    {
        $articleImage = $article->articleImages()->with('image')->orderBy('position')->first();

        return $articleImage?->image?->file_path;
    }
}
