@extends('admin.layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">批量复刻</h1>
        <a href="{{ route('admin.viral.index') }}"
           class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            返回列表
        </a>
    </div>

    <form action="{{ route('admin.viral.batchStore') }}" method="POST" class="bg-white rounded-lg shadow p-6 space-y-6">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">文章链接 <span class="text-red-500">*</span></label>
            <textarea name="urls" rows="10"
                      class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      placeholder="每行一个链接&#10;https://example.com/article1&#10;https://example.com/article2&#10;https://example.com/article3"
                      required>{{ old('urls') }}</textarea>
            <p class="mt-1 text-sm text-gray-500">每行输入一个文章链接，系统将自动归类并改写</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">文章分类</label>
                <select name="category_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">自动创建</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">图库选择</label>
                <select name="image_library_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">不使用</option>
                    @foreach($imageLibraries as $library)
                        <option value="{{ $library->id }}" {{ old('image_library_id') == $library->id ? 'selected' : '' }}>
                            {{ $library->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">改写指令</label>
                <select name="instruction_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">使用默认</option>
                    @foreach($instructions as $instruction)
                        <option value="{{ $instruction->id }}" {{ old('instruction_id') == $instruction->id ? 'selected' : '' }}>
                            {{ $instruction->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t">
            <a href="{{ route('admin.viral.index') }}"
               class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                取消
            </a>
            <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                <i data-lucide="layers" class="w-4 h-4 inline mr-1"></i>
                批量归类
            </button>
        </div>
    </form>
</div>
@endsection
