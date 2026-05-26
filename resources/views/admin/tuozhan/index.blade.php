@extends('admin.layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: linear-gradient(135deg, #97c2ff, #6e72ff);">
                <i data-lucide="puzzle" class="w-5 h-5 text-white"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">参考站拓词工具</h1>
                <p class="text-sm text-gray-500 mt-0.5">笛卡尔积组合关键词，支持多维度自由搭配</p>
            </div>
        </div>
    </div>

    {{-- 维度说明 --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center gap-2 mb-2">
                <span class="w-6 h-6 rounded-full bg-blue-500 text-white text-xs flex items-center justify-center font-bold">A</span>
                <span class="font-medium text-blue-800">前缀词</span>
            </div>
            <p class="text-xs text-blue-600">如：市面上、行业内、目前、最新</p>
        </div>
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center gap-2 mb-2">
                <span class="w-6 h-6 rounded-full bg-green-500 text-white text-xs flex items-center justify-center font-bold">B</span>
                <span class="font-medium text-green-800">前缀词B</span>
            </div>
            <p class="text-xs text-green-600">如：为什么、如何、怎样</p>
        </div>
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
            <div class="flex items-center gap-2 mb-2">
                <span class="w-6 h-6 rounded-full bg-purple-500 text-white text-xs flex items-center justify-center font-bold">C</span>
                <span class="font-medium text-purple-800">主词 <span class="text-red-500">*</span></span>
            </div>
            <p class="text-xs text-purple-600">核心产品词，如：洗地机、扫地机</p>
        </div>
        <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
            <div class="flex items-center gap-2 mb-2">
                <span class="w-6 h-6 rounded-full bg-orange-500 text-white text-xs flex items-center justify-center font-bold">D</span>
                <span class="font-medium text-orange-800">通义词</span>
            </div>
            <p class="text-xs text-orange-600">如：品牌、公司、工厂、价格</p>
        </div>
        <div class="bg-cyan-50 border border-cyan-200 rounded-lg p-4">
            <div class="flex items-center gap-2 mb-2">
                <span class="w-6 h-6 rounded-full bg-cyan-500 text-white text-xs flex items-center justify-center font-bold">E</span>
                <span class="font-medium text-cyan-800">后缀词</span>
            </div>
            <p class="text-xs text-cyan-600">如：推荐、排行榜、评测</p>
        </div>
        <div class="bg-pink-50 border border-pink-200 rounded-lg p-4">
            <div class="flex items-center gap-2 mb-2">
                <span class="w-6 h-6 rounded-full bg-pink-500 text-white text-xs flex items-center justify-center font-bold">F</span>
                <span class="font-medium text-pink-800">疑问词</span>
            </div>
            <p class="text-xs text-pink-600">如：哪家好、多少钱、怎么选</p>
        </div>
    </div>

    {{-- 组合方式选择 --}}
    <div class="bg-white rounded-lg shadow p-6">
        <div class="mb-4">
            <h3 class="font-medium text-gray-900 mb-3">选择组合方式</h3>
            <div class="grid grid-cols-5 gap-3" id="combinations">
                @foreach($combinations as $key => $fields)
                    <label class="relative cursor-pointer">
                        <input type="checkbox" name="combinations[]" value="{{ $key }}"
                               class="sr-only peer" {{ $key == 'C+D' ? 'checked' : '' }}>
                        <div class="border-2 border-gray-200 rounded-lg p-3 text-center transition-all
                                    peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700
                                    hover:border-blue-300">
                            <span class="font-medium">{{ $key }}</span>
                            <div class="text-xs text-gray-500 mt-1">{{ implode('+', $fields) }}</div>
                        </div>
                    </label>
                @endforeach
            </div>
            <div class="mt-2 flex gap-2">
                <button type="button" onclick="checkAll()" class="text-xs text-blue-600 hover:text-blue-800">全选</button>
                <span class="text-gray-300">|</span>
                <button type="button" onclick="checkNone()" class="text-xs text-gray-600 hover:text-gray-800">取消全选</button>
            </div>
        </div>
    </div>

    {{-- 维度输入区 --}}
    <div class="grid grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow p-5">
            <div class="flex items-center gap-2 mb-3">
                <span class="w-7 h-7 rounded-full bg-blue-500 text-white text-sm flex items-center justify-center font-bold">A</span>
                <span class="font-medium text-gray-900">前缀词</span>
                <span class="text-xs text-gray-400 ml-auto">每行一个</span>
            </div>
            <textarea name="A" rows="6" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                      placeholder="例如：市面上&#10;行业内&#10;目前&#10;最新"></textarea>
        </div>

        <div class="bg-white rounded-lg shadow p-5">
            <div class="flex items-center gap-2 mb-3">
                <span class="w-7 h-7 rounded-full bg-green-500 text-white text-sm flex items-center justify-center font-bold">B</span>
                <span class="font-medium text-gray-900">前缀词B</span>
                <span class="text-xs text-gray-400 ml-auto">每行一个</span>
            </div>
            <textarea name="B" rows="6" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm"
                      placeholder="例如：为什么&#10;如何&#10;怎样"></textarea>
        </div>

        <div class="bg-white rounded-lg shadow p-5">
            <div class="flex items-center gap-2 mb-3">
                <span class="w-7 h-7 rounded-full bg-purple-500 text-white text-sm flex items-center justify-center font-bold">C</span>
                <span class="font-medium text-gray-900">主词 <span class="text-red-500">*</span></span>
                <span class="text-xs text-gray-400 ml-auto">每行一个</span>
            </div>
            <textarea name="C" rows="6" class="w-full rounded-lg border-purple-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 text-sm"
                      placeholder="例如：洗地机&#10;扫地机&#10;吸尘器"></textarea>
        </div>

        <div class="bg-white rounded-lg shadow p-5">
            <div class="flex items-center gap-2 mb-3">
                <span class="w-7 h-7 rounded-full bg-orange-500 text-white text-sm flex items-center justify-center font-bold">D</span>
                <span class="font-medium text-gray-900">通义词</span>
                <span class="text-xs text-gray-400 ml-auto">每行一个</span>
            </div>
            <textarea name="D" rows="6" class="w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm"
                      placeholder="例如：品牌&#10;公司&#10;工厂&#10;价格&#10;推荐"></textarea>
        </div>

        <div class="bg-white rounded-lg shadow p-5">
            <div class="flex items-center gap-2 mb-3">
                <span class="w-7 h-7 rounded-full bg-cyan-500 text-white text-sm flex items-center justify-center font-bold">E</span>
                <span class="font-medium text-gray-900">后缀词</span>
                <span class="text-xs text-gray-400 ml-auto">每行一个</span>
            </div>
            <textarea name="E" rows="6" class="w-full rounded-lg border-cyan-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 text-sm"
                      placeholder="例如：推荐&#10;排行榜&#10;评测&#10;哪个好"></textarea>
        </div>

        <div class="bg-white rounded-lg shadow p-5">
            <div class="flex items-center gap-2 mb-3">
                <span class="w-7 h-7 rounded-full bg-pink-500 text-white text-sm flex items-center justify-center font-bold">F</span>
                <span class="font-medium text-gray-900">疑问词</span>
                <span class="text-xs text-gray-400 ml-auto">每行一个</span>
            </div>
            <textarea name="F" rows="6" class="w-full rounded-lg border-pink-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 text-sm"
                      placeholder="例如：哪家好&#10;多少钱&#10;怎么选&#10;哪个牌子"></textarea>
        </div>
    </div>

    {{-- 操作区 --}}
    <div class="bg-white rounded-lg shadow p-6 flex items-center justify-between">
        <div class="flex gap-3">
            <button type="button" onclick="generate()" id="generateBtn"
                    class="px-5 py-2.5 text-sm font-medium text-white rounded-lg transition-all"
                    style="background: linear-gradient(135deg, #6e72ff, #43d2f7);"
                    onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                <i data-lucide="sparkles" class="w-4 h-4 inline mr-1"></i>
                生成关键词
            </button>
            <button type="button" onclick="copyResults()" id="copyBtn" disabled
                    class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed">
                <i data-lucide="copy" class="w-4 h-4 inline mr-1"></i>
                复制结果
            </button>
            <button type="button" onclick="clearAll()"
                    class="px-5 py-2.5 text-sm font-medium text-gray-500 bg-gray-100 rounded-lg hover:bg-gray-200">
                <i data-lucide="trash-2" class="w-4 h-4 inline mr-1"></i>
                清空所有
            </button>
        </div>

        <div class="flex items-center gap-4">
            <div class="text-sm text-gray-500">
                已生成 <span id="totalCount" class="font-bold text-purple-600">0</span> 个关键词
            </div>
            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-600">保存到关键词库：</label>
                <select name="library_id" id="librarySelect" class="rounded-lg border-gray-300 shadow-sm text-sm py-2">
                    <option value="">请选择关键词库</option>
                    @foreach($libraries as $library)
                        <option value="{{ $library->id }}">{{ $library->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="button" onclick="saveKeywords()" id="saveBtn" disabled
                    class="px-5 py-2.5 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">
                <i data-lucide="save" class="w-4 h-4 inline mr-1"></i>
                保存关键词
            </button>
        </div>
    </div>

    {{-- 结果展示 --}}
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b flex items-center justify-between">
            <h3 class="font-medium text-gray-900">生成结果</h3>
            <div class="flex items-center gap-4 text-sm" id="countSummary"></div>
        </div>
        <div class="p-6">
            <textarea id="resultArea" rows="20" readonly
                      class="w-full rounded-lg border-gray-200 bg-gray-50 text-sm font-mono"
                      placeholder="点击「生成关键词」按钮，结果将显示在这里..."></textarea>
        </div>
    </div>
</div>

@push('scripts')
<script>
let generatedKeywords = [];

function checkAll() {
    document.querySelectorAll('#combinations input[type="checkbox"]').forEach(cb => cb.checked = true);
}

function checkNone() {
    document.querySelectorAll('#combinations input[type="checkbox"]').forEach(cb => cb.checked = false);
}

async function generate() {
    const combinations = Array.from(document.querySelectorAll('#combinations input:checked')).map(cb => cb.value);
    if (combinations.length === 0) {
        alert('请至少选择一个组合方式');
        return;
    }

    const c = document.querySelector('textarea[name="C"]').value.trim();
    if (!c) {
        alert('请输入主词（C）');
        return;
    }

    const btn = document.getElementById('generateBtn');
    btn.disabled = true;
    btn.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 inline mr-1 animate-spin"></i> 生成中...';
    lucide.createIcons();

    const formData = new FormData();
    combinations.forEach(combo => formData.append('combinations[]', combo));
    formData.append('c', c);

    ['A', 'B', 'C', 'D', 'E', 'F'].forEach(dim => {
        const val = document.querySelector(`textarea[name="${dim}"]`).value;
        if (val) formData.append(dim, val);
    });

    try {
        const response = await fetch('{{ route("admin.tuozhan.generate") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: formData,
        });

        const data = await response.json();
        if (response.ok) {
            generatedKeywords = data.keywords;
            document.getElementById('resultArea').value = data.keywords.join('\n');
            document.getElementById('totalCount').textContent = data.total;

            // Update count summary
            let summary = '';
            for (const [combo, count] of Object.entries(data.counts)) {
                summary += `<span class="px-2 py-1 bg-gray-100 rounded text-xs">${combo}: ${count}</span>`;
            }
            document.getElementById('countSummary').innerHTML = summary;

            document.getElementById('copyBtn').disabled = false;
            document.getElementById('saveBtn').disabled = false;
        } else {
            alert('生成失败：' + (data.message || '未知错误'));
        }
    } catch (error) {
        alert('网络错误，请重试');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i data-lucide="sparkles" class="w-4 h-4 inline mr-1"></i> 生成关键词';
        lucide.createIcons();
    }
}

function copyResults() {
    const text = document.getElementById('resultArea').value;
    navigator.clipboard.writeText(text).then(() => {
        const btn = document.getElementById('copyBtn');
        const original = btn.innerHTML;
        btn.innerHTML = '<i data-lucide="check" class="w-4 h-4 inline mr-1"></i> 已复制';
        btn.classList.add('bg-green-100', 'text-green-700');
        lucide.createIcons();
        setTimeout(() => {
            btn.innerHTML = original;
            btn.classList.remove('bg-green-100', 'text-green-700');
            lucide.createIcons();
        }, 2000);
    });
}

function clearAll() {
    ['A', 'B', 'C', 'D', 'E', 'F'].forEach(dim => {
        document.querySelector(`textarea[name="${dim}"]`).value = '';
    });
    document.getElementById('resultArea').value = '';
    document.getElementById('totalCount').textContent = '0';
    document.getElementById('countSummary').innerHTML = '';
    generatedKeywords = [];
    document.getElementById('copyBtn').disabled = true;
    document.getElementById('saveBtn').disabled = true;
}

async function saveKeywords() {
    const libraryId = document.getElementById('librarySelect').value;
    if (!libraryId) {
        alert('请选择要保存的关键词库');
        return;
    }

    if (generatedKeywords.length === 0) {
        alert('没有可保存的关键词');
        return;
    }

    const btn = document.getElementById('saveBtn');
    btn.disabled = true;
    btn.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 inline mr-1 animate-spin"></i> 保存中...';
    lucide.createIcons();

    try {
        const response = await fetch('{{ route("admin.tuozhan.save") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                keywords: generatedKeywords.join('\n'),
                library_id: libraryId,
            }),
        });

        const data = await response.json();
        if (response.ok) {
            btn.innerHTML = '<i data-lucide="check" class="w-4 h-4 inline mr-1"></i> 保存成功';
            btn.classList.add('bg-green-600');
            setTimeout(() => {
                btn.innerHTML = '<i data-lucide="save" class="w-4 h-4 inline mr-1"></i> 保存关键词';
                btn.classList.remove('bg-green-600');
                btn.disabled = false;
                lucide.createIcons();
            }, 2000);
        } else {
            alert('保存失败：' + (data.message || '未知错误'));
            btn.disabled = false;
            btn.innerHTML = '<i data-lucide="save" class="w-4 h-4 inline mr-1"></i> 保存关键词';
            lucide.createIcons();
        }
    } catch (error) {
        alert('网络错误，请重试');
        btn.disabled = false;
        btn.innerHTML = '<i data-lucide="save" class="w-4 h-4 inline mr-1"></i> 保存关键词';
        lucide.createIcons();
    }
}
</script>
@endpush
@endsection