@extends('admin.layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">新建AI可见度诊断</h1>
        <a href="{{ route('admin.diagnosis.index') }}"
           class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            返回列表
        </a>
    </div>

    <form action="{{ route('admin.diagnosis.store') }}" method="POST" class="bg-white rounded-lg shadow p-6 space-y-6">
        @csrf

        {{-- 基本信息 --}}
        <div class="border-b border-gray-200 pb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">基本信息</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">主关键词 <span class="text-red-500">*</span></label>
                    <input type="text" name="main_keyword" value="{{ old('main_keyword') }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           placeholder="例如：geo搜索优化" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">品牌名称</label>
                    <input type="text" name="brand_name" value="{{ old('brand_name') }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           placeholder="例如：小汇萃">
                </div>
            </div>
        </div>

        {{-- 查询组合 --}}
        <div class="border-b border-gray-200 pb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">查询组合</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">A列：前缀/地域</label>
                    <input type="text" name="column_a" value="{{ old('column_a') }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           placeholder="例如：北京,上海,深圳（逗号分隔，可留空）">
                    <p class="mt-1 text-sm text-gray-500">可选，多个值用逗号分隔</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">B列：形容词</label>
                    <input type="text" name="column_b" value="{{ old('column_b') }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           placeholder="例如：最好的,专业的,靠谱的（逗号分隔，可留空）">
                    <p class="mt-1 text-sm text-gray-500">可选，多个值用逗号分隔</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">C列：主词 <span class="text-red-500">*</span></label>
                    <input type="text" name="column_c" value="{{ old('column_c') }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           placeholder="例如：geo搜索优化" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">D列：目标词 <span class="text-red-500">*</span></label>
                    <input type="text" name="column_d" value="{{ old('column_d') }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           placeholder="例如：公司,服务商,工具" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">E列：推荐词</label>
                    <input type="text" name="column_e" value="{{ old('column_e') }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           placeholder="例如：哪家好,怎么选,推荐（逗号分隔，可留空）">
                    <p class="mt-1 text-sm text-gray-500">可选，多个值用逗号分隔</p>
                </div>
            </div>
        </div>

        {{-- 检测平台 --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-3">检测平台 <span class="text-red-500">*</span></label>
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
            <a href="{{ route('admin.diagnosis.index') }}"
               class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                取消
            </a>
            <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                <i data-lucide="eye" class="w-4 h-4 inline mr-1"></i>
                开始诊断
            </button>
        </div>
    </form>
</div>
@endsection
