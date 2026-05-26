@extends('admin.layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">AI可见度诊断报告</h1>
        <div class="flex gap-3">
            <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">
                <i data-lucide="printer" class="w-4 h-4 mr-2"></i>
                打印报告
            </button>
            <a href="{{ route('admin.diagnosis.show', $task) }}"
               class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                返回详情
            </a>
        </div>
    </div>

    {{-- 报告概览 --}}
    <div class="bg-gradient-to-r from-purple-600 to-blue-600 rounded-lg shadow-lg p-8 text-white">
        <h2 class="text-2xl font-bold mb-6">诊断概览</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="text-center">
                <p class="text-sm opacity-80">主关键词</p>
                <p class="mt-2 text-xl font-bold">{{ $task->main_keyword }}</p>
            </div>
            <div class="text-center">
                <p class="text-sm opacity-80">品牌名称</p>
                <p class="mt-2 text-xl font-bold">{{ $task->brand_name ?? '-' }}</p>
            </div>
            <div class="text-center">
                <p class="text-sm opacity-80">可见度评分</p>
                <p class="mt-2 text-4xl font-bold">{{ $task->visibility_rate }}%</p>
            </div>
            <div class="text-center">
                <p class="text-sm opacity-80">检测平台</p>
                <p class="mt-2 text-xl font-bold">{{ count($task->platforms) }} 个</p>
            </div>
        </div>
    </div>

    {{-- 详细统计 --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i data-lucide="search" class="w-6 h-6"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">总查询数</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $task->total_queries }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i data-lucide="check-circle" class="w-6 h-6"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">品牌提及数</p>
                    <p class="text-2xl font-bold text-green-600">{{ $task->brand_mentioned }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- 各平台结果 --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">各平台检测结果</h3>
        </div>
        <div class="p-6">
            @php
                $platformResults = $task->results->groupBy('platform');
            @endphp
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($task->platforms as $platform)
                <div class="border rounded-lg p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="font-medium text-gray-900">{{ $platforms[$platform] ?? $platform }}</h4>
                        @php
                            $platformData = $platformResults->get($platform, collect());
                            $mentioned = $platformData->filter(function($r) { return $r->brand_mentioned; })->count();
                            $total = $platformData->count();
                        @endphp
                        @if($total > 0)
                            <span class="{{ $mentioned > 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $mentioned }}/{{ $total }}
                            </span>
                        @endif
                    </div>
                    @if($platformData->isNotEmpty())
                        <div class="space-y-2">
                            @foreach($platformData->take(5) as $result)
                            <div class="text-sm {{ $result->brand_mentioned ? 'text-green-600' : 'text-gray-500' }}">
                                <i data-lucide="{{ $result->brand_mentioned ? 'check' : 'x' }}" class="w-4 h-4 inline mr-1"></i>
                                {{ Str::limit($result->query, 30) }}
                            </div>
                            @endforeach
                            @if($platformData->count() > 5)
                                <p class="text-xs text-gray-400">还有 {{ $platformData->count() - 5 }} 条结果...</p>
                            @endif
                        </div>
                    @else
                        <p class="text-sm text-gray-400">暂无数据</p>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- 优化建议 --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">优化建议</h3>
        <div class="space-y-4">
            @if($task->visibility_rate < 30)
                <div class="flex items-start p-4 bg-red-50 rounded-lg">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-red-600 mr-3 shrink-0 mt-0.5"></i>
                    <div>
                        <p class="font-medium text-red-800">可见度较低</p>
                        <p class="text-sm text-red-600 mt-1">您的品牌在AI搜索引擎中的可见度较低，建议加强GEO优化工作。</p>
                    </div>
                </div>
            @elseif($task->visibility_rate < 70)
                <div class="flex items-start p-4 bg-yellow-50 rounded-lg">
                    <i data-lucide="info" class="w-5 h-5 text-yellow-600 mr-3 shrink-0 mt-0.5"></i>
                    <div>
                        <p class="font-medium text-yellow-800">可见度中等</p>
                        <p class="text-sm text-yellow-600 mt-1">您的品牌在AI搜索引擎中有一定可见度，仍有提升空间。</p>
                    </div>
                </div>
            @else
                <div class="flex items-start p-4 bg-green-50 rounded-lg">
                    <i data-lucide="check-circle" class="w-5 h-5 text-green-600 mr-3 shrink-0 mt-0.5"></i>
                    <div>
                        <p class="font-medium text-green-800">可见度良好</p>
                        <p class="text-sm text-green-600 mt-1">您的品牌在AI搜索引擎中具有较好的可见度，继续保持。</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
