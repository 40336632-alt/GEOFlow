<?php

namespace App\Http\Controllers\Api\V1\Internal;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublishBatchController extends Controller
{
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
            'review_status' => 'approved',
            'published_at' => now(),
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
}
