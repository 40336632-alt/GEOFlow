# GEO优化系统 - 全系列复刻计划（基于GEOFlow）

## 项目概述

**目标**: 基于现有GEOFlow系统，完整复刻小汇萃GEO优化系统的全部功能。

**原则**:
- 保留GEOFlow所有现有页面和功能
- 只新增缺失的功能模块
- 按照用户工作流程重新组织导航结构
- 按功能分区，每个分区有清晰的职责

**技术栈**:
- 后端: Laravel 11 + PHP 8.2 + PostgreSQL + Redis
- AI引擎: AutoGEO Python微服务 (Flask)
- 前端: Blade模板 + Tailwind CSS + Alpine.js
- 多平台发布: SyncCaster + 比特浏览器(BitBrowser)
- 部署: Docker Compose

---

## 导航结构重组

### 现有页面 → 新导航映射

```
┌─────────────────────────────────────────────────────────────────┐
│                    GEOFlow 导航结构（重组后）                     │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  1. 首页                    [已有] dashboard                     │
│                                                                 │
│  2. AI诊断                  [新增分区]                           │
│     ├── AI可见度诊断         [NEW] /diagnosis/add                │
│     └── 诊断报告             [NEW] /diagnosis/reports             │
│                                                                 │
│  3. 素材准备                [已有materials，重组]                 │
│     ├── 关键词库             [已有] keyword-libraries             │
│     ├── 标题库               [已有] title-libraries               │
│     ├── 企业画像图库         [已有] image-libraries               │
│     ├── 企业知识库           [已有] knowledge-bases               │
│     └── URL导入              [已有] url-import                    │
│                                                                 │
│  4. AI写作                  [新增分区]                           │
│     ├── 写作指令             [NEW] /writing-instructions          │
│     ├── 文章分类             [已有] categories                    │
│     ├── AI写作任务           [NEW] /writing-tasks                 │
│     └── 文章列表             [已有] articles                      │
│                                                                 │
│  5. 流量复刻                [新增分区]                           │
│     ├── 全网爆文复刻         [NEW] /viral-replication             │
│     └── 批量爆文复刻         [NEW] /batch-replication             │
│                                                                 │
│  6. 发布管理                [已有distribution，扩展]              │
│     ├── 网站媒体发布         [已有] distribution                  │
│     ├── 自媒体大V发布        [NEW] /publish/kol                   │
│     ├── 个人自媒体发布       [NEW] /publish/personal              │
│     ├── AI智能发布任务       [NEW] /publish/ai-tasks              │
│     └── 发布记录             [NEW] /publish/records               │
│                                                                 │
│  7. AI官网SEO               [新增分区]                           │
│     ├── 站点管理             [NEW] /seo/sites                     │
│     ├── SEO发布任务          [NEW] /seo/tasks                     │
│     └── 发布记录             [NEW] /seo/records                   │
│                                                                 │
│  8. AI数据中心              [已有analytics，扩展]                │
│     ├── 数据报表             [已有] analytics                     │
│     ├── AI收录查询           [NEW] /data/index-check              │
│     ├── 查询记录             [NEW] /data/index-records            │
│     ├── 关键词指数           [NEW] /data/keyword-index            │
│     └── AI拓词               [NEW] /data/ai-expand                │
│                                                                 │
│  9. GEO优化                 [已有] geo-optimization               │
│                                                                 │
│  10. 任务管理               [已有] tasks                          │
│                                                                 │
│  11. 系统设置               [已有，重组]                          │
│      ├── AI配置              [已有] ai-configurator               │
│      ├── AI模型管理          [已有] ai-models                     │
│      ├── AI提示词            [已有] ai-prompts                    │
│      ├── 站点设置            [已有] site-settings                 │
│      ├── 安全设置            [已有] security-settings             │
│      ├── 管理员管理          [已有] admin-users                   │
│      ├── API Tokens          [已有] api-tokens                    │
│      └── 操作日志            [已有] admin-activity-logs           │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## 新增功能清单

### 1. AI诊断模块（2个新页面）

#### 1.1 AI可见度诊断页
- **路由**: `/admin/diagnosis/add`
- **功能**: 检测品牌在8大AI平台的可见度
- **字段**:
  - 主关键词(下拉选择)
  - 公司品牌名
  - 关键词组合(A-E列)
  - 检测平台选择(8个checkbox)
- **流程**: 选择关键词 → 组合长尾词 → 选择平台 → 执行查询 → 生成报告
- **费用**: 50点/次

#### 1.2 诊断报告页
- **路由**: `/admin/diagnosis/reports`
- **功能**: 查看历史诊断报告
- **字段**: 任务名、关键词、品牌名、可见度分数、收录数量、创建时间
- **操作**: 查看详情、导出报告

---

### 2. AI写作模块（2个新页面）

#### 2.1 写作指令页
- **路由**: `/admin/writing-instructions`
- **功能**: 管理AI写作的Prompt模板
- **字段**: 指令名称、创作类型(文章/标题/流量复刻)、指令内容
- **预置模板**:
  - 文章创作指令
  - 标题创作指令
  - 流量复刻指令

#### 2.2 AI写作任务页
- **路由**: `/admin/writing-tasks`
- **功能**: 创建和管理AI写作任务
- **字段**: 任务名称、文章分类、蒸馏训练词、画像图库、配图数量、企业知识库、写作指令
- **流程**: 选择关键词 → 选择图库 → 选择知识库 → 选择指令 → 执行写作
- **状态**: 待执行/执行中/已完成/失败

---

### 3. 流量复刻模块（2个新页面）

#### 3.1 全网爆文复刻页
- **路由**: `/admin/viral-replication`
- **功能**: 输入爆文URL，AI改写后归类
- **字段**: 文章链接、图库选择、改写指令选择
- **流程**: 输入URL → 爬取原文 → AI改写 → 归类文章

#### 3.2 批量爆文复刻页
- **路由**: `/admin/batch-replication`
- **功能**: 批量输入多个URL，批量改写
- **字段**: URL列表(每行一个)、图库选择、改写指令选择

---

### 4. 发布管理模块（4个新页面）

#### 4.1 自媒体大V发布页
- **路由**: `/admin/publish/kol`
- **功能**: 选择KOL账号发布文章
- **字段**: 媒体查询搜索、平台筛选(18+平台)、粉丝量筛选、价格排序
- **平台**: 今日头条、百家号、知乎、微博、B站、小红书、微信公众号等

#### 4.2 个人自媒体发布页
- **路由**: `/admin/publish/personal`
- **功能**: 通过比特浏览器自动发布到个人账号
- **字段**: 浏览器配置列表、授权状态、每日发布限制
- **流程**: 下载助手 → 登录平台 → 捕获Token → 授权 → 自动发布

#### 4.3 AI智能发布任务页
- **路由**: `/admin/publish/ai-tasks`
- **功能**: 创建AI自动发布任务
- **字段**: 任务名称、文章选择、目标平台、发布时间

#### 4.4 发布记录页
- **路由**: `/admin/publish/records`
- **功能**: 查看所有发布记录
- **字段**: 文章标题、目标平台、发布状态、发布时间、发布URL

---

### 5. AI官网SEO模块（3个新页面）

#### 5.1 站点管理页
- **路由**: `/admin/seo/sites`
- **功能**: 管理企业官网站点
- **字段**: 网站类型、域名、已发布数量、备注

#### 5.2 SEO发布任务页
- **路由**: `/admin/seo/tasks`
- **功能**: 创建SEO发布任务
- **字段**: 任务名称、目标站点、文章选择、发布时间

#### 5.3 SEO发布记录页
- **路由**: `/admin/seo/records`
- **功能**: 查看SEO发布记录

---

### 6. AI数据中心模块（4个新页面）

#### 6.1 AI收录查询页
- **路由**: `/admin/data/index-check`
- **功能**: 检测文章在8大AI平台的收录情况
- **字段**: 关键词选择、检测平台、查询结果(带截图)
- **平台**: DeepSeek、豆包、腾讯元宝、通义千问、文心一言、纳米AI、Kimi、智谱清言
- **费用**: 5点/次

#### 6.2 查询记录页
- **路由**: `/admin/data/index-records`
- **功能**: 查看历史查询记录

#### 6.3 关键词指数页
- **路由**: `/admin/data/keyword-index`
- **功能**: 查看关键词热度指数

#### 6.4 AI拓词页
- **路由**: `/admin/data/ai-expand`
- **功能**: AI自动扩展关键词
- **费用**: 10点/次

---

## 数据库设计（新增表）

### 7.1 诊断相关表

```sql
-- 诊断任务表
CREATE TABLE diagnosis_tasks (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id),
    main_keyword VARCHAR(100) NOT NULL,
    brand_name VARCHAR(200),
    column_a JSONB,                            -- 前缀/地域
    column_b JSONB,                            -- 形容词
    column_c VARCHAR(100),                     -- 主词(必填)
    column_d VARCHAR(100),                     -- 目标词(必填)
    column_e JSONB,                            -- 推荐词
    platforms JSONB,                           -- 检测平台
    status VARCHAR(20) DEFAULT 'pending',
    total_queries INT DEFAULT 0,
    brand_mentioned INT DEFAULT 0,
    visibility_score DECIMAL(5,2),
    points_consumed INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- 诊断结果表
