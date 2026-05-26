<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ImageLibrary;
use App\Models\WritingInstruction;
use App\Models\ViralReplication;
use App\Services\AiService;
use App\Services\WebCrawlerService;
use Illuminate\Http\Request;

class ViralReplicationController extends Controller
{
    protected AiService $aiService;
    protected WebCrawlerService $crawlerService;

    public function __construct(AiService $aiService, WebCrawlerService $crawlerService)
    {
        $this->aiService = $aiService;
        $this->crawlerService = $crawlerService;
    }

    public function index(Request $request)
    {
        $query = ViralReplication::with(['category', 'instruction'])
            ->where('user_id', auth('admin')->id());

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $replications = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.viral-replication.index', [
            'replications' => $replications,
            'currentStatus' => $request->get('status'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'source_url' => 'required|url|max:500',
            'category_id' => 'nullable|exists:categories,id',
            'image_library_id' => 'nullable|exists:image_libraries,id',
            'instruction_id' => 'nullable|exists:writing_instructions,id',
        ]);

        $userId = auth('admin')->id();

        $replication = ViralReplication::create([
            'user_id' => $userId,
            'source_url' => $validated['source_url'],
            'category_id' => $validated['category_id'] ?? null,
            'image_library_id' => $validated['image_library_id'] ?? null,
            'instruction_id' => $validated['instruction_id'] ?? null,
            'status' => 'rewriting',
        ]);

        $this->processReplication($replication);

        return back()->with('message', '爆文复刻任务已创建');
    }

    public function batch()
    {
        return view('admin.viral-replication.batch', [
            'categories' => Category::orderBy('name')->get(),
            'imageLibraries' => ImageLibrary::orderBy('name')->get(),
            'instructions' => WritingInstruction::ofType('replication')->orderBy('name')->get(),
        ]);
    }

    public function batchStore(Request $request)
    {
        $validated = $request->validate([
            'urls' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'image_library_id' => 'nullable|exists:image_libraries,id',
            'instruction_id' => 'nullable|exists:writing_instructions,id',
        ]);

        $userId = auth('admin')->id();
        $urls = array_filter(array_map('trim', explode("\n", $validated['urls'])));

        $count = 0;
        foreach ($urls as $url) {
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                $replication = ViralReplication::create([
                    'user_id' => $userId,
                    'source_url' => $url,
                    'category_id' => $validated['category_id'] ?? null,
                    'image_library_id' => $validated['image_library_id'] ?? null,
                    'instruction_id' => $validated['instruction_id'] ?? null,
                    'status' => 'rewriting',
                ]);

                $this->processReplication($replication);

                $count++;
            }
        }

        return back()->with('message', "已创建 {$count} 个爆文复刻任务");
    }

    public function destroy(ViralReplication $viralReplication)
    {
        if ($viralReplication->user_id !== auth('admin')->id()) {
            abort(403);
        }
        $viralReplication->delete();

        return back()->with('message', '爆文复刻任务已删除');
    }

    /**
     * 处理复刻任务：抓取 + 改写
     */
    protected function processReplication(ViralReplication $replication): void
    {
        try {
            // 1. 抓取源文章
            $crawled = $this->crawlerService->crawl($replication->source_url);

            if (!$crawled) {
                $replication->update([
                    'status' => 'failed',
                    'error_message' => '无法抓取源文章',
                ]);
                return;
            }

            $replication->update([
                'source_title' => $crawled['title'],
                'source_content' => $crawled['content'],
            ]);

            // 2. 获取改写指令
            $instruction = null;
            if ($replication->instruction_id) {
                $instructionObj = WritingInstruction::find($replication->instruction_id);
                $instruction = $instructionObj?->content;
            }

            // 3. AI改写
            $rewrittenContent = $this->aiService->rewriteArticle($crawled['content'], $instruction);

            if (!$rewrittenContent) {
                $replication->update([
                    'status' => 'failed',
                    'error_message' => 'AI改写失败',
                ]);
                return;
            }

            // 4. 生成新标题
            $rewrittenTitle = $this->generateTitle($crawled['title'], $instruction);

            $replication->update([
                'rewritten_title' => $rewrittenTitle,
                'rewritten_content' => $rewrittenContent,
                'status' => 'completed',
            ]);

        } catch (\Exception $e) {
            $replication->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 生成新标题
     */
    protected function generateTitle(string $originalTitle, string $instruction = null): string
    {
        $systemPrompt = $instruction ?: "请根据原标题生成一个新的、吸引人的标题。要求：
1. 保持原文核心主题
2. 更有吸引力和点击欲望
3. 适合中文自媒体平台
4. 长度控制在15-30字之间";

        $result = $this->aiService->chat($systemPrompt, "原标题：{$originalTitle}", ['max_tokens' => 100]);

        return $result ?: $originalTitle;
    }
}
