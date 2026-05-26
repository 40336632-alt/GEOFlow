@extends('admin.layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.browser-profiles.index') }}" class="text-gray-500 hover:text-gray-700">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">添加浏览器配置</h1>
    </div>

    <form method="POST" action="{{ route('admin.browser-profiles.store') }}" class="bg-white rounded-lg shadow">
        @csrf

        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">配置信息</h3>
        </div>

        <div class="px-6 py-4 space-y-4">
            <div>
                <label for="platform" class="block text-sm font-medium text-gray-700 mb-2">
                    平台 <span class="text-red-500">*</span>
                </label>
                <select name="platform" id="platform" required
                        class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- 请选择平台 --</option>
                    @foreach($platforms as $key => $name)
                        <option value="{{ $key }}" {{ old('platform') == $key ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
                @error('platform')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="profile_id" class="block text-sm font-medium text-gray-700 mb-2">
                    BitBrowser Profile ID <span class="text-red-500">*</span>
                </label>
                <input type="text" name="profile_id" id="profile_id" required value="{{ old('profile_id') }}"
                       class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                       placeholder="在BitBrowser中创建的配置文件ID">
                @error('profile_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="profile_name" class="block text-sm font-medium text-gray-700 mb-2">配置名称</label>
                <input type="text" name="profile_name" id="profile_name" value="{{ old('profile_name') }}"
                       class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                       placeholder="便于识别的名称，如'张三的抖音号'">
                @error('profile_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="account_name" class="block text-sm font-medium text-gray-700 mb-2">账号名称</label>
                <input type="text" name="account_name" id="account_name" value="{{ old('account_name') }}"
                       class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                       placeholder="平台账号名称">
                @error('account_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="daily_limit" class="block text-sm font-medium text-gray-700 mb-2">每日发布上限</label>
                <input type="number" name="daily_limit" id="daily_limit" value="{{ old('daily_limit', 10) }}" min="1" max="50"
                       class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                @error('daily_limit')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
            <a href="{{ route('admin.browser-profiles.index') }}"
               class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                取消
            </a>
            <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                添加配置
            </button>
        </div>
    </form>
</div>
@endsection