CREATE TABLE diagnosis_results (
    id BIGSERIAL PRIMARY KEY,
    task_id BIGINT NOT NULL REFERENCES diagnosis_tasks(id) ON DELETE CASCADE,
    query TEXT NOT NULL,
    platform VARCHAR(50) NOT NULL,
    answer TEXT,
    brand_mentioned BOOLEAN DEFAULT FALSE,
    mention_position INT,
    screenshot_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT NOW()
);
```

### 7.2 写作指令表

```sql
CREATE TABLE writing_instructions (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id),
    name VARCHAR(100) NOT NULL,
    type VARCHAR(20) NOT NULL,                  -- article/title/replication
    content TEXT NOT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);
```

### 7.3 写作任务表

```sql
CREATE TABLE writing_tasks (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id),
    name VARCHAR(200) NOT NULL,
    keyword_library_id BIGINT REFERENCES keyword_libraries(id),
    category_id BIGINT REFERENCES categories(id),
    image_library_id BIGINT REFERENCES image_libraries(id),
    image_count INT DEFAULT 2,
    knowledge_base_id BIGINT REFERENCES knowledge_bases(id),
    instruction_id BIGINT REFERENCES writing_instructions(id),
    max_articles INT DEFAULT 1,
    created_count INT DEFAULT 0,
    status VARCHAR(20) DEFAULT 'pending',
    error_message TEXT,
    last_written_at TIMESTAMP,
    points_consumed INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);
