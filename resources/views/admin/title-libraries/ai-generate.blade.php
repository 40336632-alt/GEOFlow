@extends('admin.layouts.app')

@section('content')
    <div class="px-4 sm:px-0">
        <div class="mb-8 flex items-center space-x-4">
            <a href="{{ route('admin.title-libraries.detail', ['libraryId' => (int) $library->id]) }}" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ __('admin.title_ai_generate.page_heading') }}</h1>
                <p class="mt-1 text-sm text-gray-600">{{ __('admin.title_ai_generate.page_subtitle', ['name' => $library->name]) }}</p>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">{{ __('admin.title_ai_generate.section.config') }}</h3>
                <p class="mt-1 text-sm text-gray-600">{{ __('admin.title_ai_generate.section.config_desc') }}</p>
            </div>
            <form method="POST" action="{{ route('admin.title-libraries.ai-generate.submit', ['libraryId' => (int) $library->id]) }}" class="p-6 space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.title_ai_generate.field.keyword_library') }}</label>
                        <select name="keyword_library_id" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 text-sm" required>
                            <option value="">{{ __('admin.title_ai_generate.option.select_keyword_library') }}</option>
                            @foreach ($keywordLibraries as $keywordLibrary)
                                <option value="{{ (int) $keywordLibrary->id }}" @selected((int) old('keyword_library_id') === (int) $keywordLibrary->id)>
                                    {{ $keywordLibrary->name }} ({{ (int) ($keywordLibrary->keyword_count ?? 0) }} {{ __('admin.title_ai_generate.option.keyword_count_suffix') }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.title_ai_generate.field.ai_model') }}</label>
                        <select name="ai_model_id" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 text-sm" required>
                            <option value="">{{ __('admin.title_ai_generate.option.select_ai_model') }}</option>
                            @foreach ($aiModels as $aiModel)
                                <option value="{{ (int) $aiModel->id }}" @selected((int) old('ai_model_id') === (int) $aiModel->id)>
                                    {{ $aiModel->name }} ({{ $aiModel->model_id }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.title_ai_generate.field.count') }}</label>
                        <input type="number" name="title_count" value="{{ old('title_count', 10) }}" min="1" max="50" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 text-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.title_ai_generate.field.style') }}</label>
                        <select name="title_style" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 text-sm" required>
                            @foreach (['professional', 'attractive', 'seo', 'creative', 'question'] as $style)
                                <option value="{{ $style }}" @selected(old('title_style', 'professional') === $style)>
                                    {{ __('admin.title_ai_generate.style.'.$style) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="border-t border-gray-200 pt-6">
                    <h4 class="text-sm font-semibold text-gray-900 mb-4">{{ __('admin.title_ai_generate.field.doctor_info_section') }}</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.title_ai_generate.field.city') }}</label>
                            <input type="text" name="doctor_city" value="{{ old('doctor_city') }}" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 text-sm" placeholder="{{ __('admin.title_ai_generate.placeholder.city') }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.title_ai_generate.field.topic') }}</label>
                            <input type="text" name="doctor_topic" value="{{ old('doctor_topic') }}" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 text-sm" placeholder="{{ __('admin.title_ai_generate.placeholder.topic') }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.title_ai_generate.field.extra_info') }}</label>
                            <input type="text" name="doctor_extra" value="{{ old('doctor_extra') }}" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 text-sm" placeholder="{{ __('admin.title_ai_generate.placeholder.extra_info') }}">
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.title_ai_generate.field.select_author') }}</label>
                        <select id="doctorAuthorSelect" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 text-sm">
                            <option value="">{{ __('admin.title_ai_generate.option.select_author') }}</option>
                            @foreach ($authors as $author)
                                <option value="{{ (int) $author->id }}"
                                    data-name="{{ $author->name }}"
                                    data-bio="{{ $author->bio ?? '' }}"
                                    @selected((int) old('doctor_author_id') === (int) $author->id)
                                >{{ $author->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.title_ai_generate.field.doctor_info') }}</label>
                        <textarea id="doctorInfoTextarea" name="doctor_info" rows="3" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 text-sm" placeholder="{{ __('admin.title_ai_generate.placeholder.doctor_info') }}">{{ old('doctor_info') }}</textarea>
                        <p class="mt-1 text-xs text-gray-400">{{ __('admin.title_ai_generate.placeholder.doctor_info_hint') }}</p>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.title_ai_generate.field.custom_prompt') }}</label>
                        <textarea name="custom_prompt" rows="2" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 text-sm" placeholder="{{ __('admin.title_ai_generate.placeholder.custom_prompt_extra') }}">{{ old('custom_prompt') }}</textarea>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="inline-flex items-center px-5 py-2.5 border border-transparent rounded-md text-sm font-medium text-white bg-purple-600 hover:bg-purple-700">
                        <i data-lucide="sparkles" class="w-4 h-4 mr-2"></i>
                        {{ __('admin.title_ai_generate.button.sync') }}
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-6 bg-purple-50 border border-purple-200 rounded-lg p-5">
            <h3 class="text-sm font-semibold text-purple-900">{{ __('admin.title_ai_generate.section.instructions') }}</h3>
            <ul class="mt-3 space-y-1 text-sm text-purple-800">
                <li>{{ __('admin.title_ai_generate.instructions.keyword_library') }}</li>
                <li>{{ __('admin.title_ai_generate.instructions.ai_model') }}</li>
                <li>{{ __('admin.title_ai_generate.instructions.count') }}</li>
                <li>{{ __('admin.title_ai_generate.instructions.style') }}</li>
                <li>{{ __('admin.title_ai_generate.instructions.custom_prompt') }}</li>
                <li>{{ __('admin.title_ai_generate.instructions.saved_titles') }}</li>
            </ul>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var select = document.getElementById('doctorAuthorSelect');
            var textarea = document.getElementById('doctorInfoTextarea');
            if (!select || !textarea) return;

            select.addEventListener('change', function () {
                var option = select.options[select.selectedIndex];
                if (!option || !option.value) {
                    return;
                }
                var name = option.getAttribute('data-name') || '';
                var bio = option.getAttribute('data-bio') || '';
                var info = name + (bio ? ' ' + bio : '');
                textarea.value = info;
            });
        });
    </script>
@endsection
