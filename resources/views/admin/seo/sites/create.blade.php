@extends('admin.layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.seo.sites.index') }}" class="text-gray-500 hover:text-gray-700">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">添加站点</h1>
    </div>

    <form method="POST" action="{{ route('admin.seo.sites.store') }}" class="bg-white rounded-lg shadow">
        @csrf

        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">站点信息</h3>
        </div>

        <div class="px-6 py-4 space-y-4">
            <div>
                <label for="domain" class="block text-sm font-medium text-gray-700 mb-2">
                    域名 <span class="text-red-500">*</span>
                </label>
                <input type="text" name="domain" id="domain" required value="{{ old('domain') }}"
                       class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                       placeholder="example.com">
                @error('domain')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="site_type" class="block text-sm font-medium text-gray-700 mb-2">站点类型</label>
                <select name="site_type" id="site_type"
                        class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- 请选择 --</option>
                    @foreach($siteTypes as $key => $name)
                        <option value="{{ $key }}" {{ old('site_type') == $key ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
                @error('site_type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="remark" class="block text-sm font-medium text-gray-700 mb-2">备注</label>
                <textarea name="remark" id="remark" rows="3"
                          class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                          placeholder="可选备注信息">{{ old('remark') }}</textarea>
                @error('remark')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
            <a href="{{ route('admin.seo.sites.index') }}"
               class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                取消
            </a>
            <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                添加站点
            </button>
        </div>
    </form>
</div>
@endsection