```

### 7.4 流量复刻表

```sql
CREATE TABLE viral_replications (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id),
    source_url VARCHAR(500) NOT NULL,
    source_title VARCHAR(500),
    source_content TEXT,
    category_id BIGINT REFERENCES categories(id),
    image_library_id BIGINT REFERENCES image_libraries(id),
    instruction_id BIGINT REFERENCES writing_instructions(id),
    rewritten_title VARCHAR(500),
    rewritten_content TEXT,
    status VARCHAR(20) DEFAULT 'pending',
    points_consumed INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT NOW()
);
```

### 7.5 浏览器配置表

```sql
CREATE TABLE browser_profiles (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id),
    platform VARCHAR(50) NOT NULL,
    profile_id VARCHAR(100) NOT NULL,           -- 比特浏览器配置ID
    profile_name VARCHAR(200),
    account_name VARCHAR(200),
    status VARCHAR(20) DEFAULT 'authorized',
    daily_limit INT DEFAULT 3,
    today_published INT DEFAULT 0,
    last_used_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT NOW()
);
```

### 7.6 发布任务表

```sql
CREATE TABLE publish_tasks (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id),
    article_id BIGINT NOT NULL REFERENCES articles(id),
    profile_id BIGINT REFERENCES browser_profiles(id),
    media_id BIGINT,
    platform VARCHAR(50) NOT NULL,
    publish_type VARCHAR(20) NOT NULL,          -- personal/kol/webmedia
    status VARCHAR(20) DEFAULT 'pending',
    error_message TEXT,
    published_url VARCHAR(500),
    published_at TIMESTAMP,
    points_consumed INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE publish_logs (
    id BIGSERIAL PRIMARY KEY,
    task_id BIGINT NOT NULL REFERENCES publish_tasks(id),
    action VARCHAR(50) NOT NULL,
    status VARCHAR(20),
    detail TEXT,
    screenshot_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT NOW()
);
```

### 7.7 SEO站点表

```sql
CREATE TABLE seo_sites (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id),
    site_type VARCHAR(50),                      -- wordpress/typecho/custom
    domain VARCHAR(200) NOT NULL,
    published_count INT DEFAULT 0,
    remark TEXT,
    created_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE seo_publish_tasks (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id),
    site_id BIGINT NOT NULL REFERENCES seo_sites(id),
    article_id BIGINT NOT NULL REFERENCES articles(id),
    status VARCHAR(20) DEFAULT 'pending',
    published_url VARCHAR(500),
    published_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT NOW()
);
```

### 7.8 收录查询表

```sql
CREATE TABLE index_checks (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id),
    question TEXT NOT NULL,
    brand_name VARCHAR(200),
    platforms JSONB,
    results JSONB,
    total_indexed INT DEFAULT 0,
    points_consumed INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE index_check_details (
    id BIGSERIAL PRIMARY KEY,
    check_id BIGINT NOT NULL REFERENCES index_checks(id) ON DELETE CASCADE,
    platform VARCHAR(50) NOT NULL,
    is_indexed BOOLEAN DEFAULT FALSE,
    answer_text TEXT,
    screenshot_url VARCHAR(500),
    error_message TEXT,
    checked_at TIMESTAMP DEFAULT NOW()
);
```

### 7.9 点数系统表

```sql
CREATE TABLE user_points (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id) UNIQUE,
    balance INT DEFAULT 0,
    total_purchased INT DEFAULT 0,
    total_consumed INT DEFAULT 0,
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE point_transactions (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id),
    type VARCHAR(20) NOT NULL,                  -- purchase/consumption/refund
    amount INT NOT NULL,
    balance_after INT NOT NULL,
    description VARCHAR(500),
    related_type VARCHAR(50),
    related_id BIGINT,
    created_at TIMESTAMP DEFAULT NOW()
);
```

---

## 新增路由

```php
// routes/web.php 新增

