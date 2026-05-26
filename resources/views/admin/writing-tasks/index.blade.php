@extends('admin.layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">AI写作任务</h1>
        <a href="{{ route('admin.writing-tasks.create') }}"
           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
            添加任务
        </a>
    </div>

    {{-- 筛选 --}}
    <div class="flex gap-2">
        <a href="{{ route('admin.writing-tasks.index') }}"
           class="px-3 py-1.5 text-sm rounded-lg {{ !$currentStatus ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            全部
        </a>
        <a href="{{ route('admin.writing-tasks.index', ['status' => 'pending']) }}"
           class="px-3 py-1.5 text-sm rounded-lg {{ $currentStatus === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            待执行
        </a>
        <a href="{{ route('admin.writing-tasks.index', ['status' => 'running']) }}"
           class="px-3 py-1.5 text-sm rounded-lg {{ $currentStatus === 'running' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            执行中
        </a>
        <a href="{{ route('admin.writing-tasks.index', ['status' => 'completed']) }}"
           class="px-3 py-1.5 text-sm rounded-lg {{ $currentStatus === 'completed' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            已完成
        </a>
        <a href="{{ route('admin.writing-tasks.index', ['status' => 'failed']) }}"
           class="px-3 py-1.5 text-sm rounded-lg {{ $currentStatus === 'failed' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            失败
        </a>
    </div>

    {{-- 表格 --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">任务名</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">蒸馏词</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">创作上限</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">已创作</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">知识库</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">状态</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">最新写作</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">创建时间</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">操作</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($tasks as $task)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $task->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $task->keywordLibrary->name ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $task->max_articles }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $task->created_count }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        @if($task->knowledgeBase)
                            <span class="text-green-600">{{ $task->knowledgeBase->name }}</span>
                        @else
                            <span class="text-gray-400">未使用</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm">
                        @if($task->isPending())
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                待执行
                            </span>
                        @elseif($task->isRunning())
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                执行中
                            </span>
                        @elseif($task->isCompleted())
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                已完成
                            </span>
                        @elseif($task->isFailed())
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                失败
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $task->last_written_at ? $task->last_written_at->format('Y-m-d H:i') : '-' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $task->created_at->format('Y-m-d H:i') }}</td>
                    <td class="px-6 py-4 text-right text-sm space-x-2">
                        <a href="{{ route('admin.writing-tasks.show', $task) }}"
                           class="text-blue-600 hover:text-blue-800">
                            <i data-lucide="eye" class="w-4 h-4 inline"></i>
                        </a>
                        @if($task->isPending() || $task->isFailed())
                            <form action="{{ route('admin.writing-tasks.execute', $task) }}" method="POST" class="inline"
                                  onsubmit="return confirm('确定执行该写作任务？')">
                                @csrf
                                <button type="submit" class="text-green-600 hover:text-green-800">
                                    <i data-lucide="play" class="w-4 h-4 inline"></i>
                                </button>
                            </form>
                        @endif
                        <form action="{{ route('admin.writing-tasks.destroy', $task) }}" method="POST" class="inline"
                              onsubmit="return confirm('确定删除该任务？')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800">
                                <i data-lucide="trash-2" class="w-4 h-4 inline"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                        暂无写作任务，点击"添加任务"创建
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $tasks->links() }}
    </div>
</div>
@endsection
