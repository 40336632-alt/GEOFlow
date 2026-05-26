<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\ImageLibrary;
use App\Models\KeywordLibrary;
use App\Models\KnowledgeBase;
use App\Models\WritingInstruction;
use App\Models\WritingTask;
use App\Support\GeoFlow\ArticleWorkflow;
use Illuminate\Http\Request;

class WritingTaskController extends Controller
{
    public function index(Request $request)
    {
        $query = WritingTask::with(['keywordLibrary', 'category', 'knowledgeBase', 'instruction'])
            ->where('user_id', auth('admin')->id());

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $tasks = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.writing-tasks.index', [
            'tasks' => $tasks,
            'currentStatus' => $request->get('status'),
        ]);
    }

    public function create()
    {
        return view('admin.writing-tasks.create', [
            'keywordLibraries' => KeywordLibrary::withCount('keywords')->orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
            'imageLibraries' => ImageLibrary::withCount('images')->orderBy('name')->get(),
            'knowledgeBases' => KnowledgeBase::orderBy('name')->get(),
            'instructions' => WritingInstruction::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'keyword_library_id' => 'required|exists:keyword_libraries,id',
            'category_id' => 'nullable|exists:categories,id',
            'image_library_id' => 'nullable|exists:image_libraries,id',
            'image_count' => 'integer|min:0|max:10',
            'knowledge_base_id' => 'nullable|exists:knowledge_bases,id',
            'instruction_id' => 'nullable|exists:writing_instructions,id',
            'max_articles' => 'required|integer|min:1|max:100',
        ]);

        $validated['user_id'] = auth('admin')->id();

        WritingTask::create($validated);

        return redirect()
            ->route('admin.writing-tasks.index')
            ->with('message', '写作任务创建成功');
    }

    public function show(WritingTask $writingTask)
    {
        if ($writingTask->user_id !== auth('admin')->id()) {
            abort(403);
        }
        $writingTask->load(['keywordLibrary', 'category', 'imageLibrary', 'knowledgeBase', 'instruction']);

        return view('admin.writing-tasks.show', [
            'task' => $writingTask,
        ]);
    }

    public function execute(WritingTask $writingTask)
    {
        if ($writingTask->user_id !== auth('admin')->id()) {
            abort(403);
        }
        if (!$writingTask->isPending() && !$writingTask->isFailed()) {
            return back()->withErrors(['task' => '该任务无法执行']);
        }

        $writingTask->update([
            'status' => 'running',
            'error_message' => null,
        ]);

        try {
            // 获取关键词库中的关键词
            $keywords = $writingTask->keywordLibrary->keywords()->limit($writingTask->max_articles)->get();

            if ($keywords->isEmpty()) {
                throw new \Exception('关键词库为空');
            }

            // 获取或创建分类
            $category = $writingTask->category;
            if (!$category) {
                $category = Category::create([
                    'name' => $writingTask->name,
                    'slug' => ArticleWorkflow::generateUniqueSlug($writingTask->name),
                ]);
            }

            $created = 0;
            foreach ($keywords as $keyword) {
                // 生成文章
                $title = $keyword->keyword . ' - 相关资讯';

                // 检查是否已存在
                $exists = Article::where('title', $title)->where('category_id', $category->id)->exists();
                if ($exists) {
                    continue;
                }

                Article::create([
                    'title' => $title,
                    'slug' => ArticleWorkflow::generateUniqueSlug($title),
                    'excerpt' => '关于' . $keyword->keyword . '的详细内容',
                    'content' => '<p>这是一篇关于' . $keyword->keyword . '的文章内容。</p>',
                    'category_id' => $category->id,
                    'author_id' => 1,
                    'writing_task_id' => $writingTask->id,
                    'original_keyword' => $keyword->keyword,
                    'keywords' => $keyword->keyword,
                    'status' => 'published',
                    'review_status' => 'approved',
                    'is_ai_generated' => 1,
                    'published_at' => now(),
                ]);

                $created++;
            }

            $writingTask->update([
                'status' => 'completed',
                'created_count' => $created,
                'last_written_at' => now(),
            ]);

            return back()->with('message', "写作任务完成，已创建 {$created} 篇文章");

        } catch (\Exception $e) {
            $writingTask->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return back()->withErrors(['task' => '执行失败：' . $e->getMessage()]);
        }
    }

    public function destroy(WritingTask $writingTask)
    {
        if ($writingTask->user_id !== auth('admin')->id()) {
            abort(403);
        }
        $writingTask->delete();

        return redirect()
            ->route('admin.writing-tasks.index')
            ->with('message', '写作任务已删除');
    }
}
