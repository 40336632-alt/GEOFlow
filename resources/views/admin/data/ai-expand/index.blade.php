@extends('admin.layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">AI智能拓词</h1>
    </div>

    {{-- 功能说明 --}}
    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
        <div class="flex">
            <i data-lucide="sparkles" class="w-5 h-5 text-purple-600 mr-2 shrink-0 mt-0.5"></i>
            <div class="text-sm text-purple-800">
                <p class="font-medium">AI智能拓词功能说明：</p>
                <ul class="mt-1 list-disc list-inside space-y-1">
                    <li>输入主关键词，AI自动生成相关搜索问题</li>
                    <li>生成的关键词可用于AI写作任务的蒸馏训练</li>
                </ul>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.ai-expand.expand') }}" method="POST" class="bg-white rounded-lg shadow p-6 space-y-6">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">主关键词 <span class="text-red-500">*</span></label>
                <input type="text" name="keyword" value="{{ old('keyword') }}"
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                       placeholder="例如：geo搜索优化" required>
                <p class="mt-1 text-sm text-gray-500">输入您要拓展的核心关键词</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">品牌名称</label>
                <input type="text" name="brand_name" value="{{ old('brand_name') }}"
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                       placeholder="例如：小汇萃">
                <p class="mt-1 text-sm text-gray-500">可选，用于生成包含品牌的问题</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">生成数量</label>
                <select name="count" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="10" {{ old('count') == 10 ? 'selected' : '' }}>10个</option>
                    <option value="20" {{ old('count', 20) == 20 ? 'selected' : '' }}>20个</option>
                    <option value="30" {{ old('count') == 30 ? 'selected' : '' }}>30个</option>
                    <option value="50" {{ old('count') == 50 ? 'selected' : '' }}>50个</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">保存到关键词库</label>
                <select name="library_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">不保存</option>
                    @foreach($libraries as $library)
                        <option value="{{ $library->id }}" {{ old('library_id') == $library->id ? 'selected' : '' }}>
                            {{ $library->name }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-sm text-gray-500">选择后自动保存生成的关键词</p>
            </div>
        </div>

        <div class="flex justify-end pt-4 border-t">
            <button type="submit"
                    class="px-6 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                <i data-lucide="sparkles" class="w-4 h-4 inline mr-1"></i>
                开始拓词
            </button>
        </div>
    </form>
</div>
@endsection
