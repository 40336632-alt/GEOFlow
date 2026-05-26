@extends('admin.layouts.app')

@section('content')
<div class="py-6 px-4 max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">补发文章</h1>
        <a href="{{ route('admin.articles.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
            &larr; 返回文章列表
        </a>
    </div>

    {{-- 库存统计 --}}
    @if($inventory->isNotEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-3">文章库存</h2>
        <div class="flex flex-wrap gap-3">
            @foreach($inventory as $taskId => $count)
            @php $task = $tasks->get($taskId); @endphp
            <div class="px-4 py-2 rounded-lg border text-sm {{ $count < 10 ? 'bg-red-50 border-red-200 text-red-700' : 'bg-green-50 border-green-200 text-green-700' }}">
                <span class="font-medium">{{ $task?->name ?? "任务{$taskId}" }}</span>
                <span class="ml-2">{{ $count }} 篇</span>
                @if($count < 10)
                <a href="{{ route('admin.tasks.edit', $taskId) }}" class="ml-2 underline hover:no-underline">去生成</a>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- 选中文章列表 --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-3">已选文章 ({{ $articles->count() }} 篇)</h2>
        <div class="max-h-64 overflow-y-auto">
            <table class="w-full text-sm">
                <thead class="text-left text-gray-500 border-b">
                    <tr>
                        <th class="pb-2 w-12">ID</th>
                        <th class="pb-2">标题</th>
                        <th class="pb-2 w-20">任务</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($articles as $article)
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 text-gray-400">{{ $article->id }}</td>
                        <td class="py-2 text-gray-700 truncate max-w-md">{{ $article->title }}</td>
                        <td class="py-2 text-gray-500">{{ $article->task_id }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- 平台 + 窗口选择 --}}
    <div class="space-y-6 mb-6">
        @forelse($platforms as $group)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">{{ $group['label'] }}</h2>
            <p class="text-sm text-gray-500 mb-4">选定窗口后，文章将按顺序逐一发布，同一窗口每篇间隔 5~10 分钟。</p>
            <div class="space-y-3">
                @foreach($group['accounts'] as $account)
                <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-blue-50 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50 transition-colors account-option"
                       data-platform="{{ $group['platform'] }}"
                       data-trigger-url="{{ $group['trigger_url'] }}"
                       data-mode="{{ $group['trigger_mode'] ?? '' }}">
                    <input type="radio" name="account" value="{{ $account->account_name ?? $account->profile_name ?? $account->id }}"
                           class="mr-3 account-radio">
                    <div class="flex-1">
                        <div class="font-medium text-gray-800">{{ $account->account_name ?? $account->profile_name ?? '未命名' }}</div>
                        <div class="text-xs text-gray-500">限额 {{ $account->daily_limit }}/天</div>
                    </div>
                </label>
                @endforeach
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-sm text-red-500">没有可用的发布窗口，请先配置 BitBrowser 窗口。</p>
        </div>
        @endforelse
    </div>

    {{-- 确认 --}}
    <div class="flex items-center justify-end space-x-3">
        <a href="{{ route('admin.articles.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">取消</a>
        <button type="button" id="confirm-supplement-btn"
            class="px-6 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
            disabled>
            开始补发
        </button>
    </div>

    {{-- 状态 --}}
    <div id="supplement-status" class="mt-6 hidden"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const radios = document.querySelectorAll('.account-radio');
    const confirmBtn = document.getElementById('confirm-supplement-btn');
    const statusDiv = document.getElementById('supplement-status');
    const articleIds = @json($articles->pluck('id'));

    radios.forEach(function(radio) {
        radio.addEventListener('change', function() {
            confirmBtn.disabled = false;
        });
    });

    confirmBtn.addEventListener('click', async function() {
        const selected = document.querySelector('.account-radio:checked');
        if (!selected) {
            alert('请选择一个发布窗口');
            return;
        }

        const option = selected.closest('.account-option');
        if (!option) {
            alert('选项错误');
            return;
        }

        const accountName = selected.value;
        const triggerUrl = option.dataset.triggerUrl;
        const mode = option.dataset.mode;

        if (!confirm('确定用 "' + accountName + '" 补发 ' + articleIds.length + ' 篇文章？')) {
            return;
        }

        confirmBtn.disabled = true;
        confirmBtn.textContent = '启动中...';
        statusDiv.className = 'mt-6 p-4 bg-blue-50 text-blue-700 rounded-lg';
        statusDiv.textContent = '正在启动补发任务...';
        statusDiv.classList.remove('hidden');

        const body = {
            article_ids: articleIds,
            account_name: accountName,
        };
        if (mode) {
            body.mode = mode;
        }

        try {
            const resp = await fetch(triggerUrl, {
                method: 'POST',
                headers: { 'content-type': 'application/json' },
                body: JSON.stringify(body),
            });

            const data = await resp.json();

            if (resp.ok) {
                statusDiv.className = 'mt-6 p-4 bg-green-50 text-green-700 rounded-lg';
                statusDiv.innerHTML = '<strong>补发任务已启动！</strong><br>' +
                    '窗口: ' + accountName + '<br>' +
                    '文章数: ' + articleIds.length + ' 篇<br>' +
                    '请在 BitBrowser 上观察发布过程。';
                confirmBtn.textContent = '已启动';
            } else {
                throw new Error(data.error || '启动失败');
            }
        } catch (err) {
            confirmBtn.disabled = false;
            confirmBtn.textContent = '开始补发';
            statusDiv.className = 'mt-6 p-4 bg-red-50 text-red-700 rounded-lg';
            statusDiv.innerHTML = '<strong>启动失败:</strong> ' + err.message + '<br>' +
                '请确认批量触发服务 (batch_trigger_server.js) 是否运行在端口 18433。';
        }
    });
});
</script>
@endsection
