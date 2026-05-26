@extends('admin.layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.writing-tasks.index') }}" class="text-gray-500 hover:text-gray-700">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">{{ $task->name }}</h1>
        @if($task->isCompleted())
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                已完成
            </span>
        @elseif($task->isRunning())
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                执行中
            </span>
        @elseif($task->isFailed())
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                失败
            </span>
        @else
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                待执行
            </span>
        @endif
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">任务信息</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <div class="text-sm text-gray-500">关键词库</div>
                <div class="text-sm font-medium text-gray-900">{{ $task->keywordLibrary->name ?? '-' }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-500">文章分类</div>
                <div class="text-sm font-medium text-gray-900">{{ $task->category->name ?? '自动创建' }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-500">图片库</div>
                <div class="text-sm font-medium text-gray-900">{{ $task->imageLibrary->name ?? '未使用' }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-500">配图数量</div>
                <div class="text-sm font-medium text-gray-900">{{ $task->image_count }} 张</div>
            </div>
            <div>
                <div class="text-sm text-gray-500">知识库</div>
                <div class="text-sm font-medium text-gray-900">{{ $task->knowledgeBase->name ?? '未使用' }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-500">写作指令</div>
                <div class="text-sm font-medium text-gray-900">{{ $task->instruction->name ?? '默认指令' }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-500">创作上限</div>
                <div class="text-sm font-medium text-gray-900">{{ $task->max_articles }} 篇</div>
            </div>
            <div>
                <div class="text-sm text-gray-500">已创建</div>
                <div class="text-sm font-medium text-gray-900">{{ $task->created_count }} 篇</div>
            </div>
        </div>

        @if($task->error_message)
            <div class="mt-4 p-4 bg-red-50 rounded-lg">
                <div class="text-sm text-red-800">{{ $task->error_message }}</div>
            </div>
        @endif

        <div class="mt-4 flex gap-3">
            @if($task->isPending() || $task->isFailed())
                <form action="{{ route('admin.writing-tasks.execute', $task) }}" method="POST"
                      onsubmit="return confirm('确定执行该写作任务？')">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">
                        <i data-lucide="play" class="w-4 h-4 mr-2"></i>
                        执行任务
                    </button>
                </form>
            @endif
            <form action="{{ route('admin.writing-tasks.destroy', $task) }}" method="POST"
                  onsubmit="return confirm('确定删除该任务？')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">
                    <i data-lucide="trash-2" class="w-4 h-4 mr-2"></i>
                    删除任务
                </button>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">生成的文章 ({{ $task->articles->count() }})</h2>
        @if($task->articles->count() > 0)
            <div class="space-y-4">
                @foreach($task->articles as $article)
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <div class="text-sm font-medium text-gray-900">{{ $article->title }}</div>
                            <div class="text-xs text-gray-500">{{ $article->created_at->format('Y-m-d H:i') }}</div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($article->status === 'published')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">已发布</span>
                            @elseif($article->status === 'draft')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">草稿</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">{{ $article->status }}</span>
                            @endif
                            <a href="{{ route('admin.articles.edit', $article) }}"
                               class="text-blue-600 hover:text-blue-800">
                                <i data-lucide="pencil" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 text-gray-500">
                暂无生成的文章
            </div>
        @endif
    </div>
</div>
@endsection