// AI诊断
Route::prefix('diagnosis')->name('diagnosis.')->group(function () {
    Route::get('/add', [DiagnosisController::class, 'add'])->name('add');
    Route::post('/add', [DiagnosisController::class, 'store'])->name('store');
    Route::get('/reports', [DiagnosisController::class, 'reports'])->name('reports');
    Route::get('/reports/{id}', [DiagnosisController::class, 'show'])->name('show');
});

// 写作指令
Route::resource('writing-instructions', WritingInstructionController::class);

// 写作任务
Route::prefix('writing-tasks')->name('writing-tasks.')->group(function () {
    Route::get('/', [WritingTaskController::class, 'index'])->name('index');
    Route::get('/create', [WritingTaskController::class, 'create'])->name('create');
    Route::post('/', [WritingTaskController::class, 'store'])->name('store');
    Route::get('/{id}', [WritingTaskController::class, 'show'])->name('show');
    Route::post('/{id}/execute', [WritingTaskController::class, 'execute'])->name('execute');
});

// 流量复刻
Route::prefix('viral-replication')->name('viral.')->group(function () {
    Route::get('/', [ViralReplicationController::class, 'index'])->name('index');
    Route::post('/', [ViralReplicationController::class, 'store'])->name('store');
    Route::get('/batch', [ViralReplicationController::class, 'batch'])->name('batch');
    Route::post('/batch', [ViralReplicationController::class, 'batchStore'])->name('batch.store');
});

