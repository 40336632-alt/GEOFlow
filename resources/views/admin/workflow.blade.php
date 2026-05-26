@extends('admin.layouts.app')

@section('content')
<div class="space-y-8">
    {{-- 页面标题 --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">GEO内容发布流程</h1>
        <p class="mt-2 text-sm text-gray-500">按以下7个步骤完成从素材准备到内容发布的完整流程，每个步骤都有关键注意事项和快捷入口。</p>
    </div>

    {{-- 流程概览 --}}
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-100">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600 text-white">
                    <i data-lucide="workflow" class="h-5 w-5"></i>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">完整流程概览</h2>
                    <p class="text-sm text-gray-600">准备素材 → 拓词 → 写作 → 收录查询 → 可见度诊断 → 发布 → SEO优化</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700">
                    <i data-lucide="zap" class="w-3 h-3 mr-1"></i>
                    7个步骤
                </span>

            </div>
        </div>
    </div>

    {{-- 步骤1: 准备素材 --}}
    <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 overflow-hidden">
        <div class="p-6">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-violet-100 text-violet-600 font-bold text-lg">
                    1
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <h3 class="text-lg font-semibold text-gray-900">准备素材</h3>
                        <span class="inline-flex items-center rounded-full bg-violet-50 px-2.5 py-0.5 text-xs font-medium text-violet-700">
                            <i data-lucide="database" class="w-3 h-3 mr-1"></i>
                            基础工作
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 mb-4">把真实、可靠的业务资料沉淀为素材库，任务生成时会优先使用这些内容。素材质量直接决定AI生成内容的质量。</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div class="rounded-lg bg-gray-50 p-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">操作步骤</h4>
                            <ul class="space-y-1.5 text-sm text-gray-600">
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5"></i>
                                    创建关键词库，导入或手动添加关键词
                                </li>
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5"></i>
                                    创建标题库，准备文章标题
                                </li>
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5"></i>
                                    上传知识库文档（PDF/TXT/DOCX）
                                </li>
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5"></i>
                                    上传图片库素材
                                </li>
                            </ul>
                        </div>
                        <div class="rounded-lg bg-amber-50 p-4">
                            <h4 class="text-sm font-medium text-amber-800 mb-2">
                                <i data-lucide="alert-triangle" class="w-4 h-4 inline mr-1"></i>
                                关键注意事项
                            </h4>
                            <ul class="space-y-1.5 text-sm text-amber-700">
                                <li>关键词要精准，不要太宽泛</li>
                                <li>标题要吸引人，包含关键词</li>
                                <li>知识库文档质量直接影响生成质量</li>
                                <li>建议每个领域准备20-50个关键词</li>
                            </ul>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('admin.keyword-libraries.index') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-violet-700 bg-violet-50 rounded-lg hover:bg-violet-100">
                            <i data-lucide="key" class="w-4 h-4 mr-1.5"></i>
                            关键词库
                        </a>
                        <a href="{{ route('admin.title-libraries.index') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-violet-700 bg-violet-50 rounded-lg hover:bg-violet-100">
                            <i data-lucide="heading" class="w-4 h-4 mr-1.5"></i>
                            标题库
                        </a>
                        <a href="{{ route('admin.knowledge-bases.index') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-violet-700 bg-violet-50 rounded-lg hover:bg-violet-100">
                            <i data-lucide="book-open" class="w-4 h-4 mr-1.5"></i>
                            知识库
                        </a>
                        <a href="{{ route('admin.image-libraries.index') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-violet-700 bg-violet-50 rounded-lg hover:bg-violet-100">
                            <i data-lucide="image" class="w-4 h-4 mr-1.5"></i>
                            图片库
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 步骤2: AI智能拓词 --}}
    <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 overflow-hidden">
        <div class="p-6">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-purple-100 text-purple-600 font-bold text-lg">
                    2
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <h3 class="text-lg font-semibold text-gray-900">AI智能拓词</h3>
                        <span class="inline-flex items-center rounded-full bg-purple-50 px-2.5 py-0.5 text-xs font-medium text-purple-700">
                            <i data-lucide="sparkles" class="w-3 h-3 mr-1"></i>
                            可选步骤
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 mb-4">输入主关键词，AI自动生成20-50个相关搜索问题，用于扩展关键词库。生成的关键词可用于AI写作任务的蒸馏训练。</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div class="rounded-lg bg-gray-50 p-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">操作步骤</h4>
                            <ul class="space-y-1.5 text-sm text-gray-600">
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5"></i>
                                    输入主关键词（如"富贵包消除"）
                                </li>
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5"></i>
                                    输入品牌名称（如"尹卫民"）
                                </li>
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5"></i>
                                    选择生成数量（10/20/30/50个）
                                </li>
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5"></i>
                                    保存到关键词库备用
                                </li>
                            </ul>
                        </div>
                        <div class="rounded-lg bg-amber-50 p-4">
                            <h4 class="text-sm font-medium text-amber-800 mb-2">
                                <i data-lucide="alert-triangle" class="w-4 h-4 inline mr-1"></i>
                                关键注意事项
                            </h4>
                            <ul class="space-y-1.5 text-sm text-amber-700">
                                <li>建议先拓词再写文章，关键词更丰富</li>
                                <li>品牌名要准确，影响收录查询结果</li>
                                <li>生成的关键词可保存到关键词库复用</li>

                            </ul>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('admin.ai-expand.index') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                            <i data-lucide="sparkles" class="w-4 h-4 mr-1.5"></i>
                            开始拓词
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 步骤3: AI写作 --}}
    <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 overflow-hidden">
        <div class="p-6">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-600 font-bold text-lg">
                    3
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <h3 class="text-lg font-semibold text-gray-900">AI写作</h3>
                        <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700">
                            <i data-lucide="pen-tool" class="w-3 h-3 mr-1"></i>
                            核心步骤
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 mb-4">基于关键词库和写作指令，AI自动生成高质量文章。可关联知识库进行RAG增强，大幅提升内容专业性和准确性。</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div class="rounded-lg bg-gray-50 p-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">操作步骤</h4>
                            <ul class="space-y-1.5 text-sm text-gray-600">
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5"></i>
                                    创建写作指令（定义风格、语气、结构）
                                </li>
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5"></i>
                                    创建写作任务，选择关键词库
                                </li>
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5"></i>
                                    关联知识库（RAG增强）
                                </li>
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5"></i>
                                    执行任务，生成文章草稿
                                </li>
                            </ul>
                        </div>
                        <div class="rounded-lg bg-amber-50 p-4">
                            <h4 class="text-sm font-medium text-amber-800 mb-2">
                                <i data-lucide="alert-triangle" class="w-4 h-4 inline mr-1"></i>
                                关键注意事项
                            </h4>
                            <ul class="space-y-1.5 text-sm text-amber-700">
                                <li>写作指令越详细，生成质量越高</li>
                                <li>关联知识库可大幅提升专业性</li>
                                <li>生成后必须人工审核再发布</li>
                                <li>不要直接发布未审核内容</li>
                            </ul>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('admin.writing-instructions.index') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100">
                            <i data-lucide="file-text" class="w-4 h-4 mr-1.5"></i>
                            写作指令
                        </a>
                        <a href="{{ route('admin.writing-tasks.index') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                            <i data-lucide="pen-tool" class="w-4 h-4 mr-1.5"></i>
                            写作任务
                        </a>
                        <a href="{{ route('admin.articles.index') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100">
                            <i data-lucide="file" class="w-4 h-4 mr-1.5"></i>
                            文章管理
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 步骤4: AI收录查询 --}}
    <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 overflow-hidden">
        <div class="p-6">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 font-bold text-lg">
                    4
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <h3 class="text-lg font-semibold text-gray-900">AI收录查询</h3>
                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700">
                            <i data-lucide="search" class="w-3 h-3 mr-1"></i>
                            验证效果
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 mb-4">查询8大AI搜索引擎是否收录了你的品牌内容。支持DeepSeek、豆包AI、腾讯元宝、通义千问、文心一言、纳米AI、Kimi、智谱清言。</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div class="rounded-lg bg-gray-50 p-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">操作步骤</h4>
                            <ul class="space-y-1.5 text-sm text-gray-600">
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5"></i>
                                    输入查询问题（如"深圳富贵包消除哪家好"）
                                </li>
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5"></i>
                                    输入品牌名称（如"尹卫民"）
                                </li>
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5"></i>
                                    选择要查询的AI平台
                                </li>
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5"></i>
                                    查看各平台收录情况
                                </li>
                            </ul>
                        </div>
                        <div class="rounded-lg bg-amber-50 p-4">
                            <h4 class="text-sm font-medium text-amber-800 mb-2">
                                <i data-lucide="alert-triangle" class="w-4 h-4 inline mr-1"></i>
                                关键注意事项
                            </h4>
                            <ul class="space-y-1.5 text-sm text-amber-700">
                                <li>建议文章发布24小时后再查收录</li>
                                <li>记录哪些平台收录了，针对性优化</li>
                            </ul>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('admin.index-check.create') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700">
                            <i data-lucide="search" class="w-4 h-4 mr-1.5"></i>
                            开始查询
                        </a>
                        <a href="{{ route('admin.index-check.index') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-emerald-700 bg-emerald-50 rounded-lg hover:bg-emerald-100">
                            <i data-lucide="list" class="w-4 h-4 mr-1.5"></i>
                            查询记录
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 步骤5: AI可见度诊断 --}}
    <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 overflow-hidden">
        <div class="p-6">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-orange-100 text-orange-600 font-bold text-lg">
                    5
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <h3 class="text-lg font-semibold text-gray-900">AI可见度诊断</h3>
                        <span class="inline-flex items-center rounded-full bg-orange-50 px-2.5 py-0.5 text-xs font-medium text-orange-700">
                            <i data-lucide="bar-chart-3" class="w-3 h-3 mr-1"></i>
                            深度分析
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 mb-4">批量查询品牌在AI搜索引擎中的可见度。通过组合前缀词、形容词、核心词、后缀词生成大量查询，全面评估品牌曝光情况。</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div class="rounded-lg bg-gray-50 p-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">操作步骤</h4>
                            <ul class="space-y-1.5 text-sm text-gray-600">
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5"></i>
                                    输入主关键词和品牌名称
                                </li>
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5"></i>
                                    设置列A（前缀词）：深圳、广州...
                                </li>
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5"></i>
                                    设置列B（形容词）：最好的、专业的...
                                </li>
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5"></i>
                                    选择查询平台，执行诊断
                                </li>
                            </ul>
                        </div>
                        <div class="rounded-lg bg-amber-50 p-4">
                            <h4 class="text-sm font-medium text-amber-800 mb-2">
                                <i data-lucide="alert-triangle" class="w-4 h-4 inline mr-1"></i>
                                关键注意事项
                            </h4>
                            <ul class="space-y-1.5 text-sm text-amber-700">
                                <li>建议先小批量测试，再批量执行</li>
                                <li>诊断报告可导出用于分析</li>
                                <li>定期诊断，跟踪可见度变化</li>
                            </ul>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('admin.diagnosis.create') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-orange-600 rounded-lg hover:bg-orange-700">
                            <i data-lucide="bar-chart-3" class="w-4 h-4 mr-1.5"></i>
                            开始诊断
                        </a>
                        <a href="{{ route('admin.diagnosis.index') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-orange-700 bg-orange-50 rounded-lg hover:bg-orange-100">
                            <i data-lucide="list" class="w-4 h-4 mr-1.5"></i>
                            诊断记录
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 步骤6: 浏览器发布 --}}
    <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 overflow-hidden">
        <div class="p-6">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-cyan-100 text-cyan-600 font-bold text-lg">
                    6
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <h3 class="text-lg font-semibold text-gray-900">浏览器发布</h3>
                        <span class="inline-flex items-center rounded-full bg-cyan-50 px-2.5 py-0.5 text-xs font-medium text-cyan-700">
                            <i data-lucide="globe" class="w-3 h-3 mr-1"></i>
                            多平台分发
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 mb-4">通过BitBrowser自动化发布到各自媒体平台。支持个人自媒体、KOL、网站媒体等多种发布类型。</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div class="rounded-lg bg-gray-50 p-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">操作步骤</h4>
                            <ul class="space-y-1.5 text-sm text-gray-600">
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5"></i>
                                    启动BitBrowser客户端
                                </li>
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5"></i>
                                    同步浏览器配置，授权平台账号
                                </li>
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5"></i>
                                    选择已发布的文章
                                </li>
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5"></i>
                                    选择发布类型和平台，执行发布
                                </li>
                            </ul>
                        </div>
                        <div class="rounded-lg bg-amber-50 p-4">
                            <h4 class="text-sm font-medium text-amber-800 mb-2">
                                <i data-lucide="alert-triangle" class="w-4 h-4 inline mr-1"></i>
                                关键注意事项
                            </h4>
                            <ul class="space-y-1.5 text-sm text-amber-700">
                                <li>BitBrowser要保持运行状态</li>
                                <li>各平台账号要提前在浏览器中登录</li>
                                <li>每天发布3-5篇，保持稳定节奏</li>
                            </ul>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('admin.browser-profiles.index') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-cyan-700 bg-cyan-50 rounded-lg hover:bg-cyan-100">
                            <i data-lucide="monitor" class="w-4 h-4 mr-1.5"></i>
                            浏览器管理
                        </a>
                        <a href="{{ route('admin.publish-tasks.create') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-cyan-600 rounded-lg hover:bg-cyan-700">
                            <i data-lucide="send" class="w-4 h-4 mr-1.5"></i>
                            创建发布任务
                        </a>
                        <a href="{{ route('admin.publish-tasks.index') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-cyan-700 bg-cyan-50 rounded-lg hover:bg-cyan-100">
                            <i data-lucide="list" class="w-4 h-4 mr-1.5"></i>
                            发布任务列表
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 步骤7: SEO优化发布 --}}
    <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 overflow-hidden">
        <div class="p-6">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-600 font-bold text-lg">
                    7
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <h3 class="text-lg font-semibold text-gray-900">SEO优化发布</h3>
                        <span class="inline-flex items-center rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-medium text-rose-700">
                            <i data-lucide="trending-up" class="w-3 h-3 mr-1"></i>
                            可选步骤
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 mb-4">将文章发布到自有网站进行SEO优化。自动生成sitemap，支持多站点分发，提升搜索引擎排名。</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div class="rounded-lg bg-gray-50 p-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">操作步骤</h4>
                            <ul class="space-y-1.5 text-sm text-gray-600">
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5"></i>
                                    添加目标站点，输入域名
                                </li>
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5"></i>
                                    选择站点类型
                                </li>
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5"></i>
                                    创建SEO发布任务
                                </li>
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5"></i>
                                    执行发布，自动生成sitemap
                                </li>
                            </ul>
                        </div>
                        <div class="rounded-lg bg-amber-50 p-4">
                            <h4 class="text-sm font-medium text-amber-800 mb-2">
                                <i data-lucide="alert-triangle" class="w-4 h-4 inline mr-1"></i>
                                关键注意事项
                            </h4>
                            <ul class="space-y-1.5 text-sm text-amber-700">
                                <li>适合自有网站的SEO优化</li>
                                <li>支持多站点分发</li>
                                <li>定期更新内容保持活跃度</li>
                                <li>配合收录查询验证效果</li>
                            </ul>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('admin.seo.sites.index') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-rose-700 bg-rose-50 rounded-lg hover:bg-rose-100">
                            <i data-lucide="globe" class="w-4 h-4 mr-1.5"></i>
                            站点管理
                        </a>
                        <a href="{{ route('admin.seo.tasks.index') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-rose-600 rounded-lg hover:bg-rose-700">
                            <i data-lucide="send" class="w-4 h-4 mr-1.5"></i>
                            SEO发布任务
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>
@endsection
