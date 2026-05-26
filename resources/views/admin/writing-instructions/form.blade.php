@extends('admin.layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.writing-instructions.index') }}" class="text-gray-500 hover:text-gray-700">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">{{ $instruction ? '编辑写作指令' : '添加写作指令' }}</h1>
    </div>

    <form action="{{ $instruction ? route('admin.writing-instructions.update', $instruction) : route('admin.writing-instructions.store') }}"
          method="POST"
          class="bg-white rounded-lg shadow p-6 space-y-6">
        @csrf
        @if($instruction)
            @method('PUT')
        @endif

        {{-- 指令名称 --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">指令名称 <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $instruction->name ?? '') }}"
                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                   required>
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- 创作类型 --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">创作类型 <span class="text-red-500">*</span></label>
            <div class="flex gap-4">
                <label class="flex items-center gap-2">
                    <input type="radio" name="type" value="article"
                           {{ old('type', $instruction->type ?? 'article') === 'article' ? 'checked' : '' }}
                           class="text-blue-600 focus:ring-blue-500">
                    <span class="text-sm text-gray-700">文章创作</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="radio" name="type" value="title"
                           {{ old('type', $instruction->type ?? '') === 'title' ? 'checked' : '' }}
                           class="text-blue-600 focus:ring-blue-500">
                    <span class="text-sm text-gray-700">标题创作</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="radio" name="type" value="replication"
                           {{ old('type', $instruction->type ?? '') === 'replication' ? 'checked' : '' }}
                           class="text-blue-600 focus:ring-blue-500">
                    <span class="text-sm text-gray-700">流量复刻</span>
                </label>
            </div>
            @error('type')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- 指令内容 --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">指令内容 <span class="text-red-500">*</span></label>
            <textarea name="content" rows="15"
                      class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 font-mono text-sm"
                      placeholder="输入AI写作指令内容...">{{ old('content', $instruction->content ?? '') }}</textarea>
            @error('content')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-gray-500">
                提示：使用 Markdown 格式编写指令，AI会按照此指令生成内容
            </p>
        </div>

        {{-- 是否默认 --}}
        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_default" value="1"
                   {{ old('is_default', $instruction->is_default ?? false) ? 'checked' : '' }}
                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            <label class="text-sm text-gray-700">设为默认指令</label>
        </div>

        {{-- 提交按钮 --}}
        <div class="flex justify-end gap-3 pt-4 border-t">
            <a href="{{ route('admin.writing-instructions.index') }}"
               class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                取消
            </a>
            <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                {{ $instruction ? '更新指令' : '创建指令' }}
            </button>
        </div>
    </form>

    {{-- 预置模板参考 --}}
    @if(!$instruction)
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">预置模板参考</h2>
        <div class="space-y-4">
            <details class="group">
                <summary class="cursor-pointer text-sm font-medium text-blue-600 hover:text-blue-800">
                    文章创作指令模板
                </summary>
                <div class="mt-2 p-4 bg-gray-50 rounded-lg text-sm text-gray-700 font-mono whitespace-pre-wrap">#请根据以下要求，为我创作一篇自媒体热门文章：
##文章类型：自媒体文章
1. 内容要有具体数据和案例支撑；
2. 分点论述，每点都要有实操建议；
3. 适当加入自己的观点和思考；
4. 不要输出其他文章无关的信息；

##限制
1、不要加网址跟电话等违规；
2、禁止出现AI分析的话术跟语句，直接写出文章结果；
3、不要输出其他文章内容无关的信息，不限于文章字数、提示词分析等无关信息；
4、字数要求1000到2000字（不要在文章内显示本文有多少字）；
5、禁止虚构数据跟资料；
6、不要违反广告法；</div>
            </details>
            <details class="group">
                <summary class="cursor-pointer text-sm font-medium text-blue-600 hover:text-blue-800">
                    标题创作指令模板
                </summary>
                <div class="mt-2 p-4 bg-gray-50 rounded-lg text-sm text-gray-700 font-mono whitespace-pre-wrap">根据以下关键词，生成5个吸引人的自媒体标题：
1. 标题要有悬念感或解决痛点
2. 包含数字或对比
3. 字数控制在15-30字
4. 不要标题党，内容要真实</div>
            </details>
            <details class="group">
                <summary class="cursor-pointer text-sm font-medium text-blue-600 hover:text-blue-800">
                    流量复刻指令模板
                </summary>
                <div class="mt-2 p-4 bg-gray-50 rounded-lg text-sm text-gray-700 font-mono whitespace-pre-wrap">请根据原文内容，重新创作一篇相似主题的文章：
1. 保留原文的核心观点和数据
2. 用不同的表达方式重新组织
3. 增加自己的分析和见解
4. 确保内容原创，不抄袭
5. 字数1000-2000字</div>
            </details>
        </div>
    </div>
    @endif
</div>
@endsection