// 发布管理
Route::prefix('publish')->name('publish.')->group(function () {
    Route::get('/kol', [PublishController::class, 'kol'])->name('kol');
    Route::get('/personal', [PublishController::class, 'personal'])->name('personal');
    Route::get('/ai-tasks', [PublishController::class, 'aiTasks'])->name('ai-tasks');
    Route::get('/records', [PublishController::class, 'records'])->name('records');
    Route::post('/execute', [PublishController::class, 'execute'])->name('execute');
});

// AI官网SEO
Route::prefix('seo')->name('seo.')->group(function () {
    Route::resource('sites', SeoSiteController::class);
    Route::resource('tasks', SeoTaskController::class);
    Route::get('/records', [SeoController::class, 'records'])->name('records');
});

// AI数据中心
Route::prefix('data')->name('data.')->group(function () {
    Route::get('/index-check', [DataController::class, 'indexCheck'])->name('index-check');
    Route::post('/index-check', [DataController::class, 'indexCheckStore'])->name('index-check.store');
    Route::get('/index-records', [DataController::class, 'indexRecords'])->name('index-records');
    Route::get('/keyword-index', [DataController::class, 'keywordIndex'])->name('keyword-index');
    Route::get('/ai-expand', [DataController::class, 'aiExpand'])->name('ai-expand');
    Route::post('/ai-expand', [DataController::class, 'aiExpandStore'])->name('ai-expand.store');
});
```

---

## 新增Controller

### DiagnosisController
```php
class DiagnosisController extends Controller
{
    public function add() { /* 显示诊断表单 */ }
    public function store(Request $request) { /* 创建诊断任务 */ }
    public function reports() { /* 诊断报告列表 */ }
    public function show($id) { /* 诊断报告详情 */ }
}
```

### WritingInstructionController
```php
class WritingInstructionController extends Controller
{
    public function index() { /* 指令列表 */ }
    public function create() { /* 创建表单 */ }
    public function store(Request $request) { /* 保存指令 */ }
    public function edit($id) { /* 编辑表单 */ }
    public function update(Request $request, $id) { /* 更新指令 */ }
    public function destroy($id) { /* 删除指令 */ }
}
```

### WritingTaskController
```php
class WritingTaskController extends Controller
{
    public function index() { /* 任务列表 */ }
    public function create() { /* 创建表单 */ }
    public function store(Request $request) { /* 保存任务 */ }
    public function show($id) { /* 任务详情 */ }
    public function execute($id) { /* 执行写作 */ }
}
```

### ViralReplicationController
```php
class ViralReplicationController extends Controller
{
    public function index() { /* 复刻列表 */ }
    public function store(Request $request) { /* 创建复刻 */ }
    public function batch() { /* 批量复刻表单 */ }
    public function batchStore(Request $request) { /* 批量创建 */ }
}
```

### PublishController
```php
class PublishController extends Controller
{
    public function kol() { /* KOL发布页 */ }
    public function personal() { /* 个人自媒体页 */ }
    public function aiTasks() { /* AI发布任务 */ }
    public function records() { /* 发布记录 */ }
    public function execute(Request $request) { /* 执行发布 */ }
}
```

### SeoSiteController
```php
class SeoSiteController extends Controller
{
    public function index() { /* 站点列表 */ }
    public function store(Request $request) { /* 添加站点 */ }
    public function update(Request $request, $id) { /* 更新站点 */ }
    public function destroy($id) { /* 删除站点 */ }
}
```

### DataController
```php
class DataController extends Controller
{
    public function indexCheck() { /* 收录查询页 */ }
    public function indexCheckStore(Request $request) { /* 执行查询 */ }
    public function indexRecords() { /* 查询记录 */ }
    public function keywordIndex() { /* 关键词指数 */ }
    public function aiExpand() { /* AI拓词页 */ }
    public function aiExpandStore(Request $request) { /* 执行拓词 */ }
}
```

---

## 导航重组实现

### 修改 header.blade.php 的 $menu 数组

```php
$menu = [
    'dashboard' => ['route' => 'admin.dashboard', 'name' => '首页', 'icon' => 'home'],
    'diagnosis' => ['route' => 'admin.diagnosis.add', 'name' => 'AI诊断', 'icon' => 'search'],
    'materials' => ['route' => 'admin.materials.index', 'name' => '素材准备', 'icon' => 'database'],
    'writing' => ['route' => 'admin.writing-instructions.index', 'name' => 'AI写作', 'icon' => 'pen-tool'],
    'viral' => ['route' => 'admin.viral.index', 'name' => '流量复刻', 'icon' => 'copy'],
    'publish' => ['route' => 'admin.publish.personal', 'name' => '发布管理', 'icon' => 'send'],
    'seo' => ['route' => 'admin.seo.sites.index', 'name' => 'AI官网SEO', 'icon' => 'globe'],
    'data' => ['route' => 'admin.data.index-check', 'name' => 'AI数据中心', 'icon' => 'bar-chart-2'],
    'geo-optimization' => ['route' => 'admin.geo-optimization.index', 'name' => 'GEO优化', 'icon' => 'zap'],
    'tasks' => ['route' => 'admin.tasks.index', 'name' => '任务管理', 'icon' => 'list'],
    'ai_config' => ['route' => 'admin.ai.configurator', 'name' => '系统设置', 'icon' => 'settings'],
];
```

### 更新 $subMap 映射

```php
$subMap = [
    // AI诊断
    'admin.diagnosis.add' => 'diagnosis',
    'admin.diagnosis.store' => 'diagnosis',
    'admin.diagnosis.reports' => 'diagnosis',
    'admin.diagnosis.show' => 'diagnosis',

    // 素材准备
    'admin.materials.index' => 'materials',
    'admin.keyword-libraries.*' => 'materials',
    'admin.title-libraries.*' => 'materials',
    'admin.image-libraries.*' => 'materials',
    'admin.knowledge-bases.*' => 'materials',
    'admin.url-import' => 'materials',

    // AI写作
    'admin.writing-instructions.*' => 'writing',
    'admin.writing-tasks.*' => 'writing',
    'admin.categories.*' => 'writing',
    'admin.articles.*' => 'writing',

    // 流量复刻
    'admin.viral.*' => 'viral',

    // 发布管理
    'admin.publish.*' => 'publish',
    'admin.distribution.*' => 'publish',

    // AI官网SEO
    'admin.seo.*' => 'seo',

    // AI数据中心
    'admin.data.*' => 'data',
    'admin.analytics' => 'data',

    // GEO优化
    'admin.geo-optimization.*' => 'geo-optimization',

    // 任务管理
    'admin.tasks.*' => 'tasks',

    // 系统设置
    'admin.ai.*' => 'ai_config',
    'admin.site-settings.*' => 'ai_config',
    'admin.security-settings.*' => 'ai_config',
    'admin.admin-users.*' => 'ai_config',
    'admin.api-tokens.*' => 'ai_config',
    'admin.admin-activity-logs' => 'ai_config',
];
```

---

## 消耗规则

| 操作 | 消耗点数 | 说明 |
|------|----------|------|
| AI蒸馏(生成问题) | 10点 | 从主关键词生成问题 |
| 查收录(单次) | 5点 | 检查单个问题收录 |
| 查收录联网 | 5点 | 联网检查收录 |
| AI写作(单篇) | 20点 | 生成一篇文章 |
| 流量复刻(单篇) | 15点 | 改写一篇文章 |
| AI可见度诊断 | 50点 | 完整诊断报告 |
| AI拓词 | 10点 | 扩展关键词 |
| 网站媒体发布 | 按媒体定价 | PR分发 |
| 自媒体大V发布 | 按KOL定价 | KOL分发 |
| 个人自媒体发布 | 0点 | 使用自己的账号 |

---

## 实施计划

### 第一阶段: 核心基础 (Week 1-2)
- [ ] 创建新增数据库表(migration)
- [ ] 实现写作指令系统
- [ ] 实现AI写作任务系统
- [ ] 重组导航结构

### 第二阶段: 内容生产 (Week 3-4)
- [ ] 实现流量复刻系统
- [ ] 实现AI收录查询
- [ ] 实现AI拓词功能

### 第三阶段: 分发系统 (Week 5-6)
- [ ] 实现个人自媒体发布(对接比特浏览器)
- [ ] 实现自媒体大V发布
- [ ] 实现AI智能发布任务

### 第四阶段: 检测分析 (Week 7-8)
- [ ] 实现AI可见度诊断
- [ ] 实现诊断报告
- [ ] 实现数据报表扩展

### 第五阶段: SEO与商业化 (Week 9-10)
- [ ] 实现AI官网SEO模块
- [ ] 实现点数计费系统
- [ ] 完善所有页面样式

---

## 文件清单

### 新增Blade模板
```
resources/views/admin/
├── diagnosis/
│   ├── add.blade.php              # AI可见度诊断
│   └── reports.blade.php          # 诊断报告
├── writing-instructions/
│   ├── index.blade.php            # 写作指令列表
│   ├── form.blade.php             # 写作指令表单
├── writing-tasks/
│   ├── index.blade.php            # 写作任务列表
│   ├── create.blade.php           # 创建任务
│   └── show.blade.php             # 任务详情
├── viral-replication/
│   ├── index.blade.php            # 爆文复刻列表
│   └── batch.blade.php            # 批量复刻
├── publish/
│   ├── kol.blade.php              # KOL发布
│   ├── personal.blade.php         # 个人自媒体
│   ├── ai-tasks.blade.php         # AI发布任务
│   └── records.blade.php          # 发布记录
├── seo/
│   ├── sites/
│   │   ├── index.blade.php        # 站点列表
│   │   └── form.blade.php         # 站点表单
│   ├── tasks/
│   │   ├── index.blade.php        # SEO任务列表
│   │   └── create.blade.php       # 创建任务
│   └── records.blade.php          # SEO记录
└── data/
    ├── index-check.blade.php      # 收录查询
    ├── index-records.blade.php    # 查询记录
    ├── keyword-index.blade.php    # 关键词指数
    └── ai-expand.blade.php        # AI拓词
