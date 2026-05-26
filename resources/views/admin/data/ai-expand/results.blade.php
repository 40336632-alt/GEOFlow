@extends('admin.layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">拓词结果</h1>
        <a href="{{ route('admin.ai-expand.index') }}"
           class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            重新拓词
        </a>
    </div>

    {{-- 拓词信息 --}}
    <div class="bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-500">主关键词</label>
                <p class="mt-1 text-lg text-gray-900">{{ $keyword }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500">品牌名称</label>
                <p class="mt-1 text-lg text-gray-900">{{ $brandName ?? '-' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500">生成结果</label>
                <p class="mt-1 text-lg">
                    <span class="text-green-600 font-medium">{{ count($expandedKeywords) }} 个关键词</span>
                    @if($savedCount > 0)
                        <span class="text-sm text-gray-500 ml-2">(已保存 {{ $savedCount }} 个)</span>
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- 关键词列表 --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">生成的关键词</h3>
            <button onclick="copyAllKeywords()" class="text-sm text-blue-600 hover:text-blue-800">
                <i data-lucide="copy" class="w-4 h-4 inline mr-1"></i>
                复制全部
            </button>
        </div>
        <div class="p-6">
            <div id="keywords-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($expandedKeywords as $index => $kw)
                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm text-gray-500 w-6">{{ $index + 1 }}</span>
                    <span class="text-sm text-gray-900 flex-1">{{ $kw }}</span>
                    <button onclick="copyKeyword('{{ $kw }}')" class="text-gray-400 hover:text-gray-600">
                        <i data-lucide="copy" class="w-4 h-4"></i>
                    </button>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- 保存到关键词库 --}}
    @if(!$libraryId)
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">保存到关键词库</h3>
        <form action="{{ route('admin.ai-expand.expand') }}" method="POST" class="flex gap-4">
            @csrf
            <input type="hidden" name="keyword" value="{{ $keyword }}">
            <input type="hidden" name="brand_name" value="{{ $brandName }}">
            <input type="hidden" name="count" value="{{ count($expandedKeywords) }}">
            <select name="library_id" class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">选择关键词库</option>
                @foreach($libraries as $library)
                    <option value="{{ $library->id }}">{{ $library->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                保存
            </button>
        </form>
    </div>
    @endif
</div>

<script>
function copyKeyword(keyword) {
    navigator.clipboard.writeText(keyword).then(() => {
        // Could add a toast notification here
    });
}

function copyAllKeywords() {
    const keywords = @json($expandedKeywords);
    navigator.clipboard.writeText(keywords.join('\n')).then(() => {
        // Could add a toast notification here
    });
}
</script>
@endsection
