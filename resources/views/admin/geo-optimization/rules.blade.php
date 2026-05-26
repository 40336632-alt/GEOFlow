@extends('admin.layouts.app')

@section('content')
    <div class="px-4 sm:px-0">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.geo-optimization.index') }}" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">GEO优化规则</h1>
                    <p class="mt-1 text-sm text-gray-600">管理不同领域的GEO内容优化规则</p>
                </div>
            </div>
        </div>

        {{-- Create Rule Form --}}
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">添加新规则</h3>
                <p class="mt-1 text-sm text-gray-600">每行一条规则，用于指导AI如何优化内容以提高GEO分数</p>
            </div>
            <form method="POST" action="{{ route('admin.geo-optimization.rules.store') }}" class="px-6 py-4">
                @csrf
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">规则名称 *</label>
                        <input type="text" name="name" id="name" required value="{{ old('name') }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                               placeholder="例如：医疗内容优化规则">
                    </div>
                    <div>
                        <label for="dataset" class="block text-sm font-medium text-gray-700">适用数据集 *</label>
                        <select name="dataset" id="dataset" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="default" @selected(old('dataset') === 'default')>默认</option>
                            <option value="medical" @selected(old('dataset') === 'medical')>医疗</option>
                            <option value="ecommerce" @selected(old('dataset') === 'ecommerce')>电商</option>
                            <option value="research" @selected(old('dataset') === 'research')>学术</option>
                        </select>
                    </div>
                    <div class="lg:col-span-2">
                        <label for="rules" class="block text-sm font-medium text-gray-700">规则内容 *</label>
                        <textarea name="rules" id="rules" rows="3" required
                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="每行一条规则，例如：&#10;使用权威的语气和专业的术语&#10;引用可靠的数据来源&#10;包含常见问题解答">{{ old('rules') }}</textarea>
                    </div>
                </div>
                <div class="mt-4 flex justify-end">
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                        <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                        添加规则
                    </button>
                </div>
            </form>
        </div>

        {{-- Built-in Rules Info --}}
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-5 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i data-lucide="info" class="w-5 h-5 text-blue-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">内置规则说明</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <p>AutoGEO内置了4套基础规则（default/medical/ecommerce/research），自定义规则将与内置规则合并使用。</p>
                        <p class="mt-1">如果未添加自定义规则，系统将使用内置默认规则。</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Custom Rules List --}}
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">自定义规则</h3>
            </div>
            @if ($rules->isEmpty())
                <div class="px-6 py-12 text-center">
                    <i data-lucide="inbox" class="w-12 h-12 text-gray-300 mx-auto"></i>
                    <p class="mt-2 text-sm text-gray-500">暂无自定义规则</p>
                    <p class="mt-1 text-xs text-gray-400">添加自定义规则以覆盖或补充内置规则</p>
                </div>
            @else
                <div class="divide-y divide-gray-200">
                    @foreach ($rules as $rule)
                        <div class="px-6 py-4 {{ $rule->is_active ? '' : 'bg-gray-50' }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if ($rule->dataset === 'medical') bg-pink-100 text-pink-800
                                        @elseif ($rule->dataset === 'ecommerce') bg-yellow-100 text-yellow-800
                                        @elseif ($rule->dataset === 'research') bg-purple-100 text-purple-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ $rule->dataset }}
                                    </span>
                                    <h4 class="text-sm font-medium text-gray-900">{{ $rule->name }}</h4>
                                    @if (! $rule->is_active)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500">已禁用</span>
                                    @endif
                                </div>
                                <div class="flex items-center space-x-2">
                                    <form method="POST" action="{{ route('admin.geo-optimization.rules.toggle', ['ruleId' => $rule->id]) }}">
                                        @csrf
                                        <button type="submit" class="text-sm {{ $rule->is_active ? 'text-orange-600 hover:text-orange-700' : 'text-green-600 hover:text-green-700' }}">
                                            {{ $rule->is_active ? '禁用' : '启用' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.geo-optimization.rules.delete', ['ruleId' => $rule->id]) }}" onsubmit="return confirm('确定删除此规则？')">
                                        @csrf
                                        <button type="submit" class="text-sm text-red-600 hover:text-red-700">删除</button>
                                    </form>
                                </div>
                            </div>
                            <div class="mt-2">
                                <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                                    @foreach ($rule->rules as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="mt-2 text-xs text-gray-400">
                                创建于 {{ $rule->created_at?->format('Y-m-d H:i') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
@endpush
