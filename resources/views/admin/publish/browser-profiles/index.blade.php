@extends('admin.layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">浏览器账号管理</h1>
        <div class="flex gap-3">
            <a href="{{ route('admin.browser-profiles.sync') }}"
               class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">
                <i data-lucide="refresh-cw" class="w-4 h-4 mr-2"></i>
                同步比特浏览器
            </a>
            <a href="{{ route('admin.browser-profiles.create') }}"
               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                添加账号
            </a>
        </div>
    </div>

    {{-- 平台筛选 --}}
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.browser-profiles.index') }}"
           class="px-3 py-1.5 text-sm rounded-lg {{ !$currentPlatform ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            全部平台
        </a>
        @foreach($platforms as $key => $name)
            <a href="{{ route('admin.browser-profiles.index', ['platform' => $key]) }}"
               class="px-3 py-1.5 text-sm rounded-lg {{ $currentPlatform === $key ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                {{ $name }}
            </a>
        @endforeach
    </div>

    {{-- 账号列表 --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">序号</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">平台</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">账号名称</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">配置名称</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">状态</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">今日发布</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">日限额</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">最后使用</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">操作</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($profiles as $index => $profile)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $profiles->firstItem() + $index }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $platforms[$profile->platform] ?? $profile->platform }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $profile->account_name ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $profile->profile_name ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm">
                        @if($profile->isAuthorized())
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">已授权</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">{{ $profile->status }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        <span class="{{ $profile->today_published >= $profile->daily_limit ? 'text-red-600' : '' }}">
                            {{ $profile->today_published }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $profile->daily_limit }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $profile->last_used_at?->format('Y-m-d H:i') ?? '-' }}
                    </td>
                    <td class="px-6 py-4 text-right text-sm">
                        <a href="{{ route('admin.browser-profiles.edit', $profile) }}" class="text-blue-600 hover:text-blue-800 mr-3">
                            <i data-lucide="edit" class="w-4 h-4 inline"></i>
                        </a>
                        <form action="{{ route('admin.browser-profiles.destroy', $profile) }}" method="POST" class="inline"
                              onsubmit="return confirm('确定删除？')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800">
                                <i data-lucide="trash-2" class="w-4 h-4 inline"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                        暂无浏览器账号配置
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $profiles->links() }}
    </div>
</div>
@endsection
