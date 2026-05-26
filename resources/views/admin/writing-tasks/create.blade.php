@extends('admin.layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.writing-tasks.index') }}" class="text-gray-500 hover:text-gray-700">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">创建写作任务</h1>
    </div>

    <form action="{{ route('admin.writing-tasks.store') }}" method="POST"
          class="bg-white rounded-lg shadow p-6 space-y-6">
        @csrf

        {{-- 任务名称 --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">任务名称 <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name') }}"
                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                   placeholder="例如：GEO优化文章批量写作" required>
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- 蒸馏训练词 --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">蒸馏训练词 <span class="text-red-500">*</span></label>
            <select name="keyword_library_id"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                <option value="">请选择关键词库</option>
                @foreach($keywordLibraries as $library)
                    <option value="{{ $library->id }}" {{ old('keyword_library_id') == $library->id ? 'selected' : '' }}>
                        {{ $library->name }} (关键词: {{ $library->keywords_count ?? 0 }})
                    </option>
                @endforeach
            </select>
            @error('keyword_library_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- 文章分类 --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">文章分类</label>
            <select name="category_id"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">自动创建分类</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-gray-500">留空将根据任务名称自动创建分类</p>
        </div>

        {{-- 画像图库 --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">画像图库</label>
            <select name="image_library_id"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">不使用图库</option>
                @foreach($imageLibraries as $library)
                    <option value="{{ $library->id }}" {{ old('image_library_id') == $library->id ? 'selected' : '' }}>
                        {{ $library->name }} (图片: {{ $library->images_count }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- 文章配图数量 --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">文章配图: <span id="imageCountValue">{{ old('image_count', 2) }}</span> 张</label>
            <input type="range" name="image_count" min="0" max="10" value="{{ old('image_count', 2) }}"
                   class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                   oninput="document.getElementById('imageCountValue').textContent = this.value">
        </div>

        {{-- 企业知识库 --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">企业知识库</label>
            <select name="knowledge_base_id"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">不使用知识库</option>
                @foreach($knowledgeBases as $kb)
                    <option value="{{ $kb->id }}" {{ old('knowledge_base_id') == $kb->id ? 'selected' : '' }}>
                        {{ $kb->name }} ({{ $kb->company_name }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- 写作指令 --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">写作指令</label>
            <select name="instruction_id"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">使用默认指令</option>
                @foreach($instructions as $instruction)
                    <option value="{{ $instruction->id }}" {{ old('instruction_id') == $instruction->id ? 'selected' : '' }}>
                        {{ $instruction->name }} ({{ $instruction->type === 'article' ? '文章创作' : ($instruction->type === 'title' ? '标题创作' : '流量复刻') }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- 创作上限 --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">创作上限 <span class="text-red-500">*</span></label>
            <input type="number" name="max_articles" value="{{ old('max_articles', 1) }}" min="1" max="100"
                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            <p class="mt-1 text-xs text-gray-500">每个蒸馏问题生成一篇文章，最多100篇</p>
        </div>

        {{-- 提交按钮 --}}
        <div class="flex justify-end gap-3 pt-4 border-t">
            <a href="{{ route('admin.writing-tasks.index') }}"
               class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                取消
            </a>
            <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                创建任务
            </button>
        </div>
    </form>
</div>
@endsection
