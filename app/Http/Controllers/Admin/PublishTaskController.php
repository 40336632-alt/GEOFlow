<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\BatchPublishLog;
use App\Models\BrowserProfile;
use App\Models\PublishLog;
use App\Models\PublishTask;
use App\Services\ToutiaoPublisherService;
use App\Support\AdminWeb;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublishTaskController extends Controller
{
    protected ToutiaoPublisherService $publisherService;

    public function __construct(ToutiaoPublisherService $publisherService)
    {
        $this->publisherService = $publisherService;
    }

    public function dashboard()
    {
        $userId = auth('admin')->id();

        $profiles = BrowserProfile::where('user_id', $userId)
            ->orderBy('status')
            ->orderBy('platform')
            ->get()
            ->map(function ($p) {
                $isToday = $p->last_used_at && $p->last_used_at->isToday();
                $todayBatch = BatchPublishLog::query()
                    ->where('account_name', $p->account_name)
                    ->whereDate('created_at', today())
                    ->where('status', 'submitted')
                    ->count();
                $todayPublished = max($todayBatch, $isToday ? $p->today_published : 0);
                return [
                    'id' => $p->id,
                    'platform' => $p->platform,
                    'platform_label' => BrowserProfile::PLATFORMS[$p->platform] ?? $p->platform,
                    'account_name' => $p->account_name ?? '-',
                    'profile_name' => $p->profile_name ?? '-',
                    'daily_limit' => $p->daily_limit,
                    'today_published' => $todayPublished,
                    'remaining' => max(0, $p->daily_limit - $todayPublished),
                    'status' => $p->status,
                    'last_used_at' => $p->last_used_at,
                ];
            });

        $today = Carbon::today();
        $todayPublishes = PublishTask::where('user_id', $userId)
            ->whereDate('published_at', $today)
            ->where('status', 'completed')
            ->count();

        $pendingTasks = PublishTask::where('user_id', $userId)
            ->where('status', 'pending')
            ->count();

        $todayFailed = PublishTask::where('user_id', $userId)
            ->whereDate('created_at', $today)
            ->where('status', 'failed')
            ->count();

        $totalSlots = $profiles->sum('daily_limit');
        $usedSlots = $profiles->sum('today_published');
        $remainingSlots = $totalSlots - $usedSlots;

        $recentTasks = PublishTask::with(['article:id,title', 'profile:id,account_name'])
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $todayBatchPublishes = BatchPublishLog::query()
            ->whereDate('created_at', $today)
            ->where('status', 'submitted')
            ->count();

        $recentBatchLogs = BatchPublishLog::query()
            ->select('batch_publish_logs.*')
            ->selectRaw('(SELECT deleted_at IS NOT NULL FROM articles WHERE articles.id = batch_publish_logs.article_id) as article_deleted')
            ->selectRaw('(SELECT review_status FROM articles WHERE articles.id = batch_publish_logs.article_id) as article_review_status')
            ->orderByDesc('batch_publish_logs.id')
            ->limit(20)
            ->get();

        $weeklyBatchStats = collect(range(6, 0))->map(function ($daysAgo) {
            $date = Carbon::today()->subDays($daysAgo);
            $count = BatchPublishLog::query()
                ->whereDate('created_at', $date)
                ->where('status', 'submitted')
                ->count();
            return [
                'date' => $date->format('m-d'),
                'label' => $daysAgo === 0 ? '今天' : ($daysAgo === 1 ? '昨天' : $date->format('m-d')),
                'count' => $count,
            ];
        });

        $weeklyStats = collect(range(6, 0))->map(function ($daysAgo) use ($userId) {
            $date = Carbon::today()->subDays($daysAgo);
            $count = PublishTask::where('user_id', $userId)
                ->whereDate('published_at', $date)
                ->where('status', 'completed')
                ->count();
            return [
                'date' => $date->format('m-d'),
                'label' => $daysAgo === 0 ? '今天' : ($daysAgo === 1 ? '昨天' : $date->format('m-d')),
                'count' => $count,
            ];
        });

        return view('admin.publish.dashboard', [
            'pageTitle' => '发布看板',
            'activeMenu' => 'distribution',
            'adminSiteName' => AdminWeb::siteName(),
            'profiles' => $profiles,
            'todayPublishes' => $todayPublishes,
            'todayBatchPublishes' => $todayBatchPublishes,
            'pendingTasks' => $pendingTasks,
            'todayFailed' => $todayFailed,
            'totalSlots' => $totalSlots,
            'usedSlots' => $usedSlots,
            'remainingSlots' => $remainingSlots,
            'recentTasks' => $recentTasks,
            'recentBatchLogs' => $recentBatchLogs,
            'weeklyStats' => $weeklyStats,
            'weeklyBatchStats' => $weeklyBatchStats,
        ]);
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
