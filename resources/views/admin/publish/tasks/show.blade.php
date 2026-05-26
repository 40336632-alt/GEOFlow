@extends('admin.layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.publish-tasks.index') }}" class="text-gray-500 hover:text-gray-700">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">发布任务详情</h1>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <div class="text-sm text-gray-500">发布类型</div>
                <div class="text-sm font-medium text-gray-900">{{ $types[$task->publish_type] ?? '-' }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-500">平台</div>
                <div class="text-sm font-medium text-gray-900">{{ $platforms[$task->platform] ?? '-' }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-500">状态</div>
                <div class="text-sm font-medium text-gray-900">
                    @if($task->status === 'pending')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">待处理</span>
                    @elseif($task->status === 'running')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">执行中</span>
                    @elseif($task->status === 'completed')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">已完成</span>
                    @elseif($task->status === 'failed')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">失败</span>
                    @endif
                </div>
            </div>
            <div>
                <div class="text-sm text-gray-500">同步状态</div>
                <div class="text-sm font-medium text-gray-900">{{ $task->sync_status ?? '-' }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-500">文章ID</div>
                <div class="text-sm font-medium text-gray-900">{{ $task->article_id }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-500">浏览器配置ID</div>
                <div class="text-sm font-medium text-gray-900">{{ $task->profile_id ?? '-' }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-500">远端文章ID</div>
                <div class="text-sm font-medium text-gray-900">{{ $task->remote_article_id ?? '-' }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-500">发布时间</div>
                <div class="text-sm font-medium text-gray-900">{{ $task->published_at?->format('Y-m-d H:i') ?? '-' }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-500">同步时间</div>
                <div class="text-sm font-medium text-gray-900">{{ $task->synced_at?->format('Y-m-d H:i') ?? '-' }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-500">创建时间</div>
                <div class="text-sm font-medium text-gray-900">{{ $task->created_at->format('Y-m-d H:i') }}</div>
            </div>
        </div>

        @if($task->error_message)
            <div class="mt-4 p-4 bg-red-50 rounded-lg">
                <div class="text-sm text-red-800">{{ $task->error_message }}</div>
            </div>
        @endif

        @if($task->sync_error_message)
            <div class="mt-4 p-4 bg-red-50 rounded-lg">
                <div class="text-sm text-red-800">{{ $task->sync_error_message }}</div>
            </div>
        @endif

        @if($task->published_url)
            <div class="mt-4 p-4 bg-green-50 rounded-lg">
                <div class="text-sm text-green-800">
                    发布链接：
                    <a href="{{ $task->published_url }}" target="_blank" class="underline">{{ $task->published_url }}</a>
                </div>
            </div>
        @endif

        <div class="mt-4 flex gap-3">
            @if($task->status === 'pending')
                <form action="{{ route('admin.publish-tasks.execute', $task) }}" method="POST"
                      onsubmit="return confirm('确定执行该发布任务？')">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">
                        执行任务
                    </button>
                </form>
            @endif
            <form action="{{ route('admin.publish-tasks.destroy', $task) }}" method="POST"
                  onsubmit="return confirm('确定删除该任务？')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">
                    删除任务
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
