@extends('admin.layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.browser-profiles.index') }}" class="text-gray-500 hover:text-gray-700">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">编辑浏览器配置</h1>
    </div>

    <form method="POST" action="{{ route('admin.browser-profiles.update', $profile) }}" class="bg-white rounded-lg shadow">
        @csrf
        @method('PUT')

        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">配置信息</h3>
        </div>

        <div class="px-6 py-4 space-y-4">
            <div>
                <label for="platform" class="block text-sm font-medium text-gray-700 mb-2">平台</label>
                <select name="platform" id="platform"
                        class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    @foreach($platforms as $key => $name)
                        <option value="{{ $key }}" {{ old('platform', $profile->platform) == $key ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
                @error('platform')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="profile_id" class="block text-sm font-medium text-gray-700 mb-2">BitBrowser Profile ID</label>
                <input type="text" name="profile_id" id="profile_id" required value="{{ old('profile_id', $profile->profile_id) }}"
                       class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                @error('profile_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="profile_name" class="block text-sm font-medium text-gray-700 mb-2">配置名称</label>
                <input type="text" name="profile_name" id="profile_name" value="{{ old('profile_name', $profile->profile_name) }}"
                       class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                @error('profile_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="account_name" class="block text-sm font-medium text-gray-700 mb-2">账号名称</label>
                <input type="text" name="account_name" id="account_name" value="{{ old('account_name', $profile->account_name) }}"
                       class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                @error('account_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="daily_limit" class="block text-sm font-medium text-gray-700 mb-2">每日发布上限</label>
                <input type="number" name="daily_limit" id="daily_limit" value="{{ old('daily_limit', $profile->daily_limit) }}" min="1" max="50"
                       class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                @error('daily_limit')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">状态</label>
                <select name="status" id="status"
                        class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="authorized" {{ old('status', $profile->status) == 'authorized' ? 'selected' : '' }}>已授权</option>
                    <option value="pending" {{ old('status', $profile->status) == 'pending' ? 'selected' : '' }}>待授权</option>
                    <option value="disabled" {{ old('status', $profile->status) == 'disabled' ? 'selected' : '' }}>已禁用</option>
                </select>
                @error('status')
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
                保存修改
            </button>
        </div>
    </form>
</div>
@endsection