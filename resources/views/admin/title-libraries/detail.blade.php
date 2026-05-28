@extends('admin.layouts.app')

@section('content')
    <div class="px-4 sm:px-0">
        <div class="mb-8 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.title-libraries.index') }}" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $library->name }}</h1>
                    <p class="mt-1 text-sm text-gray-600">{{ __('admin.title_detail.subtitle') }}</p>
                </div>
            </div>
            <div class="flex space-x-2">
                <button type="button" onclick="showImportModal()" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <i data-lucide="upload" class="w-4 h-4 mr-2"></i>
                    {{ __('admin.title_detail.import_batch') }}
                </button>
                <button type="button" onclick="showAddModal()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                    <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                    {{ __('admin.title_detail.add_title') }}
                </button>
                <a href="{{ route('admin.title-libraries.ai-generate', ['libraryId' => (int) $library->id]) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    <i data-lucide="zap" class="w-4 h-4 mr-2"></i>
                    {{ __('admin.title_detail.ai_generate') }}
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i data-lucide="list" class="h-6 w-6 text-green-600"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">{{ __('admin.title_detail.total_titles') }}</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $titles->total() }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i data-lucide="calendar" class="h-6 w-6 text-blue-600"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">{{ __('admin.title_detail.created_date') }}</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ optional($library->created_at)->format('Y-m-d') ?? '-' }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i data-lucide="trending-up" class="h-6 w-6 text-purple-600"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">{{ __('admin.title_detail.usage_total') }}</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $usageTotal }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">{{ __('admin.title_detail.list_title') }}</h3>
            </div>

            @if ($titles->isEmpty())
                <div class="px-6 py-8 text-center">
                    <i data-lucide="list" class="w-12 h-12 mx-auto text-gray-400 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('admin.title_detail.empty') }}</h3>
                    <p class="text-gray-500 mb-4">{{ __('admin.title_detail.empty_desc') }}</p>
                    <div class="flex justify-center space-x-2">
                        <button type="button" onclick="showAddModal()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                            {{ __('admin.title_detail.add_title') }}
                        </button>
                        <button type="button" onclick="showImportModal()" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            <i data-lucide="upload" class="w-4 h-4 mr-2"></i>
                            {{ __('admin.title_detail.import_batch') }}
                        </button>
                    </div>
                </div>
            @else
                <div class="px-6 py-3 border-b border-gray-200 bg-gray-50 flex items-center justify-between" id="batch-toolbar" style="display:none;">
                    <div class="flex items-center space-x-4">
                        <label class="inline-flex items-center space-x-2 text-sm text-gray-700">
                            <input type="checkbox" id="select-all" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                            <span>{{ __('admin.title_detail.select_all') }}</span>
                        </label>
                        <span class="text-sm text-gray-500" id="selected-count"></span>
                    </div>
                    <button type="button" onclick="batchDelete()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                        <i data-lucide="trash-2" class="w-4 h-4 mr-2"></i>
                        {{ __('admin.title_detail.batch_delete') }}
                    </button>
                </div>

                <div class="divide-y divide-gray-200">
                    @foreach ($titles as $title)
                        <div class="px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3 flex-1 min-w-0">
                                    <input type="checkbox" name="title_ids" value="{{ (int) $title->id }}" class="title-checkbox rounded border-gray-300 text-red-600 focus:ring-red-500">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center space-x-3">
                                            <h4 class="text-lg font-medium text-gray-900 break-all">{{ $title->title }}</h4>
                                            @if ((bool) $title->is_ai_generated)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                    <i data-lucide="zap" class="w-3 h-3 mr-1"></i>
                                                    {{ __('admin.title_detail.ai_badge') }}
                                                </span>
                                            @endif
                                            @if ((string) ($title->keyword ?? '') !== '')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                    {{ $title->keyword }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="mt-2 flex items-center space-x-4 text-sm text-gray-500">
                                            <span>{{ __('admin.title_detail.usage_count', ['count' => (int) ($title->used_count ?? 0)]) }}</span>
                                            <span>{{ __('admin.title_detail.created_at', ['value' => optional($title->created_at)->format('Y-m-d H:i') ?? '-']) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button type="button" onclick="deleteTitle({{ (int) $title->id }}, @js($title->title))" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700">
                                        <i data-lucide="trash-2" class="w-4 h-4 mr-1"></i>
                                        {{ __('admin.button.delete') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($titles->lastPage() > 1)
                    <div class="px-6 py-4 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                {{ __('admin.title_detail.pagination', ['start' => $titles->firstItem(), 'end' => $titles->lastItem(), 'total' => $titles->total()]) }}
                            </div>
                            <div>
                                {{ $titles->links() }}
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>

    <form method="POST" action="{{ route('admin.title-libraries.titles.delete', ['libraryId' => (int) $library->id]) }}" id="delete-title-form" class="hidden">
        @csrf
        <input type="hidden" name="title_ids[]" id="delete-title-id" value="">
    </form>

    <div id="add-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('admin.title_detail.modal_add') }}</h3>
                <form method="POST" action="{{ route('admin.title-libraries.titles.store', ['libraryId' => (int) $library->id]) }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('admin.title_detail.field_title') }}</label>
                            <input type="text" name="title" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm" placeholder="{{ __('admin.title_detail.placeholder_title') }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('admin.title_detail.field_keyword') }}</label>
                            <input type="text" name="keyword" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm" placeholder="{{ __('admin.title_detail.placeholder_keyword') }}">
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" onclick="hideAddModal()" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            {{ __('admin.button.cancel') }}
                        </button>
                        <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                            {{ __('admin.button.add') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="import-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-5 border w-2/3 max-w-2xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('admin.title_detail.modal_import') }}</h3>
                <form method="POST" action="{{ route('admin.title-libraries.import', ['libraryId' => (int) $library->id]) }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('admin.title_detail.field_titles') }}</label>
                            <textarea name="titles_text" rows="10" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm" placeholder="{{ __('admin.title_detail.placeholder_titles') }}"></textarea>
                        </div>
                        <div class="text-sm text-gray-500">
                            <p class="mb-2">{{ __('admin.title_detail.import_format_title') }}</p>
                            <ul class="list-disc list-inside space-y-1">
                                <li>{{ __('admin.title_detail.import_format_line') }}</li>
                                <li>{{ __('admin.title_detail.import_format_pipe') }}</li>
                                <li>{{ __('admin.title_detail.import_format_dedupe') }}</li>
                            </ul>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" onclick="hideImportModal()" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            {{ __('admin.button.cancel') }}
                        </button>
                        <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                            {{ __('admin.title_detail.import_button') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function showAddModal() {
            document.getElementById('add-modal').classList.remove('hidden');
        }

        function hideAddModal() {
            document.getElementById('add-modal').classList.add('hidden');
        }

        function showImportModal() {
            document.getElementById('import-modal').classList.remove('hidden');
        }

        function hideImportModal() {
            document.getElementById('import-modal').classList.add('hidden');
        }

        function deleteTitle(titleId, titleName) {
            const confirmed = confirm(@json(__('admin.title_detail.confirm_delete', ['name' => '{name}'])).replace('{name}', titleName));
            if (!confirmed) {
                return;
            }

            document.getElementById('delete-title-id').value = String(titleId);
            document.getElementById('delete-title-form').submit();
        }

        function getSelectedIds() {
            return Array.from(document.querySelectorAll('.title-checkbox:checked')).map(function (cb) {
                return cb.value;
            });
        }

        function updateToolbar() {
            var ids = getSelectedIds();
            var toolbar = document.getElementById('batch-toolbar');
            var counter = document.getElementById('selected-count');
            var selectAll = document.getElementById('select-all');
            var allCheckboxes = document.querySelectorAll('.title-checkbox');

            if (ids.length > 0) {
                toolbar.style.display = '';
                counter.textContent = @json(__('admin.title_detail.selected_count', ['count' => '{count}'])).replace('{count}', ids.length);
            } else {
                toolbar.style.display = 'none';
            }

            if (allCheckboxes.length > 0) {
                selectAll.checked = ids.length === allCheckboxes.length;
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            var selectAll = document.getElementById('select-all');
            var checkboxes = document.querySelectorAll('.title-checkbox');

            selectAll.addEventListener('change', function () {
                checkboxes.forEach(function (cb) {
                    cb.checked = selectAll.checked;
                });
                updateToolbar();
            });

            checkboxes.forEach(function (cb) {
                cb.addEventListener('change', updateToolbar);
            });
        });

        function batchDelete() {
            var ids = getSelectedIds();
            if (ids.length === 0) {
                return;
            }

            var msg = @json(__('admin.title_detail.confirm_batch_delete', ['count' => '{count}'])).replace('{count}', ids.length);
            if (!confirm(msg)) {
                return;
            }

            var form = document.getElementById('delete-title-form');
            var input = document.getElementById('delete-title-id');
            input.value = ids.join(',');
            form.submit();
        }

        window.onclick = function (event) {
            var addModal = document.getElementById('add-modal');
            var importModal = document.getElementById('import-modal');

            if (event.target === addModal) {
                hideAddModal();
            }

            if (event.target === importModal) {
                hideImportModal();
            }
        };
    </script>
@endpush
