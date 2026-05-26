@extends('admin.layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.seo.tasks.index') }}" class="text-gray-500 hover:text-gray-700">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">创建SEO发布任务</h1>
    </div>

    <form method="POST" action="{{ route('admin.seo.tasks.store') }}" class="bg-white rounded-lg shadow">
        @csrf

        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">任务信息</h3>
        </div>

        <div class="px-6 py-4 space-y-4">
            <div>
                <label for="site_id" class="block text-sm font-medium text-gray-700 mb-2">
                    目标站点 <span class="text-red-500">*</span>
                </label>
                <select name="site_id" id="site_id" required
                        class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- 请选择站点 --</option>
                    @foreach($sites as $site)
                        <option value="{{ $site->id }}" {{ old('site_id') == $site->id ? 'selected' : '' }}>
                            {{ $site->domain }}
                        </option>
                    @endforeach
                </select>
                @if($sites->isEmpty())
                    <p class="mt-1 text-sm text-amber-600">
                        <i data-lucide="alert-triangle" class="w-4 h-4 inline"></i>
                        暂无站点配置，请先添加站点
                    </p>
                @endif
                @error('site_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="article_id" class="block text-sm font-medium text-gray-700 mb-2">
                    选择文章 <span class="text-red-500">*</span>
                </label>
                <select name="article_id" id="article_id" required
                        class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- 请选择文章 --</option>
                    @foreach($articles as $article)
                        <option value="{{ $article->id }}" {{ old('article_id') == $article->id ? 'selected' : '' }}>
                            {{ Str::limit($article->title, 60) }}
                        </option>
                    @endforeach
                </select>
                @if($articles->isEmpty())
                    <p class="mt-1 text-sm text-amber-600">
                        <i data-lucide="alert-triangle" class="w-4 h-4 inline"></i>
                        暂无已发布的文章
                    </p>
                @endif
                @error('article_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
            <a href="{{ route('admin.seo.tasks.index') }}"
               class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                取消
            </a>
            <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700"
                    {{ $sites->isEmpty() || $articles->isEmpty() ? 'disabled' : '' }}>
                创建任务
            </button>
        </div>
    </form>
</div>
@endsection