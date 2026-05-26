<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\BrowserProfile;
use App\Models\PublishLog;
use App\Models\PublishTask;
use App\Services\ToutiaoPublisherService;
use Illuminate\Http\Request;

class PublishTaskController extends Controller
{
    protected ToutiaoPublisherService $publisherService;

    public function __construct(ToutiaoPublisherService $publisherService)
    {
        $this->publisherService = $publisherService;
    }

    public function index(Request $request)
    {
        $query = PublishTask::with(['article', 'profile'])->where('user_id', auth('admin')->id());

        if ($type = $request->get('type')) {
            $query->where('publish_type', $type);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $tasks = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.publish.tasks.index', [
            'tasks' => $tasks,
            'types' => PublishTask::TYPES,
            'currentType' => $request->get('type'),
            'currentStatus' => $request->get('status'),
        ]);
    }

    public function create()
    {
        $userId = auth('admin')->id();

        return view('admin.publish.tasks.create', [
            'articles' => Article::whereIn('status', ['published', 'draft'])->orderBy('created_at', 'desc')->limit(100)->get(),
            'profiles' => BrowserProfile::where('user_id', $userId)
                ->where('status', 'authorized')
                ->orderBy('platform')
                ->get(),
            'types' => PublishTask::TYPES,
            'platforms' => BrowserProfile::PLATFORMS,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'article_id' => 'required|exists:articles,id',
            'publish_type' => 'required|string|in:personal,kol,webmedia',
            'platform' => 'required|string',
            'profile_id' => 'nullable|exists:browser_profiles,id',
            'media_id' => 'nullable|integer',
        ]);

        $userId = auth('admin')->id();

        if (!empty($validated['profile_id'])) {
            $profile = BrowserProfile::whereKey($validated['profile_id'])
                ->where('user_id', $userId)
                ->first();

            if (!$profile) {
                return back()->withErrors('浏览器配置不存在');
            }
        }

        try {
            PublishTask::create([
                'user_id' => $userId,
                'article_id' => $validated['article_id'],
                'publish_type' => $validated['publish_type'],
                'platform' => $validated['platform'],
                'profile_id' => $validated['profile_id'] ?? null,
                'media_id' => $validated['media_id'] ?? null,
                'sync_status' => 'pending',
            ]);

            return redirect()->route('admin.publish-tasks.index')
                ->with('message', '发布任务已创建');
        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
        }
    }

    public function show(PublishTask $publishTask)
    {
        $this->authorizeTask($publishTask);
        $publishTask->load(['article', 'profile', 'logs']);

        return view('admin.publish.tasks.show', [
            'task' => $publishTask,
            'types' => PublishTask::TYPES,
            'platforms' => BrowserProfile::PLATFORMS,
        ]);
    }

    public function execute(PublishTask $publishTask)
    {
        $this->authorizeTask($publishTask);

        if (!$publishTask->isPending()) {
            return back()->withErrors('只能执行待处理任务');
        }

        $publishTask->loadMissing(['article', 'profile']);
        $publishTask->update(['status' => 'running', 'sync_status' => 'handed_off']);
        $this->writeLog($publishTask, 'start', 'running', '开始执行发布任务');

        try {
            $profile = $publishTask->profile;
            $article = $publishTask->article;

            if (!$profile) {
                throw new \RuntimeException('未找到浏览器配置');
            }

            if (!$article) {
                throw new \RuntimeException('未找到文章');
            }

            $this->writeLog($publishTask, 'handoff_to_publisher', 'running', '交给 ToutiaoPublisher 执行');

            $result = $this->publisherService->publish($publishTask, $article, $profile);

            if (empty($result['success'])) {
                $publishTask->update([
                    'status' => 'failed',
                    'sync_status' => 'failed',
                    'error_message' => $result['error'] ?? '发布失败',
                    'sync_error_message' => $result['error'] ?? '发布失败',
                ]);

                $this->writeLog($publishTask, 'error', 'failed', $result['error'] ?? '发布失败');

                return back()->with('message', '发布失败');
            }

            $isAlreadySubmitted = ($result['status'] ?? null) === 'already_submitted';

            $publishTask->update([
                'status' => 'completed',
                'sync_status' => 'published',
                'remote_article_id' => $result['remote_article_id'] ?? null,
                'synced_at' => now(),
                'published_at' => now(),
                'published_url' => $result['published_url'] ?? null,
                'error_message' => null,
                'sync_error_message' => null,
            ]);

            if (!$isAlreadySubmitted) {
                $profile->increment('today_published');
            }
            $profile->update(['last_used_at' => now()]);

            $this->writeLog($publishTask, 'publish_started', 'running', $result['message'] ?? '外部发布器已执行');
            $this->writeLog($publishTask, 'complete', 'completed', $isAlreadySubmitted ? '文章已存在，按成功处理' : '发布成功');
        } catch (\Exception $e) {
            $publishTask->update([
                'status' => 'failed',
                'sync_status' => 'failed',
                'error_message' => $e->getMessage(),
                'sync_error_message' => $e->getMessage(),
            ]);

            $this->writeLog($publishTask, 'error', 'failed', $e->getMessage());
        }

        return back()->with('message', $publishTask->fresh()->status === 'completed' ? '发布成功' : '发布失败');
    }

    public function destroy(PublishTask $publishTask)
    {
        $this->authorizeTask($publishTask);
        $publishTask->delete();

        return back()->with('message', '发布任务已删除');
    }

    protected function authorizeTask(PublishTask $publishTask): void
    {
        if ($publishTask->user_id !== auth('admin')->id()) {
            abort(403);
        }
    }

    protected function writeLog(PublishTask $task, string $action, string $status, string $detail): void
    {
        PublishLog::create([
            'task_id' => $task->id,
            'action' => $action,
            'status' => $status,
            'detail' => $detail,
            'created_at' => now(),
        ]);
    }
}
