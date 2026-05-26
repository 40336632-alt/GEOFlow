<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\GeoOptimizationLog;
use App\Models\GeoOptimizationRule;
use App\Services\GeoFlow\AutoGeoIntegrationService;
use App\Support\AdminWeb;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class GeoOptimizationController extends Controller
{
    public function __construct(
        private readonly AutoGeoIntegrationService $autoGeoService,
    ) {}

    /**
     * GEO优化日志列表。
     */
    public function index(Request $request): View
    {
        $query = GeoOptimizationLog::query()
            ->with(['task:id,name', 'article:id,title'])
            ->orderByDesc('id');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($dataset = $request->input('dataset')) {
            $query->where('dataset', $dataset);
        }

        $logs = $query->paginate(20)->withQueryString();

        $logStats = [
            'total' => GeoOptimizationLog::query()->count(),
            'success' => GeoOptimizationLog::query()->where('status', 'success')->count(),
            'failed' => GeoOptimizationLog::query()->whereIn('status', ['failed', 'error'])->count(),
            'avg_duration' => round((float) GeoOptimizationLog::query()->where('status', 'success')->avg('duration_seconds'), 2),
        ];

        $queuePending = DB::table('jobs')->where('queue', 'geoflow')->count();

        $articleStats = [
            'pending' => Article::query()->where('status', 'draft')->whereNotNull('task_id')->whereNull('geo_optimized_at')->count(),
            'completed' => Article::query()->whereNotNull('geo_optimized_at')->count(),
            'total' => Article::query()->where('status', 'draft')->whereNotNull('task_id')->count(),
        ];

        $perTaskStats = Article::query()
            ->select('task_id', DB::raw("COUNT(*) as total"), DB::raw("COUNT(*) FILTER (WHERE geo_optimized_at IS NOT NULL) as done"), DB::raw("COUNT(*) FILTER (WHERE geo_optimized_at IS NULL) as pending"))
            ->where('status', 'draft')
            ->whereNotNull('task_id')
            ->groupBy('task_id')
            ->with('task:id,name')
            ->get();

        return view('admin.geo-optimization.index', [
            'pageTitle' => 'GEO优化管理',
            'activeMenu' => 'geo-optimization',
            'adminSiteName' => AdminWeb::siteName(),
            'logs' => $logs,
            'logStats' => $logStats,
            'articleStats' => $articleStats,
            'perTaskStats' => $perTaskStats,
            'queuePending' => $queuePending,
            'serviceAvailable' => $this->autoGeoService->isAvailable(),
            'filters' => [
                'status' => $request->input('status', ''),
                'dataset' => $request->input('dataset', ''),
            ],
        ]);
    }

    /**
     * GEO优化规则管理。
     */
    public function rules(): View
    {
        $rules = GeoOptimizationRule::query()->orderByDesc('id')->get();

        return view('admin.geo-optimization.rules', [
            'pageTitle' => 'GEO优化规则',
            'activeMenu' => 'geo-optimization',
            'adminSiteName' => AdminWeb::siteName(),
            'rules' => $rules,
        ]);
    }

    /**
     * 创建/更新规则。
     */
    public function storeRule(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'dataset' => ['required', 'string', 'in:default,medical,ecommerce,research'],
            'rules' => ['required', 'string'],
        ]);

        $rulesArray = array_map('trim', explode("\n", $payload['rules']));
        $rulesArray = array_filter($rulesArray, static fn (string $r): bool => $r !== '');

        try {
            GeoOptimizationRule::query()->create([
                'name' => $payload['name'],
                'dataset' => $payload['dataset'],
                'rules' => array_values($rulesArray),
                'is_active' => true,
            ]);

            return back()->with('message', '规则创建成功');
        } catch (Throwable $e) {
            return back()->withErrors($e->getMessage());
        }
    }

    /**
     * 删除规则。
     */
    public function destroyRule(int $ruleId): RedirectResponse
    {
        try {
            GeoOptimizationRule::query()->whereKey($ruleId)->delete();

            return back()->with('message', '规则删除成功');
        } catch (Throwable $e) {
            return back()->withErrors($e->getMessage());
        }
    }

    /**
     * 切换规则状态。
     */
    public function toggleRule(int $ruleId): RedirectResponse
    {
        try {
            $rule = GeoOptimizationRule::query()->findOrFail($ruleId);
            $rule->update(['is_active' => ! $rule->is_active]);

            return back()->with('message', $rule->is_active ? '规则已启用' : '规则已禁用');
        } catch (Throwable $e) {
            return back()->withErrors($e->getMessage());
        }
    }

    /**
     * GEO优化进度 AJAX。
     */
    public function progress(): JsonResponse
    {
        $queuePending = DB::table('jobs')->where('queue', 'geoflow')->count();

        $articleStats = [
            'pending' => Article::query()->where('status', 'draft')->whereNotNull('task_id')->whereNull('geo_optimized_at')->count(),
            'completed' => Article::query()->whereNotNull('geo_optimized_at')->count(),
            'total' => Article::query()->where('status', 'draft')->whereNotNull('task_id')->count(),
        ];

        $logStats = [
            'total' => GeoOptimizationLog::query()->count(),
            'success' => GeoOptimizationLog::query()->where('status', 'success')->count(),
            'failed' => GeoOptimizationLog::query()->whereIn('status', ['failed', 'error'])->count(),
        ];

        $recentLogs = GeoOptimizationLog::query()
            ->with(['article:id,title'])
            ->orderByDesc('id')
            ->limit(5)
            ->get()
            ->map(fn ($log) => [
                'id' => $log->id,
                'article_title' => $log->article?->title,
                'status' => $log->status,
                'score' => $log->geo_scores['geo_score'] ?? null,
                'duration' => $log->duration_seconds,
                'created_at' => $log->created_at?->format('H:i:s'),
            ]);

        return response()->json([
            'queue_pending' => $queuePending,
            'article_stats' => $articleStats,
            'log_stats' => $logStats,
            'recent_logs' => $recentLogs,
        ]);
    }

    /**
     * AutoGEO服务健康检查 (AJAX)。
     */
    public function healthCheck(): JsonResponse
    {
        $available = $this->autoGeoService->isAvailable();

        return response()->json([
            'success' => $available,
            'message' => $available ? 'AutoGEO服务正常' : 'AutoGEO服务不可用',
        ]);
    }
}
