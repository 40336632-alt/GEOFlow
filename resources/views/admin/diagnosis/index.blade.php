@extends('admin.layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">AI可见度诊断</h1>
        <a href="{{ route('admin.diagnosis.create') }}"
           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
            新建诊断
        </a>
    </div>

    {{-- 功能说明 --}}
    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
        <div class="flex">
            <i data-lucide="eye" class="w-5 h-5 text-purple-600 mr-2 shrink-0 mt-0.5"></i>
            <div class="text-sm text-purple-800">
                <p class="font-medium">AI可见度诊断功能说明：</p>
                <ul class="mt-1 list-disc list-inside space-y-1">
                    <li>检测您的品牌在AI搜索引擎中的可见度</li>
                    <li>支持组合查询：前缀/地域 + 形容词 + 主词 + 目标词 + 推荐词</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- 筛选 --}}
    <div class="flex gap-2">
        <a href="{{ route('admin.diagnosis.index') }}"
           class="px-3 py-1.5 text-sm rounded-lg {{ !$currentStatus ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            全部
        </a>
        <a href="{{ route('admin.diagnosis.index', ['status' => 'pending']) }}"
           class="px-3 py-1.5 text-sm rounded-lg {{ $currentStatus === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            待处理
        </a>
        <a href="{{ route('admin.diagnosis.index', ['status' => 'completed']) }}"
           class="px-3 py-1.5 text-sm rounded-lg {{ $currentStatus === 'completed' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            已完成
        </a>
    </div>

    {{-- 任务列表 --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">序号</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">主关键词</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">品牌名称</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">查询数</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">可见度</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">状态</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">创建时间</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">操作</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($tasks as $index => $task)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $tasks->firstItem() + $index }}</td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                        <a href="{{ route('admin.diagnosis.show', $task) }}" class="text-blue-600 hover:text-blue-800">
                            {{ $task->main_keyword }}
                        </a>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $task->brand_name ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $task->total_queries }}</td>
                    <td class="px-6 py-4 text-sm">
                        @if($task->isCompleted())
                            <span class="{{ $task->visibility_rate > 50 ? 'text-green-600' : ($task->visibility_rate > 0 ? 'text-yellow-600' : 'text-gray-600') }}">
                                {{ $task->visibility_rate }}%
                            </span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm">
                        @if($task->isPending())
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">待处理</span>
                        @elseif($task->isRunning())
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">执行中</span>
                        @elseif($task->isCompleted())
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">已完成</span>
                        @elseif($task->isFailed())
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">失败</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $task->created_at->format('Y-m-d H:i') }}
                    </td>
                    <td class="px-6 py-4 text-right text-sm">
                        @if($task->isCompleted())
                            <a href="{{ route('admin.diagnosis.report', $task) }}" class="text-purple-600 hover:text-purple-800 mr-3">
                                <i data-lucide="file-text" class="w-4 h-4 inline"></i>
                            </a>
                        @endif
                        @if($task->isPending() || $task->isFailed())
                            <form action="{{ route('admin.diagnosis.execute', $task) }}" method="POST" class="inline mr-3">
                                @csrf
                                <button type="submit" class="text-green-600 hover:text-green-800">
                                    <i data-lucide="play" class="w-4 h-4 inline"></i>
                                </button>
                            </form>
                        @endif
                        <form action="{{ route('admin.diagnosis.destroy', $task) }}" method="POST" class="inline"
                              onsubmit="return confirm('确定删除？')">
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
                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                        暂无诊断任务
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
