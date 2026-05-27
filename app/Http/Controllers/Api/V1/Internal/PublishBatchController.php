<?php

namespace App\Http\Controllers\Api\V1\Internal;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\BatchPublishLog;
use App\Models\BrowserProfile;
use App\Models\Task;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublishBatchController extends Controller
{
    public function softDelete(Request $request): JsonResponse
    {
        $articleId = $request->integer('article_id');
        if ($articleId < 1) {
            return ApiResponse::error('invalid_param', 'article_id is required', Str::uuid()->toString(), 400);
        }

        $article = Article::query()->whereKey($articleId)->first();
        if (!$article) {
            return ApiResponse::error('not_found', 'Article not found', Str::uuid()->toString(), 404);
        }

        $article->delete();

        return ApiResponse::success([
            'article_id' => $article->id,
            'deleted' => true,
        ], Str::uuid()->toString());
    }

    public function markLogVerified(Request $request): JsonResponse
    {
        $articleId = $request->integer('article_id');
        $accountName = $request->input('account_name');
        $verifiedStatus = $request->input('verified_status', 'verified');

        if ($articleId < 1 || !$accountName) {
            return ApiResponse::error('invalid_param', 'article_id and account_name are required', Str::uuid()->toString(), 400);
        }

        BatchPublishLog::query()
            ->where('article_id', $articleId)
            ->where('account_name', $accountName)
            ->where('status', 'submitted')
            ->update(['status' => $verifiedStatus]);

        return ApiResponse::success(['updated' => true], Str::uuid()->toString());
    }

    public function forceDeleteArticle(Request $request): JsonResponse
    {
        $articleId = $request->integer('article_id');
        if ($articleId < 1) {
            return ApiResponse::error('invalid_param', 'article_id is required', Str::uuid()->toString(), 400);
        }

        $article = Article::query()->whereKey($articleId)->first();
        if (!$article) {
            return ApiResponse::error('not_found', 'Article not found', Str::uuid()->toString(), 404);
        }

        $article->forceDelete();

        return ApiResponse::success([
            'article_id' => $article->id,
            'force_deleted' => true,
        ], Str::uuid()->toString());
    }

    public function articlesByTask(Request $request): JsonResponse
    {
        $taskId = $request->integer('task_id');
        $limit = min($request->integer('limit', 3), 100);

        if ($taskId < 1) {
            return ApiResponse::error('invalid_param', 'task_id is required', Str::uuid()->toString(), 400);
        }

        $articles = Article::query()
            ->where('task_id', $taskId)
            ->where('status', 'draft')
            ->where('review_status', 'pending')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->map(function ($article) {
                $coverPath = null;
                $firstImage = $article->articleImages()
                    ->with('image')
                    ->orderBy('position')
                    ->orderBy('id')
                    ->first();
                if ($firstImage && $firstImage->image) {
                    $coverPath = $firstImage->image->file_path;
                }

                return [
                    'id' => $article->id,
                    'title' => $article->title,
                    'content' => $article->content,
                    'cover_path' => $coverPath,
                    'task_id' => $article->task_id,
                    'author_name' => $article->author?->name ?? '',
                ];
            });

        return ApiResponse::success([
            'articles' => $articles,
        ], Str::uuid()->toString());
    }

    public function supplement(Request $request): JsonResponse
    {
        $articleIds = collect($request->input('article_ids', []))
            ->map(static fn ($id): int => (int) $id)
            ->filter(static fn (int $id): bool => $id > 0)
            ->values()
            ->all();

        if (empty($articleIds)) {
            return ApiResponse::error('invalid_param', 'article_ids is required', Str::uuid()->toString(), 400);
        }

        $articles = Article::query()
            ->whereIn('id', $articleIds)
            ->whereNull('deleted_at')
            ->get()
            ->map(function ($article) {
                $coverPath = null;
                $firstImage = $article->articleImages()
                    ->with('image')
                    ->orderBy('position')
                    ->orderBy('id')
                    ->first();
                if ($firstImage && $firstImage->image) {
                    $coverPath = $firstImage->image->file_path;
                }

                return [
                    'id' => $article->id,
                    'title' => $article->title,
                    'content' => $article->content,
                    'cover_path' => $coverPath,
                    'task_id' => $article->task_id,
                    'author_name' => $article->author?->name ?? '',
                ];
            });

        return ApiResponse::success([
            'articles' => $articles,
        ], Str::uuid()->toString());
    }

    public function nextArticle(Request $request): JsonResponse
    {
        $taskId = $request->integer('task_id');
        if ($taskId < 1) {
            return ApiResponse::error('invalid_param', 'task_id is required', Str::uuid()->toString(), 400);
        }

        $article = Article::query()
            ->where('task_id', $taskId)
            ->where('status', 'draft')
            ->where('review_status', 'pending')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->first();

        if (!$article) {
            return ApiResponse::success([
                'article' => null,
                'has_more' => false,
            ], Str::uuid()->toString());
        }

        $coverPath = null;
        $images = [];
        $firstImage = $article->articleImages()
            ->with('image')
            ->orderBy('position')
            ->orderBy('id')
            ->first();
        if ($firstImage && $firstImage->image) {
            $coverPath = $firstImage->image->file_path;
            $images[] = [
                'file_path' => $firstImage->image->file_path,
                'filename' => $firstImage->image->filename,
            ];
        }

        $allImages = $article->articleImages()
            ->with('image')
            ->orderBy('position')
            ->orderBy('id')
            ->get();
        foreach ($allImages as $ai) {
            if ($ai->image && $ai->image->file_path !== $coverPath) {
                $images[] = [
                    'file_path' => $ai->image->file_path,
                    'filename' => $ai->image->filename,
                ];
            }
        }

        return ApiResponse::success([
            'article' => [
                'id' => $article->id,
                'title' => $article->title,
                'content' => $article->content,
                'cover_path' => $coverPath,
                'images' => $images,
                'task_id' => $article->task_id,
                'author_name' => $article->author?->name ?? '',
            ],
            'has_more' => Article::query()
                ->where('task_id', $taskId)
                ->where('status', 'draft')
                ->where('review_status', 'pending')
                ->whereNull('deleted_at')
                ->where('id', '>', $article->id)
                ->exists(),
        ], Str::uuid()->toString());
    }

    public function markPublished(Request $request): JsonResponse
    {
        $articleId = $request->integer('article_id');
        if ($articleId < 1) {
            return ApiResponse::error('invalid_param', 'article_id is required', Str::uuid()->toString(), 400);
        }

        $article = Article::query()->whereKey($articleId)->first();
        if (!$article) {
            return ApiResponse::error('not_found', 'Article not found', Str::uuid()->toString(), 404);
        }

        $article->update([
            'status' => 'published',
            'review_status' => 'pending_review',
            'published_at' => now(),
            'deleted_at' => now(),
        ]);

        return ApiResponse::success([
            'article_id' => $article->id,
            'status' => 'published',
        ], Str::uuid()->toString());
    }

    public function markFailed(Request $request): JsonResponse
    {
        $articleId = $request->integer('article_id');
        if ($articleId < 1) {
            return ApiResponse::error('invalid_param', 'article_id is required', Str::uuid()->toString(), 400);
        }

        $article = Article::query()->whereKey($articleId)->first();
        if (!$article) {
            return ApiResponse::error('not_found', 'Article not found', Str::uuid()->toString(), 404);
        }

        $article->update([
            'review_status' => 'rejected',
        ]);

        return ApiResponse::success([
            'article_id' => $article->id,
            'status' => 'failed',
        ], Str::uuid()->toString());
    }

    public function logPublish(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'article_id' => 'nullable|integer|exists:articles,id',
            'account_name' => 'nullable|string|max:100',
            'account_id' => 'nullable|string|max:100',
            'platform' => 'nullable|string|max:50',
            'task_id' => 'nullable|integer|exists:tasks,id',
            'title' => 'nullable|string|max:500',
            'status' => 'nullable|string|max:50',
            'verify_status' => 'nullable|string|max:100',
            'published_at' => 'nullable|date',
        ]);

        $log = BatchPublishLog::query()->create([
            'article_id' => $payload['article_id'] ?? null,
            'account_name' => $payload['account_name'] ?? null,
            'account_id' => $payload['account_id'] ?? null,
            'platform' => $payload['platform'] ?? null,
            'task_id' => $payload['task_id'] ?? null,
            'title' => $payload['title'] ?? null,
            'status' => $payload['status'] ?? 'submitted',
            'verify_status' => $payload['verify_status'] ?? null,
            'source' => 'batch_script',
            'published_at' => $payload['published_at'] ?? now(),
        ]);

        return ApiResponse::success([
            'log_id' => $log->id,
        ], Str::uuid()->toString());
    }

    public function accounts(Request $request): JsonResponse
    {
        $platform = $request->input('platform');

        $profiles = BrowserProfile::query()
            ->when($platform, fn ($q) => $q->where('platform', $platform))
            ->where('status', 'authorized')
            ->orderBy('account_name')
            ->get();

        $tasks = Task::query()
            ->where('status', 'active')
            ->orderBy('id')
            ->get(['id', 'name']);

        $result = [];
        foreach ($profiles as $profile) {
            $pendingCounts = [];
            foreach ($tasks as $task) {
                $count = Article::query()
                    ->where('task_id', $task->id)
                    ->where('status', 'draft')
                    ->where('review_status', 'pending')
                    ->whereNull('deleted_at')
                    ->count();
                if ($count > 0) {
                    $pendingCounts[] = [
                        'task_id' => $task->id,
                        'task_name' => $task->name,
                        'pending' => $count,
                    ];
                }
            }

            $accountName = $profile->account_name ?? $profile->profile_name ?? '未命名';
            $todayBatch = BatchPublishLog::query()
                ->where('account_name', $accountName)
                ->whereDate('created_at', today())
                ->where('status', 'submitted')
                ->count();
            $todayPublished = max($todayBatch, $profile->last_used_at && $profile->last_used_at->isToday() ? $profile->today_published : 0);
            $remaining = $profile->daily_limit - $todayPublished;

            $result[] = [
                'id' => $profile->id,
                'profile_id' => $profile->profile_id,
                'account_name' => $accountName,
                'daily_limit' => $profile->daily_limit,
                'today_published' => $todayPublished,
                'remaining' => max(0, $remaining),
                'tasks' => $pendingCounts,
            ];
        }

        return ApiResponse::success([
            'accounts' => $result,
        ], Str::uuid()->toString());
    }

    public function batchLogs(Request $request): JsonResponse
    {
        $query = BatchPublishLog::query()->orderByDesc('id');

        if ($taskId = $request->integer('task_id')) {
            $query->where('task_id', $taskId);
        }

        if ($account = $request->input('account_name')) {
            $query->where('account_name', $account);
        }

        $limit = min($request->integer('limit', 50), 200);
        $logs = $query->limit($limit)->get();

        return ApiResponse::success([
            'logs' => $logs,
            'today_publishes' => BatchPublishLog::query()
                ->whereDate('created_at', today())
                ->where('status', 'submitted')
                ->count(),
        ], Str::uuid()->toString());
    }
}
