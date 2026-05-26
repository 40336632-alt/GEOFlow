@extends('admin.layouts.app')

@section('content')
    <div class="px-4 sm:px-0">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">发布看板</h1>
                <p class="mt-1 text-sm text-gray-600">今日发布配额、账号状态、发布历史总览</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.publish-tasks.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                    <i data-lucide="plus" class="w-4 h-4 mr-2"></i> 新建发布任务
                </a>
                <a href="{{ route('admin.browser-profiles.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <i data-lucide="smartphone" class="w-4 h-4 mr-2"></i> 账号管理
                </a>
                <button type="button" onclick="openPublishModal()" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                    <i data-lucide="play" class="w-4 h-4 mr-2"></i> 执行批量发布
                </button>
            </div>
        </div>

        <div id="batch-status" class="hidden"></div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5 mb-6">
            <div class="bg-white shadow rounded-lg p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0"><i data-lucide="check-circle" class="w-6 h-6 text-green-400"></i></div>
                    <div class="ml-4 flex-1">
                        <dt class="text-sm font-medium text-gray-500 truncate">今日已发</dt>
                        <dd class="text-2xl font-semibold text-green-600">{{ $todayPublishes }}</dd>
                    </div>
                </div>
            </div>
            <div class="bg-white shadow rounded-lg p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0"><i data-lucide="clock" class="w-6 h-6 text-blue-400"></i></div>
                    <div class="ml-4 flex-1">
                        <dt class="text-sm font-medium text-gray-500 truncate">今日剩余</dt>
                        <dd class="text-2xl font-semibold text-blue-600">{{ $remainingSlots }}</dd>
                    </div>
                </div>
            </div>
            <div class="bg-white shadow rounded-lg p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0"><i data-lucide="layers" class="w-6 h-6 text-indigo-400"></i></div>
                    <div class="ml-4 flex-1">
                        <dt class="text-sm font-medium text-gray-500 truncate">总配额</dt>
                        <dd class="text-2xl font-semibold text-indigo-600">{{ $totalSlots }}</dd>
                    </div>
                </div>
            </div>
            <div class="bg-white shadow rounded-lg p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0"><i data-lucide="loader-2" class="w-6 h-6 text-amber-400"></i></div>
                    <div class="ml-4 flex-1">
                        <dt class="text-sm font-medium text-gray-500 truncate">待处理</dt>
                        <dd class="text-2xl font-semibold text-amber-600">{{ $pendingTasks }}</dd>
                    </div>
                </div>
            </div>
            <div class="bg-white shadow rounded-lg p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0"><i data-lucide="x-circle" class="w-6 h-6 text-red-400"></i></div>
                    <div class="ml-4 flex-1">
                        <dt class="text-sm font-medium text-gray-500 truncate">今日失败</dt>
                        <dd class="text-2xl font-semibold text-red-600">{{ $todayFailed }}</dd>
                    </div>
                </div>
            </div>
        </div>

        {{-- Weekly Chart + Accounts --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <div class="lg:col-span-1 bg-white shadow rounded-lg p-5">
                <h3 class="text-sm font-medium text-gray-700 mb-4">近7天发布量</h3>
                <div class="flex items-end justify-between gap-1" style="height: 120px;">
                    @foreach ($weeklyStats as $day)
                        @php
                            $maxCount = $weeklyStats->max('count');
                            $height = $maxCount > 0 ? max(8, round($day['count'] / $maxCount * 100)) : 0;
                        @endphp
                        <div class="flex-1 flex flex-col items-center justify-end h-full">
                            <span class="text-xs font-semibold text-gray-600 mb-1">{{ $day['count'] }}</span>
                            <div class="w-full bg-blue-500 rounded-t" style="height: {{ $height }}%; min-height: {{ $day['count'] > 0 ? '4px' : '0' }};"></div>
                            <span class="text-xs text-gray-400 mt-1">{{ $day['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="lg:col-span-2 bg-white shadow rounded-lg overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-sm font-medium text-gray-700">账号配额状态</h3>
                    <span class="text-xs text-gray-400">{{ $profiles->count() }} 个账号</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">窗口</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">平台</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">状态</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">配额</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">已发</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">剩余</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">最后使用</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($profiles as $p)
                                <tr class="hover:bg-gray-50 {{ $p['status'] !== 'authorized' ? 'opacity-60' : '' }}">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $p['account_name'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $p['platform_label'] }}</td>
                                    <td class="px-4 py-3 text-sm text-center">
                                        @if ($p['status'] === 'disabled')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">禁用</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">正常</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-center text-gray-700">{{ $p['daily_limit'] }}</td>
                                    <td class="px-4 py-3 text-sm text-center {{ $p['today_published'] > 0 ? 'text-green-600 font-medium' : 'text-gray-500' }}">{{ $p['today_published'] }}</td>
                                    <td class="px-4 py-3 text-sm text-center">
                                        @if ($p['status'] !== 'authorized')
                                            <span class="text-gray-400">-</span>
                                        @elseif ($p['remaining'] > 0)
                                            <span class="text-blue-600 font-medium">{{ $p['remaining'] }}</span>
                                        @else
                                            <span class="text-red-600 font-medium">已满</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-xs text-center text-gray-400">{{ $p['last_used_at']?->diffForHumans() ?? '从未使用' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-400">
                                        暂无账号
                                        <br>
                                        <a href="{{ route('admin.browser-profiles.create') }}" class="text-blue-600 hover:text-blue-700">添加第一个账号</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Recent Publish History --}}
        <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
            <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-sm font-medium text-gray-700">最近发布记录</h3>
                <a href="{{ route('admin.publish-tasks.index') }}" class="text-xs text-blue-600 hover:text-blue-700">查看全部 →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">文章</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">账号</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">平台</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">状态</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">时间</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($recentTasks as $task)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm text-gray-900 max-w-xs truncate">{{ $task->article?->title ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $task->profile?->account_name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ \App\Models\BrowserProfile::PLATFORMS[$task->platform] ?? $task->platform }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if ($task->status === 'completed')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">成功</span>
                                    @elseif ($task->status === 'failed')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800" title="{{ $task->error_message }}">失败</span>
                                    @elseif ($task->status === 'running')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">发布中</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">待处理</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-400 whitespace-nowrap">{{ $task->created_at?->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-400">暂无发布记录</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Batch Publish Logs --}}
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-sm font-medium text-gray-700">批量脚本发布记录</h3>
                <div class="flex items-center space-x-3">
                    <span class="text-xs text-gray-400">今日批量发布: {{ $todayBatchPublishes }} 篇</span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">文章ID</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">标题</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">窗口</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">平台状态</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">文章状态</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">时间</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($recentBatchLogs as $log)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $log->article_id }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 max-w-xs truncate">{{ $log->title ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $log->account_name ?? '-' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if ($log->verify_status === '审核中')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">审核中</span>
                                    @elseif ($log->verify_status === '已发布' || $log->verify_status === '发布成功')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">已发布</span>
                                    @elseif ($log->verify_status)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">{{ $log->verify_status }}</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">已提交</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if ($log->article_deleted)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">已删除</span>
                                    @elseif ($log->article_review_status === 'pending_review')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">待审</span>
                                    @elseif ($log->article_review_status === 'approved')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">已发布</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">{{ $log->article_review_status ?? '未知' }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-400 whitespace-nowrap">{{ $log->created_at?->format('m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-400">暂无批量发布记录</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- Publish Modal --}}
    <div id="publish-modal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black opacity-50" onclick="closePublishModal()"></div>
            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-2xl mx-auto p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-900">选择发布窗口</h2>
                    <button type="button" onclick="closePublishModal()" class="text-gray-400 hover:text-gray-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                <p class="text-sm text-gray-500 mb-4">勾选要使用的窗口账号，确认后将按顺序逐一发布。</p>
                <div id="modal-accounts" class="space-y-3 mb-6">
                    <div class="text-center text-sm text-gray-400 py-4"><i data-lucide="loader-2" class="w-4 h-4 inline animate-spin mr-1"></i> 加载中...</div>
                </div>
                <div class="flex items-center justify-end space-x-3 border-t pt-4">
                    <button type="button" onclick="closePublishModal()" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">取消</button>
                    <button type="button" id="confirm-publish-btn" onclick="confirmPublish()" disabled class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        开始发布 (0 个窗口)
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let publishAccounts = [];

        document.addEventListener('DOMContentLoaded', function () {
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });

        function openPublishModal() {
            const modal = document.getElementById('publish-modal');
            modal.classList.remove('hidden');
            if (typeof lucide !== 'undefined') lucide.createIcons();
            loadAccounts();
        }

        function closePublishModal() {
            document.getElementById('publish-modal').classList.add('hidden');
        }

        function loadAccounts() {
            const container = document.getElementById('modal-accounts');
            container.innerHTML = '<div class="text-center text-sm text-gray-400 py-4"><svg class="w-4 h-4 inline animate-spin mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg> 加载中...</div>';

            fetch('/api/v1/internal/publish/accounts')
                .then(r => r.json())
                .then(resp => {
                    const accounts = resp.data?.accounts || [];
                    if (accounts.length === 0) {
                        container.innerHTML = '<div class="text-center text-sm text-gray-400 py-4">暂无可用发布账号</div>';
                        document.getElementById('confirm-publish-btn').disabled = true;
                        return;
                    }
                    publishAccounts = accounts;
                    renderAccounts(accounts);
                })
                .catch(err => {
                    container.innerHTML = '<div class="text-center text-sm text-red-500 py-4">加载失败：' + err.message + '</div>';
                });
        }

        function renderAccounts(accounts) {
            const container = document.getElementById('modal-accounts');
            let html = '';
            accounts.forEach((acc, idx) => {
                const hasRemaining = acc.remaining > 0;
                const tasksHtml = acc.tasks.length > 0
                    ? acc.tasks.map(t => `<span class="text-xs text-gray-500">${t.task_name}: <strong>${t.pending}</strong> 篇待发</span>`).join('<br>')
                    : '<span class="text-xs text-gray-400">无待发文章</span>';

                html += `
                    <label class="flex items-start p-4 border rounded-lg cursor-pointer ${hasRemaining ? 'hover:bg-gray-50' : 'bg-gray-50 opacity-60'}">
                        <input type="checkbox" class="account-check mt-1 h-4 w-4 text-blue-600 border-gray-300 rounded" data-index="${idx}" ${hasRemaining ? 'checked' : 'disabled'}>
                        <div class="ml-3 flex-1">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-900">${acc.account_name}</span>
                                <span class="text-xs ${acc.remaining > 0 ? 'text-blue-600' : 'text-red-500'}">
                                    ${acc.remaining > 0 ? '剩余 ' + acc.remaining + '/' + acc.daily_limit : '配额已满'}
                                </span>
                            </div>
                            <div class="mt-1">${tasksHtml}</div>
                        </div>
                    </label>
                `;
            });
            container.innerHTML = html;
            updateConfirmButton();

            document.querySelectorAll('.account-check').forEach(cb => {
                cb.addEventListener('change', updateConfirmButton);
            });
        }

        function getSelectedAccounts() {
            const selected = [];
            document.querySelectorAll('.account-check:checked').forEach(cb => {
                const idx = parseInt(cb.dataset.index);
                selected.push(publishAccounts[idx]);
            });
            return selected;
        }

        function updateConfirmButton() {
            const selected = getSelectedAccounts();
            const btn = document.getElementById('confirm-publish-btn');
            const count = selected.length;
            btn.disabled = count === 0;
            btn.textContent = '开始发布 (' + count + ' 个窗口)';
        }

        function confirmPublish() {
            const selected = getSelectedAccounts();
            if (selected.length === 0) return;

            const accountNames = selected.map(a => a.account_name);
            const statusEl = document.getElementById('batch-status');
            const modal = document.getElementById('publish-modal');

            closePublishModal();

            statusEl.className = 'bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 text-sm text-blue-700';
            statusEl.innerHTML = '正在启动批量发布（' + accountNames.join(', ') + '）...';
            statusEl.classList.remove('hidden');

            fetch('http://localhost:18433/run-batch', {
                method: 'POST',
                headers: { 'content-type': 'application/json' },
                body: JSON.stringify({ accounts: accountNames }),
            })
                .then(r => r.json())
                .then(data => {
                    if (data.pid) {
                        statusEl.className = 'bg-green-50 border border-green-200 rounded-lg p-4 mb-6 text-sm text-green-700';
                        statusEl.innerHTML = '批量发布已启动 (PID: ' + data.pid + ')，窗口: ' + accountNames.join(', ') + '。日志将写入 published.jsonl，完成后请刷新看板。';
                    } else {
                        statusEl.className = 'bg-red-50 border border-red-200 rounded-lg p-4 mb-6 text-sm text-red-700';
                        statusEl.innerHTML = '启动失败: ' + (data.error || '未知错误');
                    }
                })
                .catch(err => {
                    statusEl.className = 'bg-red-50 border border-red-200 rounded-lg p-4 mb-6 text-sm text-red-700';
                    statusEl.innerHTML = '无法连接发布触发器 (localhost:18433)，请确认 batch_trigger_server.js 是否已启动';
                });
        }

    </script>
@endpush