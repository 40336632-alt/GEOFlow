@extends('admin.layouts.app')

@section('content')
    <div class="px-4 sm:px-0">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">GEO优化看板</h1>
                <p class="mt-1 text-sm text-gray-600">AutoGEO内容优化进度总览，实时监控队列与优化效果</p>
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

        {{-- Queue & Article Progress --}}
        <div class="bg-white shadow rounded-lg mb-6" id="progress-panel">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">优化进度</h3>
                <div class="flex items-center space-x-2">
                    <span id="refresh-indicator" class="text-xs text-gray-400 hidden"><i data-lucide="loader-2" class="w-3 h-3 inline animate-spin"></i> 更新中...</span>
                    <button id="refresh-progress" class="inline-flex items-center px-3 py-1 border border-gray-300 rounded-md text-xs font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <i data-lucide="refresh-cw" class="w-3 h-3 mr-1"></i> 刷新
                    </button>
                </div>
            </div>
            <div class="px-6 py-5">
                {{-- Overall Progress Bar --}}
                @php
                    $pct = $articleStats['total'] > 0 ? round($articleStats['completed'] / $articleStats['total'] * 100) : 0;
                @endphp
                <div class="mb-6">
                    <div class="flex items-center justify-between text-sm text-gray-600 mb-2">
                        <span>总体进度：{{ $articleStats['completed'] }} / {{ $articleStats['total'] }} 篇</span>
                        <span class="font-medium">{{ $pct }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-blue-600 h-3 rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                    </div>
                </div>

                {{-- Stats Grid --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-4 mb-6" id="progress-stats">
                    <div class="bg-amber-50 rounded-lg p-4 border border-amber-200">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-amber-700 font-medium">队列中</span>
                            <span id="queue-count" class="text-2xl font-bold text-amber-600">{{ $queuePending }}</span>
                        </div>
                        <p class="text-xs text-amber-500 mt-1">等待执行的任务</p>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-blue-700 font-medium">待优化</span>
                            <span id="pending-count" class="text-2xl font-bold text-blue-600">{{ $articleStats['pending'] }}</span>
                        </div>
                        <p class="text-xs text-blue-500 mt-1">未开始的草稿</p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-green-700 font-medium">已完成</span>
                            <span id="completed-count" class="text-2xl font-bold text-green-600">{{ $articleStats['completed'] }}</span>
                        </div>
                        <p class="text-xs text-green-500 mt-1">优化成功的文章</p>
                    </div>
                    <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-red-700 font-medium">失败</span>
                            <span id="failed-count" class="text-2xl font-bold text-red-600">{{ $logStats['failed'] }}</span>
                        </div>
                        <p class="text-xs text-red-500 mt-1">优化失败的次数</p>
                    </div>
                </div>

                {{-- Per-task breakdown --}}
                @if ($perTaskStats->isNotEmpty())
                    <div class="border-t border-gray-100 pt-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-3">按任务统计</h4>
                        <div class="space-y-3">
                            @foreach ($perTaskStats as $ts)
                                @php $tpct = $ts->total > 0 ? round($ts->done / $ts->total * 100) : 0; @endphp
                                <div>
                                    <div class="flex items-center justify-between text-xs text-gray-600 mb-1">
                                        <span>{{ $ts->task?->name ?? '任务 #'.$ts->task_id }}</span>
                                        <span>{{ $ts->done }}/{{ $ts->total }} ({{ $tpct }}%)</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-indigo-500 h-2 rounded-full transition-all duration-500" style="width: {{ $tpct }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Recent Logs (real-time) --}}
        <div class="bg-white shadow rounded-lg mb-6" id="recent-logs-panel">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">最近优化记录</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="recent-logs-table">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">文章</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">状态</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">GEO分数</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">耗时</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">时间</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($logs as $log)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 text-sm text-gray-900 max-w-xs truncate">{{ $log->article?->title ?? '-' }}</td>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    @if ($log->status === 'success')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">成功</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">失败</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm">
                                    @if ($log->geo_scores && isset($log->geo_scores['geo_score']))
                                        <span class="font-semibold text-blue-600">{{ round($log->geo_scores['geo_score'] * 100) }}%</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">{{ $log->duration_seconds ? $log->duration_seconds.'s' : '-' }}</td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">{{ $log->created_at?->format('H:i:s') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-400">暂无优化记录</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-3 border-t border-gray-200 bg-gray-50">
                <a href="{{ route('admin.geo-optimization.index') }}" class="text-sm text-blue-600 hover:text-blue-700">查看全部日志 →</a>
            </div>
        </div>

        {{-- Full Logs Table --}}
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">全部优化日志</h3>
            </div>
            @if ($logs->isEmpty())
                <div class="px-6 py-12 text-center">
                    <i data-lucide="inbox" class="w-12 h-12 text-gray-300 mx-auto"></i>
                    <p class="mt-2 text-sm text-gray-500">暂无优化日志</p>
                    <p class="mt-1 text-xs text-gray-400">在文章列表中选择草稿 → 批量 GEO 优化 开始优化</p>
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
            if (typeof lucide !== 'undefined') lucide.createIcons();

            const progressUrl = '{{ route("admin.geo-optimization.progress") }}';
            const statusDiv = document.getElementById('service-status');
            const healthBtn = document.getElementById('health-check-btn');
            const refreshBtn = document.getElementById('refresh-progress');
            const indicator = document.getElementById('refresh-indicator');
            let autoRefreshTimer = null;

            function updateProgress(data) {
                const el = (id) => document.getElementById(id);
                if (el('queue-count')) el('queue-count').textContent = data.queue_pending;
                if (el('pending-count')) el('pending-count').textContent = data.article_stats.pending;
                if (el('completed-count')) el('completed-count').textContent = data.article_stats.completed;
                if (el('failed-count')) el('failed-count').textContent = data.log_stats.failed;

                const total = data.article_stats.total;
                const done = data.article_stats.completed;
                const pct = total > 0 ? Math.round(done / total * 100) : 0;
                const bar = document.querySelector('#progress-panel .bg-blue-600');
                if (bar) bar.style.width = pct + '%';
                const pctText = document.querySelector('#progress-panel .flex.items-center.justify-between .font-medium');
                if (pctText) pctText.textContent = pct + '%';
                const progressText = document.querySelector('#progress-panel .flex.items-center.justify-between.text-sm.text-gray-600 span');
                if (progressText) progressText.textContent = '总体进度：' + done + ' / ' + total + ' 篇';
            }

            function fetchProgress(silent) {
                if (!silent && indicator) {
                    indicator.classList.remove('hidden');
                }
                fetch(progressUrl)
                    .then(r => r.json())
                    .then(data => {
                        updateProgress(data);
                        if (indicator) indicator.classList.add('hidden');
                    })
                    .catch(() => { if (indicator) indicator.classList.add('hidden'); });
            }

            if (refreshBtn) {
                refreshBtn.addEventListener('click', function () { fetchProgress(false); });
            }

            // Auto-refresh every 8 seconds if queue has pending or pending articles > 0
            function scheduleAutoRefresh() {
                if (autoRefreshTimer) clearInterval(autoRefreshTimer);
                const pending = parseInt(document.getElementById('queue-count')?.textContent || '0') +
                               parseInt(document.getElementById('pending-count')?.textContent || '0');
                if (pending > 0) {
                    autoRefreshTimer = setInterval(() => fetchProgress(true), 8000);
                }
            }
            scheduleAutoRefresh();

            // Observer to restart auto-refresh when counts change
            const observer = new MutationObserver(() => scheduleAutoRefresh());
            ['queue-count', 'pending-count'].forEach(id => {
                const el = document.getElementById(id);
                if (el) observer.observe(el, { childList: true, characterData: true, subtree: true });
            });

            if (healthBtn) {
                healthBtn.addEventListener('click', function () {
                    healthBtn.disabled = true;
                    healthBtn.textContent = '检查中...';
                    fetch('{{ route("admin.geo-optimization.health") }}')
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                statusDiv.className = 'mb-6 rounded-lg p-4 bg-green-50 border border-green-200';
                                statusDiv.innerHTML = '<div class="flex items-center"><div class="flex-shrink-0"><svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div><div class="ml-3"><p class="text-sm font-medium text-green-800">AutoGEO服务：运行中</p><p class="mt-1 text-sm text-green-700">内容优化引擎已就绪，可以进行GEO优化。</p></div></div>';
                            } else {
                                statusDiv.className = 'mb-6 rounded-lg p-4 bg-red-50 border border-red-200';
                                statusDiv.innerHTML = '<div class="flex items-center"><div class="flex-shrink-0"><svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></div><div class="ml-3"><p class="text-sm font-medium text-red-800">AutoGEO服务：不可用</p><p class="mt-1 text-sm text-red-700">请检查AutoGEO微服务是否已启动（默认端口5000）。</p></div></div>';
                            }
                        })
                        .catch(() => { alert('健康检查请求失败'); })
                        .finally(() => {
                            healthBtn.disabled = false;
                            healthBtn.innerHTML = '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>服务状态';
                            if (typeof lucide !== 'undefined') lucide.createIcons();
                        });
                });
            }
        });
    </script>
@endpush