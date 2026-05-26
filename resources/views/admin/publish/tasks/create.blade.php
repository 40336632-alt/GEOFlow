@extends('admin.layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.publish-tasks.index') }}" class="text-gray-500 hover:text-gray-700">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <h1 class="text-2xl font-bold text-gray-900">创建发布任务</h1>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.publish-tasks.store') }}" class="bg-white rounded-lg shadow">
        @csrf

        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">选择文章</h3>
        </div>

        <div class="px-6 py-4 space-y-4">
            <div>
                <label for="article_id" class="block text-sm font-medium text-gray-700 mb-2">选择要发布的文章 *</label>
                <select name="article_id" id="article_id" required
                        class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- 请选择文章 --</option>
                    @foreach($articles as $article)
                        <option value="{{ $article->id }}" {{ old('article_id') == $article->id ? 'selected' : '' }}>
                            {{ $article->title }}
                        </option>
                    @endforeach
                </select>
                @error('article_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">发布类型 *</label>
                <div class="grid grid-cols-3 gap-3">
                    @foreach($types as $key => $name)
                        <label class="relative cursor-pointer">
                            <input type="radio" name="publish_type" value="{{ $key }}" required
                                   class="sr-only peer"
                                   {{ old('publish_type') == $key ? 'checked' : '' }}>
                            <div class="border-2 border-gray-200 rounded-lg p-4 text-center transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700 hover:border-blue-300">
                                <span class="font-medium">{{ $name }}</span>
                            
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('publish_type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="platform" class="block text-sm font-medium text-gray-700 mb-2">发布平台 *</label>
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
                    浏览器配置
                    <span class="text-xs text-gray-500">（个人自媒体必选）</span>
                </label>
                <select name="profile_id" id="profile_id"
                        class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- 不使用浏览器配置 --</option>
                    @foreach($profiles as $profile)
                        <option value="{{ $profile->id }}" {{ old('profile_id') == $profile->id ? 'selected' : '' }}>
                            {{ $profile->platform }} - {{ $profile->profile_name ?? $profile->account_name }} ({{ $profile->today_published }}/{{ $profile->daily_limit }})
                        </option>
                    @endforeach
                </select>
                @if($profiles->isEmpty())
                    <p class="mt-1 text-sm text-amber-600">
                        <i data-lucide="alert-triangle" class="w-4 h-4 inline"></i>
                        暂无可用的浏览器配置，请先在浏览器管理中添加
                    </p>
                @endif
                @error('profile_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
            <a href="{{ route('admin.publish-tasks.index') }}"
               class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                取消
            </a>
            <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                创建发布任务
            </button>
        </div>
    </form>
</div>
@endsection
