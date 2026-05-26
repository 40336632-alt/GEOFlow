@extends('admin.layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">AI收录查询</h1>
        <a href="{{ route('admin.index-check.create') }}"
           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
            <i data-lucide="search" class="w-4 h-4 mr-2"></i>
            新建查询
        </a>
    </div>

    {{-- 平台说明 --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex">
            <i data-lucide="info" class="w-5 h-5 text-blue-600 mr-2 shrink-0 mt-0.5"></i>
            <div class="text-sm text-blue-800">
                <p class="font-medium">支持查询以下AI平台的收录情况：</p>
                <p class="mt-1">{{ implode('、', array_values($platforms)) }}</p>
            </div>
        </div>
    </div>

    {{-- 查询列表 --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">序号</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">查询问题</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">品牌名称</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">查询平台</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">收录结果</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">查询时间</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">操作</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($checks as $index => $check)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $checks->firstItem() + $index }}</td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                        <a href="{{ route('admin.index-check.show', $check) }}" class="text-blue-600 hover:text-blue-800">
                            {{ Str::limit($check->question, 50) }}
                        </a>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $check->brand_name ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        <div class="flex flex-wrap gap-1">
                            @foreach($check->platforms as $platform)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ $platforms[$platform] ?? $platform }}
                                </span>
                            @endforeach
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm">
                        @if($check->total_indexed > 0)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                {{ $check->total_indexed }}/{{ count($check->platforms) }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                0/{{ count($check->platforms) }}
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $check->created_at->format('Y-m-d H:i') }}
                    </td>
                    <td class="px-6 py-4 text-right text-sm">
                        <form action="{{ route('admin.index-check.destroy', $check) }}" method="POST" class="inline"
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
                        暂无收录查询记录
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $checks->links() }}
    </div>
</div>
@endsection
