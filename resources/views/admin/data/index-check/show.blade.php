@extends('admin.layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">收录查询详情</h1>
        <a href="{{ route('admin.index-check.index') }}"
           class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            返回列表
        </a>
    </div>

    {{-- 查询信息 --}}
    <div class="bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-500">查询问题</label>
                <p class="mt-1 text-lg text-gray-900">{{ $check->question }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500">品牌名称</label>
                <p class="mt-1 text-lg text-gray-900">{{ $check->brand_name ?? '-' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500">收录率</label>
                <p class="mt-1 text-lg">
                    <span class="{{ $check->index_rate > 50 ? 'text-green-600' : ($check->index_rate > 0 ? 'text-yellow-600' : 'text-gray-600') }}">
                        {{ $check->index_rate }}%
                    </span>
                    <span class="text-sm text-gray-500 ml-2">({{ $check->total_indexed }}/{{ count($check->platforms) }} 平台收录)</span>
                </p>
            </div>
        </div>
    </div>

    {{-- 各平台结果 --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">各平台收录结果</h3>
        </div>
        <div class="divide-y divide-gray-200">
            @foreach($check->details as $detail)
            <div class="px-6 py-4">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-medium text-gray-900">
                                {{ $platforms[$detail->platform] ?? $detail->platform }}
                            </span>
                            @if($detail->is_indexed)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    已收录
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    未收录
                                </span>
                            @endif
                        </div>
                        @if($detail->answer_text)
                            <div class="mt-2 text-sm text-gray-600 bg-gray-50 rounded-lg p-3">
                                {!! nl2br(e(Str::limit($detail->answer_text, 500))) !!}
                            </div>
                        @endif
                        @if($detail->error_message)
                            <div class="mt-2 text-sm text-red-600">
                                <i data-lucide="alert-circle" class="w-4 h-4 inline mr-1"></i>
                                {{ $detail->error_message }}
                            </div>
                        @endif
                    </div>
                    <div class="ml-4 text-sm text-gray-500">
                        {{ $detail->checked_at?->format('H:i:s') ?? '-' }}
                    </div>
                </div>
            </div>
            @endforeach

            @if($check->details->isEmpty())
            <div class="px-6 py-12 text-center text-gray-500">
                <i data-lucide="loader" class="w-8 h-8 mx-auto mb-2 text-gray-400 animate-spin"></i>
                <p>正在查询中，请稍后刷新页面...</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
