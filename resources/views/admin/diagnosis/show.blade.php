@extends('admin.layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">诊断任务详情</h1>
        <div class="flex gap-3">
            @if($task->isCompleted())
                <a href="{{ route('admin.diagnosis.report', $task) }}"
                   class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700">
                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i>
                    查看报告
                </a>
            @endif
            <a href="{{ route('admin.diagnosis.index') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                返回列表
            </a>
        </div>
    </div>

    {{-- 任务信息 --}}
    <div class="bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-500">主关键词</label>
                <p class="mt-1 text-lg text-gray-900">{{ $task->main_keyword }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500">品牌名称</label>
                <p class="mt-1 text-lg text-gray-900">{{ $task->brand_name ?? '-' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500">查询组合</label>
                <p class="mt-1 text-sm text-gray-600">
                    C: {{ $task->column_c }}<br>
                    D: {{ $task->column_d }}
                </p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500">状态</label>
                <p class="mt-1">
                    @if($task->isPending())
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">待处理</span>
                    @elseif($task->isRunning())
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">执行中</span>
                    @elseif($task->isCompleted())
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">已完成</span>
                    @elseif($task->isFailed())
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">失败</span>
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- 统计信息 --}}
    @if($task->isCompleted())
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <p class="text-sm font-medium text-gray-500">总查询数</p>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $task->total_queries }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <p class="text-sm font-medium text-gray-500">品牌提及数</p>
            <p class="mt-2 text-3xl font-bold text-green-600">{{ $task->brand_mentioned }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <p class="text-sm font-medium text-gray-500">可见度</p>
            <p class="mt-2 text-3xl font-bold {{ $task->visibility_rate > 50 ? 'text-green-600' : ($task->visibility_rate > 0 ? 'text-yellow-600' : 'text-red-600') }}">
                {{ $task->visibility_rate }}%
            </p>
        </div>
    </div>
    @endif

    {{-- 检测平台 --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">检测平台</h3>
        <div class="flex flex-wrap gap-2">
            @foreach($task->platforms as $platform)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                    {{ $platforms[$platform] ?? $platform }}
                </span>
            @endforeach
        </div>
    </div>

    {{-- 查询结果 --}}
    @if($task->results->isNotEmpty())
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">查询结果</h3>
        </div>
        <div class="divide-y divide-gray-200">
            @foreach($task->results as $result)
            <div class="px-6 py-4">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-medium text-gray-900">{{ $result->query }}</span>
                            <span class="text-xs text-gray-500">{{ $platforms[$result->platform] ?? $result->platform }}</span>
                            @if($result->brand_mentioned)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                    已提及
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                    未提及
                                </span>
                            @endif
                        </div>
                        @if($result->answer)
                            <div class="mt-2 text-sm text-gray-600 bg-gray-50 rounded-lg p-3">
                                {!! nl2br(e(Str::limit($result->answer, 300))) !!}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @elseif($task->isRunning())
    <div class="bg-white rounded-lg shadow p-12 text-center">
        <i data-lucide="loader" class="w-12 h-12 mx-auto mb-4 text-gray-400 animate-spin"></i>
        <p class="text-lg text-gray-600">正在执行诊断，请稍后刷新页面...</p>
    </div>
    @endif
</div>
@endsection
