@extends('admin.layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">全网爆文复刻</h1>
        <div class="flex gap-3">
            <a href="{{ route('admin.viral.batch') }}"
               class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700">
                <i data-lucide="layers" class="w-4 h-4 mr-2"></i>
                批量复刻
            </a>
            <button onclick="document.getElementById('addModal').classList.remove('hidden')"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                添加复刻
            </button>
        </div>
    </div>

    {{-- 筛选 --}}
    <div class="flex gap-2">
        <a href="{{ route('admin.viral.index') }}"
           class="px-3 py-1.5 text-sm rounded-lg {{ !$currentStatus ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            全部
        </a>
        <a href="{{ route('admin.viral.index', ['status' => 'pending']) }}"
           class="px-3 py-1.5 text-sm rounded-lg {{ $currentStatus === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            待处理
        </a>
        <a href="{{ route('admin.viral.index', ['status' => 'completed']) }}"
           class="px-3 py-1.5 text-sm rounded-lg {{ $currentStatus === 'completed' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            已完成
        </a>
    </div>

    {{-- 表格 --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">序号</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">文章分类</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">链接</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">标题</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">状态</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">改写时间</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">操作</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($replications as $index => $replication)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $replications->firstItem() + $index }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $replication->category->name ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        <a href="{{ $replication->source_url }}" target="_blank" class="text-blue-600 hover:text-blue-800 truncate block max-w-xs">
                            {{ Str::limit($replication->source_url, 40) }}
                        </a>
                    </td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                        {{ $replication->rewritten_title ?? $replication->source_title ?? '-' }}
                    </td>
                    <td class="px-6 py-4 text-sm">
                        @if($replication->isPending())
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">待处理</span>
                        @elseif($replication->isRewriting())
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">改写中</span>
                        @elseif($replication->isCompleted())
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">已完成</span>
                        @elseif($replication->isFailed())
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">失败</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $replication->updated_at->format('Y-m-d H:i') }}
                    </td>
                    <td class="px-6 py-4 text-right text-sm">
                        <form action="{{ route('admin.viral.destroy', $replication) }}" method="POST" class="inline"
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
                        暂无爆文复刻任务
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $replications->links() }}
    </div>
</div>

{{-- 添加弹窗 --}}
<div id="addModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4">
        <div class="flex items-center justify-between p-4 border-b">
            <h3 class="text-lg font-semibold text-gray-900">添加爆文复刻</h3>
            <button onclick="document.getElementById('addModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form action="{{ route('admin.viral.store') }}" method="POST" class="p-4 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">文章链接 <span class="text-red-500">*</span></label>
                <input type="url" name="source_url" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                       placeholder="https://xxxx" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">文章分类</label>
                <select name="category_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">自动创建</option>
                    @foreach(\App\Models\Category::orderBy('name')->get() as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">图库选择</label>
                <select name="image_library_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">不使用</option>
                    @foreach(\App\Models\ImageLibrary::orderBy('name')->get() as $library)
                        <option value="{{ $library->id }}">{{ $library->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">改写指令</label>
                <select name="instruction_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">使用默认</option>
                    @foreach(\App\Models\WritingInstruction::ofType('replication')->orderBy('name')->get() as $instruction)
                        <option value="{{ $instruction->id }}">{{ $instruction->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t">
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    取消
                </button>
                <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                    归类文章
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
