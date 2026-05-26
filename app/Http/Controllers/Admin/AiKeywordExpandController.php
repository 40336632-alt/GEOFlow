<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Keyword;
use App\Models\KeywordLibrary;
use App\Services\AiService;
use Illuminate\Http\Request;

class AiKeywordExpandController extends Controller
{
    protected AiService $aiService;

    public function __construct(AiService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function index()
    {
        return view('admin.data.ai-expand.index', [
            'libraries' => KeywordLibrary::orderBy('name')->get(),
        ]);
    }

    public function expand(Request $request)
    {
        $validated = $request->validate([
            'keyword' => 'required|string|max:200',
            'brand_name' => 'nullable|string|max:200',
            'count' => 'nullable|integer|min:5|max:50',
            'library_id' => 'nullable|exists:keyword_libraries,id',
        ]);

        $count = $validated['count'] ?? 20;

        // 调用AI服务生成关键词
        $expandedKeywords = $this->aiService->expandKeywords(
            $validated['keyword'],
            $validated['brand_name'] ?? null,
            $count
        );

        if (empty($expandedKeywords)) {
            return back()->withErrors('AI服务暂时不可用，请稍后重试');
        }

        // 如果指定了关键词库，保存关键词
        $savedCount = 0;
        if (!empty($validated['library_id'])) {
            foreach ($expandedKeywords as $kw) {
                $exists = Keyword::where('library_id', $validated['library_id'])
                    ->where('keyword', $kw)
                    ->exists();
                if (!$exists) {
                    Keyword::create([
                        'library_id' => $validated['library_id'],
                        'keyword' => $kw,
                        'used_count' => 0,
                        'usage_count' => 0,
                    ]);
                    $savedCount++;
                }
            }
        }

        return view('admin.data.ai-expand.results', [
            'keyword' => $validated['keyword'],
            'brandName' => $validated['brand_name'] ?? null,
            'expandedKeywords' => $expandedKeywords,
            'libraryId' => $validated['library_id'] ?? null,
            'savedCount' => $savedCount,
            'libraries' => KeywordLibrary::orderBy('name')->get(),
        ]);
    }
}
