@extends('admin.layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">写作指令</h1>
        <a href="{{ route('admin.writing-instructions.create') }}"
           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
            添加指令
        </a>
    </div>

    {{-- 筛选 --}}
    <div class="flex gap-2">
        <a href="{{ route('admin.writing-instructions.index') }}"
           class="px-3 py-1.5 text-sm rounded-lg {{ !$currentType ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            全部
        </a>
        <a href="{{ route('admin.writing-instructions.index', ['type' => 'article']) }}"
           class="px-3 py-1.5 text-sm rounded-lg {{ $currentType === 'article' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            文章创作
        </a>
        <a href="{{ route('admin.writing-instructions.index', ['type' => 'title']) }}"
           class="px-3 py-1.5 text-sm rounded-lg {{ $currentType === 'title' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            标题创作
        </a>
        <a href="{{ route('admin.writing-instructions.index', ['type' => 'replication']) }}"
           class="px-3 py-1.5 text-sm rounded-lg {{ $currentType === 'replication' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            流量复刻
        </a>
    </div>

    {{-- 表格 --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">序号</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">指令名称</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">创作类型</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">默认</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">创建时间</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">操作</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($instructions as $index => $instruction)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $instructions->firstItem() + $index }}</td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $instruction->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        @php
                            $typeLabels = [
                                'article' => '文章创作',
                                'title' => '标题创作',
                                'replication' => '流量复刻',
                            ];
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($instruction->type === 'article') bg-green-100 text-green-800
                            @elseif($instruction->type === 'title') bg-blue-100 text-blue-800
                            @else bg-purple-100 text-purple-800
                            @endif">
                            {{ $typeLabels[$instruction->type] ?? $instruction->type }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        @if($instruction->is_default)
                            <span class="text-green-600">✓</span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $instruction->created_at->format('Y-m-d H:i') }}</td>
                    <td class="px-6 py-4 text-right text-sm space-x-2">
                        <a href="{{ route('admin.writing-instructions.edit', $instruction) }}"
                           class="text-blue-600 hover:text-blue-800">
                            <i data-lucide="pencil" class="w-4 h-4 inline"></i>
                        </a>
                        <form action="{{ route('admin.writing-instructions.destroy', $instruction) }}" method="POST" class="inline"
                              onsubmit="return confirm('确定删除该指令？')">
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
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        暂无写作指令，点击"添加指令"创建
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $instructions->links() }}
    </div>
</div>
@endsection
