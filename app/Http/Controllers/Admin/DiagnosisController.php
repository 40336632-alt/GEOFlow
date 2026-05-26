<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiagnosisTask;
use App\Models\DiagnosisResult;
use App\Services\AiService;
use Illuminate\Http\Request;

class DiagnosisController extends Controller
{
    protected AiService $aiService;

    public function __construct(AiService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function index(Request $request)
    {
        $query = DiagnosisTask::where('user_id', auth('admin')->id());

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $tasks = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.diagnosis.index', [
            'tasks' => $tasks,
            'platforms' => DiagnosisTask::PLATFORMS,
            'currentStatus' => $request->get('status'),
        ]);
    }

    public function create()
    {
        return view('admin.diagnosis.create', [
            'platforms' => DiagnosisTask::PLATFORMS,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'main_keyword' => 'required|string|max:100',
            'brand_name' => 'nullable|string|max:200',
            'column_a' => 'nullable|string',
            'column_b' => 'nullable|string',
            'column_c' => 'required|string|max:100',
            'column_d' => 'required|string|max:100',
            'column_e' => 'nullable|string',
            'platforms' => 'required|array|min:1',
            'platforms.*' => 'string|in:' . implode(',', array_keys(DiagnosisTask::PLATFORMS)),
        ]);

        $userId = auth('admin')->id();

        // Parse comma-separated values
        $columnA = !empty($validated['column_a']) ? array_map('trim', explode(',', $validated['column_a'])) : [];
        $columnB = !empty($validated['column_b']) ? array_map('trim', explode(',', $validated['column_b'])) : [];
        $columnE = !empty($validated['column_e']) ? array_map('trim', explode(',', $validated['column_e'])) : [];

        // Calculate total queries
        $totalQueries = max(1, count($columnA) + 1) * max(1, count($columnB) + 1) * max(1, count($columnE) + 1) * count($validated['platforms']);

        $task = DiagnosisTask::create([
            'user_id' => $userId,
            'main_keyword' => $validated['main_keyword'],
            'brand_name' => $validated['brand_name'] ?? null,
            'column_a' => $columnA,
            'column_b' => $columnB,
            'column_c' => $validated['column_c'],
            'column_d' => $validated['column_d'],
            'column_e' => $columnE,
            'platforms' => $validated['platforms'],
            'total_queries' => $totalQueries,
            'status' => 'running',
        ]);

        // 执行诊断
        $this->executeDiagnosis($task);

        return redirect()->route('admin.diagnosis.show', $task)
            ->with('message', 'AI可见度诊断已完成');
    }

    public function show(DiagnosisTask $diagnosisTask)
    {
        if ($diagnosisTask->user_id !== auth('admin')->id()) {
            abort(403);
        }
        $diagnosisTask->load('results');

        return view('admin.diagnosis.show', [
            'task' => $diagnosisTask,
            'platforms' => DiagnosisTask::PLATFORMS,
        ]);
    }

    public function execute(DiagnosisTask $diagnosisTask)
    {
        if ($diagnosisTask->user_id !== auth('admin')->id()) {
            abort(403);
        }
        if (!$diagnosisTask->isPending() && !$diagnosisTask->isFailed()) {
            return back()->withErrors('只能执行待处理或失败的任务');
        }

        $diagnosisTask->update(['status' => 'running']);

        $this->executeDiagnosis($diagnosisTask);

        return back()->with('message', '诊断任务已执行');
    }

    public function destroy(DiagnosisTask $diagnosisTask)
    {
        if ($diagnosisTask->user_id !== auth('admin')->id()) {
            abort(403);
        }
        $diagnosisTask->delete();

        return back()->with('message', '诊断任务已删除');
    }

    public function report(DiagnosisTask $diagnosisTask)
    {
        $diagnosisTask->load('results');

        return view('admin.diagnosis.report', [
            'task' => $diagnosisTask,
            'platforms' => DiagnosisTask::PLATFORMS,
        ]);
    }

    /**
     * 执行诊断
     */
    protected function executeDiagnosis(DiagnosisTask $task): void
    {
        try {
            $queries = $this->generateQueries($task);
            $brandMentioned = 0;

            foreach ($queries as $query) {
                foreach ($task->platforms as $platform) {
                    $result = $this->aiService->checkIndex($query, $task->brand_name ?? '', $platform);

                    DiagnosisResult::create([
                        'task_id' => $task->id,
                        'query' => $query,
                        'platform' => $platform,
                        'answer' => $result['answer'],
                        'brand_mentioned' => $result['brand_mentioned'],
                        'mention_position' => $result['mention_position'],
                        'created_at' => now(),
                    ]);

                    if ($result['brand_mentioned']) {
                        $brandMentioned++;
                    }
                }
            }

            // 更新任务状态
            $visibilityScore = $task->total_queries > 0
                ? round($brandMentioned / $task->total_queries * 100, 2)
                : 0;

            $task->update([
                'status' => 'completed',
                'brand_mentioned' => $brandMentioned,
                'visibility_score' => $visibilityScore,
            ]);

        } catch (\Exception $e) {
            $task->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 生成查询组合
     */
    protected function generateQueries(DiagnosisTask $task): array
    {
        $queries = [];
        $prefixes = !empty($task->column_a) ? $task->column_a : [''];
        $adjectives = !empty($task->column_b) ? $task->column_b : [''];
        $recommendations = !empty($task->column_e) ? $task->column_e : [''];

        foreach ($prefixes as $prefix) {
            foreach ($adjectives as $adjective) {
                foreach ($recommendations as $recommendation) {
                    $query = trim($prefix . ' ' . $adjective . ' ' . $task->column_c . ' ' . $task->column_d . ' ' . $recommendation);
                    $query = preg_replace('/\s+/', ' ', $query);
                    if (!empty($query)) {
                        $queries[] = $query;
                    }
                }
            }
        }

        return array_unique($queries);
    }
}
