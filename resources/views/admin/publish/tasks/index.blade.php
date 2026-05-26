@extends('admin.layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">发布任务管理</h1>
        <a href="{{ route('admin.publish-tasks.create') }}"
           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
            新建发布任务
        </a>
    </div>

    <div class="flex flex-wrap gap-4">
        <div class="flex gap-2">
            <a href="{{ route('admin.publish-tasks.index') }}"
               class="px-3 py-1.5 text-sm rounded-lg {{ !$currentType ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                全部类型
            </a>
            @foreach($types as $key => $name)
                <a href="{{ route('admin.publish-tasks.index', ['type' => $key]) }}"
                   class="px-3 py-1.5 text-sm rounded-lg {{ $currentType === $key ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    {{ $name }}
                </a>
            @endforeach
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.publish-tasks.index', ['type' => $currentType]) }}"
               class="px-3 py-1.5 text-sm rounded-lg {{ !$currentStatus ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                全部状态
            </a>
            <a href="{{ route('admin.publish-tasks.index', ['type' => $currentType, 'status' => 'pending']) }}"
               class="px-3 py-1.5 text-sm rounded-lg {{ $currentStatus === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                待处理
            </a>
            <a href="{{ route('admin.publish-tasks.index', ['type' => $currentType, 'status' => 'completed']) }}"
               class="px-3 py-1.5 text-sm rounded-lg {{ $currentStatus === 'completed' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                已完成
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">序号</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">文章标题</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">发布类型</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">平台</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">状态</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">发布时间</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">操作</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($tasks as $index => $task)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $tasks->firstItem() + $index }}</td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                        <a href="{{ route('admin.publish-tasks.show', $task) }}" class="text-blue-600 hover:text-blue-800">
                            {{ Str::limit($task->article->title ?? '-', 40) }}
                        </a>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $task->publish_type === 'personal' ? 'bg-blue-100 text-blue-800' : ($task->publish_type === 'kol' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800') }}">
                            {{ $types[$task->publish_type] ?? $task->publish_type }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $task->profile ? ($task->profile->account_name ?? '-') : '-' }}
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
                        {{ $task->published_at?->format('Y-m-d H:i') ?? '-' }}
                    </td>
                    <td class="px-6 py-4 text-right text-sm">
                        @if($task->isPending())
                            <form action="{{ route('admin.publish-tasks.execute', $task) }}" method="POST" class="inline mr-3">
                                @csrf
                                <button type="submit" class="text-green-600 hover:text-green-800">
                                    <i data-lucide="play" class="w-4 h-4 inline"></i>
                                </button>
                            </form>
                        @endif
                        @if($task->published_url)
                            <a href="{{ $task->published_url }}" target="_blank" class="text-blue-600 hover:text-blue-800 mr-3">
                                <i data-lucide="external-link" class="w-4 h-4 inline"></i>
                            </a>
                        @endif
                        <form action="{{ route('admin.publish-tasks.destroy', $task) }}" method="POST" class="inline"
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
                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                        暂无发布任务
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
