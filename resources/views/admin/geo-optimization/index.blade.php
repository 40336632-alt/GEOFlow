@extends('admin.layouts.app')

@section('content')
    <div class="px-4 sm:px-0">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">GEO优化管理</h1>
                <p class="mt-1 text-sm text-gray-600">管理AutoGEO内容优化引擎，查看优化日志和效果</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.geo-optimization.rules') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <i data-lucide="settings" class="w-4 h-4 mr-2"></i>
                    优化规则
                </a>
                <button id="health-check-btn" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                    <i data-lucide="activity" class="w-4 h-4 mr-2"></i>
                    服务状态
                </button>
            </div>
        </div>

        {{-- Service Status --}}
        <div id="service-status" class="mb-6 rounded-lg p-4 {{ $serviceAvailable ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    @if ($serviceAvailable)
                        <i data-lucide="check-circle" class="w-5 h-5 text-green-400"></i>
                    @else
                        <i data-lucide="x-circle" class="w-5 h-5 text-red-400"></i>
                    @endif
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium {{ $serviceAvailable ? 'text-green-800' : 'text-red-800' }}">
                        AutoGEO服务：{{ $serviceAvailable ? '运行中' : '不可用' }}
                    </p>
                    <p class="mt-1 text-sm {{ $serviceAvailable ? 'text-green-700' : 'text-red-700' }}">
                        {{ $serviceAvailable ? '内容优化引擎已就绪，可以进行GEO优化。' : '请检查AutoGEO微服务是否已启动（默认端口5000）。' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-6">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i data-lucide="file-text" class="w-6 h-6 text-gray-400"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">总优化次数</dt>
                                <dd class="text-lg font-semibold text-gray-900">{{ number_format($stats['total']) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i data-lucide="check-circle" class="w-6 h-6 text-green-400"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">成功次数</dt>
                                <dd class="text-lg font-semibold text-green-600">{{ number_format($stats['success']) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i data-lucide="x-circle" class="w-6 h-6 text-red-400"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">失败次数</dt>
                                <dd class="text-lg font-semibold text-red-600">{{ number_format($stats['failed']) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i data-lucide="clock" class="w-6 h-6 text-blue-400"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">平均耗时</dt>
                                <dd class="text-lg font-semibold text-blue-600">{{ $stats['avg_duration'] }}s</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4">
                <form method="GET" class="flex items-end space-x-4">
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">状态</label>
                        <select name="status" id="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="">全部</option>
                            <option value="success" @selected($filters['status'] === 'success')>成功</option>
                            <option value="failed" @selected($filters['status'] === 'failed')>失败</option>
                            <option value="error" @selected($filters['status'] === 'error')>错误</option>
                        </select>
                    </div>
                    <div>
                        <label for="dataset" class="block text-sm font-medium text-gray-700">数据集</label>
                        <select name="dataset" id="dataset" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="">全部</option>
                            <option value="default" @selected($filters['dataset'] === 'default')>默认</option>
                            <option value="medical" @selected($filters['dataset'] === 'medical')>医疗</option>
                            <option value="ecommerce" @selected($filters['dataset'] === 'ecommerce')>电商</option>
                            <option value="research" @selected($filters['dataset'] === 'research')>学术</option>
                        </select>
                    </div>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                        筛选
                    </button>
                    <a href="{{ route('admin.geo-optimization.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        重置
                    </a>
                </form>
            </div>
        </div>

        {{-- Logs Table --}}
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">优化日志</h3>
            </div>
            @if ($logs->isEmpty())
                <div class="px-6 py-12 text-center">
                    <i data-lucide="inbox" class="w-12 h-12 text-gray-300 mx-auto"></i>
                    <p class="mt-2 text-sm text-gray-500">暂无优化日志</p>
                    <p class="mt-1 text-xs text-gray-400">启用任务的GEO优化功能后，优化记录将显示在这里</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">任务</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">数据集</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">引擎</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">状态</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">GEO分数</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">耗时</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">时间</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($logs as $log)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $log->id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $log->task?->name ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if ($log->dataset === 'medical') bg-pink-100 text-pink-800
                                            @elseif ($log->dataset === 'ecommerce') bg-yellow-100 text-yellow-800
                                            @elseif ($log->dataset === 'research') bg-purple-100 text-purple-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ $log->dataset }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $log->engine_llm }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($log->status === 'success')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">成功</span>
                                        @elseif ($log->status === 'failed')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">失败</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">错误</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if ($log->geo_scores && isset($log->geo_scores['geo_score']))
                                            <span class="font-semibold text-blue-600">{{ round($log->geo_scores['geo_score'] * 100) }}%</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $log->duration_seconds ? $log->duration_seconds . 's' : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $log->created_at?->format('m-d H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $logs->links() }}
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

            const healthBtn = document.getElementById('health-check-btn');
            const statusDiv = document.getElementById('service-status');

            if (healthBtn) {
                healthBtn.addEventListener('click', function () {
                    healthBtn.disabled = true;
                    healthBtn.textContent = '检查中...';

                    fetch('{{ route("admin.geo-optimization.health") }}')
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                statusDiv.className = 'mb-6 rounded-lg p-4 bg-green-50 border border-green-200';
                                statusDiv.innerHTML = `
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-green-800">AutoGEO服务：运行中</p>
                                            <p class="mt-1 text-sm text-green-700">内容优化引擎已就绪，可以进行GEO优化。</p>
                                        </div>
                                    </div>`;
                            } else {
                                statusDiv.className = 'mb-6 rounded-lg p-4 bg-red-50 border border-red-200';
                                statusDiv.innerHTML = `
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-red-800">AutoGEO服务：不可用</p>
                                            <p class="mt-1 text-sm text-red-700">请检查AutoGEO微服务是否已启动（默认端口5000）。</p>
                                        </div>
                                    </div>`;
                            }
                        })
                        .catch(() => {
                            alert('健康检查请求失败');
                        })
                        .finally(() => {
                            healthBtn.disabled = false;
                            healthBtn.innerHTML = '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>服务状态';
                            if (typeof lucide !== 'undefined') {
                                lucide.createIcons();
                            }
                        });
                });
            }
        });
    </script>
@endpush