```

### 新增Controller
```
app/Http/Controllers/Admin/
├── DiagnosisController.php
├── WritingInstructionController.php
├── WritingTaskController.php
├── ViralReplicationController.php
├── PublishController.php
├── SeoSiteController.php
├── SeoTaskController.php
└── DataController.php
```

### 新增Model
```
app/Models/
├── DiagnosisTask.php
├── DiagnosisResult.php
├── WritingInstruction.php
├── WritingTask.php
├── ViralReplication.php
├── BrowserProfile.php
├── PublishTask.php
├── PublishLog.php
├── SeoSite.php
├── SeoPublishTask.php
├── IndexCheck.php
├── IndexCheckDetail.php
├── UserPoints.php
└── PointTransaction.php
```

### 新增Migration
```
database/migrations/
├── xxxx_create_diagnosis_tasks_table.php
├── xxxx_create_diagnosis_results_table.php
├── xxxx_create_writing_instructions_table.php
├── xxxx_create_writing_tasks_table.php
├── xxxx_create_viral_replications_table.php
├── xxxx_create_browser_profiles_table.php
├── xxxx_create_publish_tasks_table.php
├── xxxx_create_publish_logs_table.php
├── xxxx_create_seo_sites_table.php
├── xxxx_create_seo_publish_tasks_table.php
├── xxxx_create_index_checks_table.php
├── xxxx_create_index_check_details_table.php
├── xxxx_create_user_points_table.php
└── xxxx_create_point_transactions_table.php
```

---

## 总结

本计划基于现有GEOFlow系统，完整复刻小汇萃GEO优化系统的全部功能:

1. **保留现有页面**: Dashboard、Analytics、Tasks、Distribution、Articles、Materials、AI配置、站点设置
2. **新增8个功能模块**: AI诊断、AI写作、流量复刻、发布管理、AI官网SEO、AI数据中心、点数系统
3. **重组导航结构**: 按照用户工作流程重新组织，分为11个功能区
4. **新增14个数据库表**: 支持所有新功能
5. **新增8个Controller**: 处理所有新业务逻辑
6. **新增14个Blade模板**: 展示所有新页面

预计10周完成全部开发。
