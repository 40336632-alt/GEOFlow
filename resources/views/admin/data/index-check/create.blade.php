@extends('admin.layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">新建收录查询</h1>
        <a href="{{ route('admin.index-check.index') }}"
           class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            返回列表
        </a>
    </div>

    <form action="{{ route('admin.index-check.store') }}" method="POST" class="bg-white rounded-lg shadow p-6 space-y-6">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">查询问题 <span class="text-red-500">*</span></label>
            <input type="text" name="question" value="{{ old('question') }}"
                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                   placeholder="例如：geo搜索优化哪家好" required>
            <p class="mt-1 text-sm text-gray-500">输入用户可能会在AI搜索引擎中提问的问题</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">品牌名称</label>
            <input type="text" name="brand_name" value="{{ old('brand_name') }}"
                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                   placeholder="例如：小汇萃">
            <p class="mt-1 text-sm text-gray-500">输入您的品牌名称，用于检测AI回答中是否提及</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-3">查询平台 <span class="text-red-500">*</span></label>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                @foreach($platforms as $key => $name)
                <label class="relative flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50
                              {{ in_array($key, old('platforms', [])) ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }}">
                    <input type="checkbox" name="platforms[]" value="{{ $key }}"
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                           {{ in_array($key, old('platforms', [])) ? 'checked' : '' }}>
                    <span class="ml-2 text-sm text-gray-700">{{ $name }}</span>
                </label>
                @endforeach
            </div>
            @error('platforms')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t">
            <a href="{{ route('admin.index-check.index') }}"
               class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                取消
            </a>
            <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                <i data-lucide="search" class="w-4 h-4 inline mr-1"></i>
                开始查询
            </button>
        </div>
    </form>
</div>
@endsection
