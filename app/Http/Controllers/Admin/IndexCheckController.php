<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IndexCheck;
use App\Models\IndexCheckDetail;
use App\Services\AiService;
use Illuminate\Http\Request;

class IndexCheckController extends Controller
{
    const PLATFORMS = [
        'deepseek' => 'DeepSeek',
        'doubao' => '豆包AI',
        'yuanbao' => '腾讯元宝',
        'tongyi' => '通义千问',
        'wenxin' => '文心一言',
        'nano' => '纳米AI',
        'kimi' => 'Kimi',
        'zhipu' => '智谱清言',
    ];

    protected AiService $aiService;

    public function __construct(AiService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function index(Request $request)
    {
        $query = IndexCheck::with('details')->where('user_id', auth('admin')->id());

        $checks = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.data.index-check.index', [
            'checks' => $checks,
            'platforms' => self::PLATFORMS,
        ]);
    }

    public function create()
    {
        return view('admin.data.index-check.create', [
            'platforms' => self::PLATFORMS,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:500',
            'brand_name' => 'nullable|string|max:200',
            'platforms' => 'required|array|min:1',
            'platforms.*' => 'string|in:' . implode(',', array_keys(self::PLATFORMS)),
        ]);

        $userId = auth('admin')->id();

        $check = IndexCheck::create([
            'user_id' => $userId,
            'question' => $validated['question'],
            'brand_name' => $validated['brand_name'] ?? null,
            'platforms' => $validated['platforms'],
        ]);

        // 查询各平台
        $totalIndexed = 0;
        foreach ($validated['platforms'] as $platform) {
            $result = $this->aiService->checkIndex(
                $validated['question'],
                $validated['brand_name'] ?? '',
                $platform
            );

            IndexCheckDetail::create([
                'check_id' => $check->id,
                'platform' => $platform,
                'is_indexed' => $result['brand_mentioned'],
                'answer_text' => $result['answer'],
                'checked_at' => now(),
            ]);

            if ($result['brand_mentioned']) {
                $totalIndexed++;
            }
        }

        // 更新统计
        $check->update(['total_indexed' => $totalIndexed]);

        return redirect()->route('admin.index-check.show', $check)
            ->with('message', '收录查询完成');
    }

    public function show(IndexCheck $indexCheck)
    {
        if ($indexCheck->user_id !== auth('admin')->id()) {
            abort(403);
        }
        $indexCheck->load('details');

        return view('admin.data.index-check.show', [
            'check' => $indexCheck,
            'platforms' => self::PLATFORMS,
        ]);
    }

    public function destroy(IndexCheck $indexCheck)
    {
        if ($indexCheck->user_id !== auth('admin')->id()) {
            abort(403);
        }
        $indexCheck->delete();

        return back()->with('message', '收录查询记录已删除');
    }
}
