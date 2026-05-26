@extends('admin.layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">站点管理</h1>
        <a href="{{ route('admin.seo.sites.create') }}"
           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
            添加站点
        </a>
    </div>

    {{-- 站点列表 --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">序号</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">域名</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">站点类型</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">已发布数</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">备注</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">创建时间</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">操作</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($sites as $index => $site)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $sites->firstItem() + $index }}</td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                        <a href="http://{{ $site->domain }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                            {{ $site->domain }}
                        </a>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $siteTypes[$site->site_type] ?? $site->site_type ?? '-' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $site->published_count }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ Str::limit($site->remark, 30) ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $site->created_at->format('Y-m-d H:i') }}
                    </td>
                    <td class="px-6 py-4 text-right text-sm">
                        <a href="{{ route('admin.seo.sites.edit', $site) }}" class="text-blue-600 hover:text-blue-800 mr-3">
                            <i data-lucide="edit" class="w-4 h-4 inline"></i>
                        </a>
                        <form action="{{ route('admin.seo.sites.destroy', $site) }}" method="POST" class="inline"
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
                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                        暂无站点配置
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $sites->links() }}
    </div>
</div>
@endsection
