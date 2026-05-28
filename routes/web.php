<?php

/**
 * Web 路由：前台与 Blade 管理后台（路径见 config/geoflow.admin_base_path，默认 geo_admin）。
 */

use App\Http\Controllers\Admin\AdminActivityLogController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminWelcomeController;
use App\Http\Controllers\Admin\AiModelController;
use App\Http\Controllers\Admin\AiPromptController;
use App\Http\Controllers\Admin\AiSpecialPromptController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\ApiTokenController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\AuthorController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GeoOptimizationController;
use App\Http\Controllers\Admin\ImageLibraryController;
use App\Http\Controllers\Admin\KeywordLibraryController;
use App\Http\Controllers\Admin\KnowledgeBaseController;
use App\Http\Controllers\Admin\LegacyController;
use App\Http\Controllers\Admin\WritingInstructionController;
use App\Http\Controllers\Admin\WritingTaskController;
use App\Http\Controllers\Admin\ViralReplicationController;
use App\Http\Controllers\Admin\IndexCheckController;
use App\Http\Controllers\Admin\AiKeywordExpandController;
use App\Http\Controllers\Admin\BrowserProfileController;
use App\Http\Controllers\Admin\PublishTaskController;
use App\Http\Controllers\Admin\DiagnosisController;
use App\Http\Controllers\Admin\SeoController;
use App\Http\Controllers\Admin\WorkflowGuideController;
use App\Http\Controllers\Admin\MaterialsController;
use App\Http\Controllers\Admin\SecuritySettingsController;
use App\Http\Controllers\Admin\SiteSettingsController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\TitleLibraryController;
use App\Http\Controllers\Admin\TuozhanController;
use App\Http\Controllers\Admin\UrlImportController;
use App\Http\Controllers\Site\ArchiveController;
use App\Http\Controllers\Site\ArticleController as SiteArticleController;
use App\Http\Controllers\Site\CategoryController as SiteCategoryController;
use App\Http\Controllers\Site\HomeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware(['site.locale', 'site.view_log'])->group(function (): void {
    Route::get('/', [HomeController::class, 'index'])->name('site.home');
    Route::get('/archive', [ArchiveController::class, 'index'])->name('site.archive');
    Route::get('/archive/{year}/{month}', [ArchiveController::class, 'month'])
        ->name('site.archive.month')
        ->where(['year' => '[0-9]{4}', 'month' => '[0-9]{2}']);
    Route::get('/category/{slug}', [SiteCategoryController::class, 'show'])->name('site.category');
    Route::get('/article/{slug}', [SiteArticleController::class, 'show'])->name('site.article');
});

$adminPrefix = trim((string) config('geoflow.admin_base_path', '/geo_admin'), '/');

Route::prefix($adminPrefix)->name('admin.')->middleware(['admin.locale'])->group(function () {
    // 通用入口与语言切换
    Route::get('locale/{locale}', [AdminAuthController::class, 'switchLocale'])->name('locale.switch');

    Route::get('/', function () {
        return Auth::guard('admin')->check()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('admin.login');
    })->name('entry');

    // 访客认证路由
    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('login');
        Route::post('login', [AdminAuthController::class, 'login'])->name('login.attempt');
    });

    // 后台受保护路由
    Route::middleware(['admin.auth', 'admin.activity'])->group(function () {
        // 会话与首页
        Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::post('welcome/dismiss', [AdminWelcomeController::class, 'dismiss'])->name('welcome.dismiss');
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('workflow', [WorkflowGuideController::class, 'index'])->name('workflow');
        Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics');

        // 任务管理（Blade 新路径）
        Route::prefix('tasks')->name('tasks.')->group(function () {
            Route::get('/', [TaskController::class, 'index'])->name('index');
            Route::post('{taskId}/toggle-status', [TaskController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('{taskId}/delete', [TaskController::class, 'destroyTask'])->name('delete');
            Route::get('create', [TaskController::class, 'create'])->name('create');
            Route::post('create', [TaskController::class, 'store'])->name('store');
            Route::get('{taskId}/edit', [TaskController::class, 'edit'])->name('edit');
            Route::put('{taskId}', [TaskController::class, 'update'])->name('update');
            Route::get('health-check', [TaskController::class, 'healthCheck'])->name('health');
            Route::post('batch/start', [TaskController::class, 'batchAction'])->name('batch');
        });

        // GEO优化管理
        Route::prefix('geo-optimization')->name('geo-optimization.')->group(function () {
            Route::get('/', [GeoOptimizationController::class, 'index'])->name('index');
            Route::get('rules', [GeoOptimizationController::class, 'rules'])->name('rules');
            Route::post('rules', [GeoOptimizationController::class, 'storeRule'])->name('rules.store');
            Route::post('rules/{ruleId}/toggle', [GeoOptimizationController::class, 'toggleRule'])->name('rules.toggle')->whereNumber('ruleId');
            Route::post('rules/{ruleId}/delete', [GeoOptimizationController::class, 'destroyRule'])->name('rules.delete')->whereNumber('ruleId');
            Route::get('health', [GeoOptimizationController::class, 'healthCheck'])->name('health');
            Route::get('progress', [GeoOptimizationController::class, 'progress'])->name('progress');
        });

        // 文章管理（Blade 新路径）
        Route::prefix('articles')->name('articles.')->group(function () {
            Route::get('/', [ArticleController::class, 'index'])->name('index');
            Route::post('batch/update-status', [ArticleController::class, 'batchUpdateStatus'])->name('batch.update-status');
            Route::post('batch/update-review', [ArticleController::class, 'batchUpdateReview'])->name('batch.update-review');
            Route::post('batch/delete', [ArticleController::class, 'batchDelete'])->name('batch.delete');
            Route::post('batch/geo-optimize', [ArticleController::class, 'batchGeoOptimize'])->name('batch.geo-optimize');
            Route::post('batch/supplement-publish', [ArticleController::class, 'batchSupplementPublish'])->name('batch.supplement-publish');
            Route::post('batch/restore', [ArticleController::class, 'batchRestore'])->name('batch.restore');
            Route::post('batch/force-delete', [ArticleController::class, 'batchForceDelete'])->name('batch.force-delete');
            Route::post('trash/empty', [ArticleController::class, 'emptyTrash'])->name('trash.empty');
            Route::get('create', [ArticleController::class, 'create'])->name('create');
            Route::post('create', [ArticleController::class, 'store'])->name('store');
            Route::post('{articleId}/restore', [ArticleController::class, 'restore'])->name('restore')->whereNumber('articleId');
            Route::post('{articleId}/force-delete', [ArticleController::class, 'forceDelete'])->name('force-delete')->whereNumber('articleId');
            Route::get('{articleId}/edit', [ArticleController::class, 'edit'])->name('edit');
            Route::put('{articleId}', [ArticleController::class, 'update'])->name('update');
        });

        // 栏目管理（保持 geo_admin/categories 路径语义）
        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/', [CategoryController::class, 'index'])->name('index');
            Route::get('create', [CategoryController::class, 'create'])->name('create');
            Route::post('create', [CategoryController::class, 'store'])->name('store');
            Route::get('{categoryId}/edit', [CategoryController::class, 'edit'])->name('edit');
            Route::put('{categoryId}', [CategoryController::class, 'update'])->name('update');
            Route::post('{categoryId}/delete', [CategoryController::class, 'destroy'])->name('delete');
        });

        // 素材管理：作者管理
        Route::prefix('authors')->name('authors.')->group(function () {
            Route::get('/', [AuthorController::class, 'index'])->name('index');
            Route::get('create', [AuthorController::class, 'create'])->name('create');
            Route::post('create', [AuthorController::class, 'store'])->name('store');
            Route::get('{authorId}/edit', [AuthorController::class, 'edit'])->name('edit');
            Route::get('{authorId}/detail', [AuthorController::class, 'detail'])->name('detail');
            Route::put('{authorId}', [AuthorController::class, 'update'])->name('update');
            Route::post('{authorId}/delete', [AuthorController::class, 'destroy'])->name('delete');
        });

        // 素材管理：关键词库管理
        Route::prefix('keyword-libraries')->name('keyword-libraries.')->group(function () {
            Route::get('/', [KeywordLibraryController::class, 'index'])->name('index');
            Route::get('create', [KeywordLibraryController::class, 'create'])->name('create');
            Route::post('create', [KeywordLibraryController::class, 'store'])->name('store');
            Route::get('{libraryId}/edit', [KeywordLibraryController::class, 'edit'])->name('edit');
            Route::get('{libraryId}/detail', [KeywordLibraryController::class, 'detail'])->name('detail');
            Route::post('{libraryId}/keywords', [KeywordLibraryController::class, 'storeKeyword'])->name('keywords.store');
            Route::post('{libraryId}/keywords/delete', [KeywordLibraryController::class, 'destroyKeywords'])->name('keywords.delete');
            Route::post('{libraryId}/import', [KeywordLibraryController::class, 'importKeywords'])->name('import');
            Route::put('{libraryId}/detail', [KeywordLibraryController::class, 'updateFromDetail'])->name('detail.update');
            Route::put('{libraryId}', [KeywordLibraryController::class, 'update'])->name('update');
            Route::post('{libraryId}/delete', [KeywordLibraryController::class, 'destroy'])->name('delete');
        });

        // 素材管理：标题库管理
        Route::prefix('title-libraries')->name('title-libraries.')->group(function () {
            Route::get('/', [TitleLibraryController::class, 'index'])->name('index');
            Route::get('create', [TitleLibraryController::class, 'create'])->name('create');
            Route::post('create', [TitleLibraryController::class, 'store'])->name('store');
            Route::get('{libraryId}/edit', [TitleLibraryController::class, 'edit'])->name('edit');
            Route::get('{libraryId}/detail', [TitleLibraryController::class, 'detail'])->name('detail');
            Route::post('{libraryId}/titles', [TitleLibraryController::class, 'storeTitle'])->name('titles.store');
            Route::post('{libraryId}/titles/delete', [TitleLibraryController::class, 'destroyTitles'])->name('titles.delete');
            Route::post('{libraryId}/import', [TitleLibraryController::class, 'importTitles'])->name('import');
            Route::get('{libraryId}/ai-generate', [TitleLibraryController::class, 'aiGenerate'])->name('ai-generate');
            Route::post('{libraryId}/ai-generate', [TitleLibraryController::class, 'generateWithAi'])->name('ai-generate.submit');
            Route::put('{libraryId}', [TitleLibraryController::class, 'update'])->name('update');
            Route::post('{libraryId}/delete', [TitleLibraryController::class, 'destroy'])->name('delete');
        });

        // 素材管理：图片库管理
        Route::prefix('image-libraries')->name('image-libraries.')->group(function () {
            Route::get('/', [ImageLibraryController::class, 'index'])->name('index');
            Route::get('create', [ImageLibraryController::class, 'create'])->name('create');
            Route::post('create', [ImageLibraryController::class, 'store'])->name('store');
            Route::get('{libraryId}/edit', [ImageLibraryController::class, 'edit'])->name('edit');
            Route::get('{libraryId}/detail', [ImageLibraryController::class, 'detail'])->name('detail');
            Route::post('{libraryId}/images/upload', [ImageLibraryController::class, 'uploadImages'])->name('images.upload');
            Route::post('{libraryId}/images/delete', [ImageLibraryController::class, 'destroyImages'])->name('images.delete');
            Route::put('{libraryId}/detail', [ImageLibraryController::class, 'updateFromDetail'])->name('detail.update');
            Route::put('{libraryId}', [ImageLibraryController::class, 'update'])->name('update');
            Route::post('{libraryId}/delete', [ImageLibraryController::class, 'destroy'])->name('delete');
        });

        // 素材管理：知识库管理
        Route::prefix('knowledge-bases')->name('knowledge-bases.')->group(function () {
            Route::get('/', [KnowledgeBaseController::class, 'index'])->name('index');
            Route::get('create', [KnowledgeBaseController::class, 'create'])->name('create');
            Route::post('create', [KnowledgeBaseController::class, 'store'])->name('store');
            Route::get('{knowledgeBaseId}/edit', [KnowledgeBaseController::class, 'edit'])->name('edit');
            Route::get('{knowledgeBaseId}/detail', [KnowledgeBaseController::class, 'detail'])->name('detail');
            Route::post('upload', [KnowledgeBaseController::class, 'uploadFile'])->name('upload');
            Route::post('{knowledgeBaseId}/chunks/refresh', [KnowledgeBaseController::class, 'refreshChunks'])->name('chunks.refresh');
            Route::put('{knowledgeBaseId}/detail', [KnowledgeBaseController::class, 'updateFromDetail'])->name('detail.update');
            Route::put('{knowledgeBaseId}', [KnowledgeBaseController::class, 'update'])->name('update');
            Route::post('{knowledgeBaseId}/delete', [KnowledgeBaseController::class, 'destroy'])->name('delete');
        });

        // 写作指令管理
        Route::resource('writing-instructions', WritingInstructionController::class)->except(['show']);

        // 写作任务管理
        Route::prefix('writing-tasks')->name('writing-tasks.')->group(function () {
            Route::get('/', [WritingTaskController::class, 'index'])->name('index');
            Route::get('create', [WritingTaskController::class, 'create'])->name('create');
            Route::post('/', [WritingTaskController::class, 'store'])->name('store');
            Route::get('{writingTask}', [WritingTaskController::class, 'show'])->name('show');
            Route::post('{writingTask}/execute', [WritingTaskController::class, 'execute'])->name('execute');
            Route::delete('{writingTask}', [WritingTaskController::class, 'destroy'])->name('destroy');
        });

        // 流量复刻管理
        Route::prefix('viral-replication')->name('viral.')->group(function () {
            Route::get('/', [ViralReplicationController::class, 'index'])->name('index');
            Route::post('/', [ViralReplicationController::class, 'store'])->name('store');
            Route::get('batch', [ViralReplicationController::class, 'batch'])->name('batch');
            Route::post('batch', [ViralReplicationController::class, 'batchStore'])->name('batchStore');
            Route::delete('{viralReplication}', [ViralReplicationController::class, 'destroy'])->name('destroy');
        });

        // AI收录查询
        Route::prefix('index-check')->name('index-check.')->group(function () {
            Route::get('/', [IndexCheckController::class, 'index'])->name('index');
            Route::get('create', [IndexCheckController::class, 'create'])->name('create');
            Route::post('/', [IndexCheckController::class, 'store'])->name('store');
            Route::get('{indexCheck}', [IndexCheckController::class, 'show'])->name('show');
            Route::delete('{indexCheck}', [IndexCheckController::class, 'destroy'])->name('destroy');
        });

        // AI智能拓词
        Route::prefix('ai-expand')->name('ai-expand.')->group(function () {
            Route::get('/', [AiKeywordExpandController::class, 'index'])->name('index');
            Route::post('/', [AiKeywordExpandController::class, 'expand'])->name('expand');
        });

        // 参考站拓词工具
        Route::prefix('tuozhan')->name('tuozhan.')->group(function () {
            Route::get('/', [TuozhanController::class, 'index'])->name('index');
            Route::post('generate', [TuozhanController::class, 'generate'])->name('generate');
            Route::post('save', [TuozhanController::class, 'save'])->name('save');
        });

        // 发布看板
        Route::get('publish-dashboard', [PublishTaskController::class, 'dashboard'])->name('publish.dashboard');

        // 浏览器账号管理
        Route::prefix('browser-profiles')->name('browser-profiles.')->group(function () {
            Route::get('/', [BrowserProfileController::class, 'index'])->name('index');
            Route::get('create', [BrowserProfileController::class, 'create'])->name('create');
            Route::post('/', [BrowserProfileController::class, 'store'])->name('store');
            Route::get('{browserProfile}/edit', [BrowserProfileController::class, 'edit'])->name('edit');
            Route::put('{browserProfile}', [BrowserProfileController::class, 'update'])->name('update');
            Route::delete('{browserProfile}', [BrowserProfileController::class, 'destroy'])->name('destroy');
            Route::get('sync', [BrowserProfileController::class, 'sync'])->name('sync');
        });

        // 发布任务管理
        Route::prefix('publish-tasks')->name('publish-tasks.')->group(function () {
            Route::get('/', [PublishTaskController::class, 'index'])->name('index');
            Route::get('create', [PublishTaskController::class, 'create'])->name('create');
            Route::post('/', [PublishTaskController::class, 'store'])->name('store');
            Route::get('{publishTask}', [PublishTaskController::class, 'show'])->name('show');
            Route::post('{publishTask}/execute', [PublishTaskController::class, 'execute'])->name('execute');
            Route::delete('{publishTask}', [PublishTaskController::class, 'destroy'])->name('destroy');
        });

        // AI可见度诊断
        Route::prefix('diagnosis')->name('diagnosis.')->group(function () {
            Route::get('/', [DiagnosisController::class, 'index'])->name('index');
            Route::get('create', [DiagnosisController::class, 'create'])->name('create');
            Route::post('/', [DiagnosisController::class, 'store'])->name('store');
            Route::get('{diagnosisTask}', [DiagnosisController::class, 'show'])->name('show');
            Route::post('{diagnosisTask}/execute', [DiagnosisController::class, 'execute'])->name('execute');
            Route::delete('{diagnosisTask}', [DiagnosisController::class, 'destroy'])->name('destroy');
            Route::get('{diagnosisTask}/report', [DiagnosisController::class, 'report'])->name('report');
        });

        // AI官网SEO - 站点管理
        Route::prefix('seo/sites')->name('seo.sites.')->group(function () {
            Route::get('/', [SeoController::class, 'sites'])->name('index');
            Route::get('create', [SeoController::class, 'createSite'])->name('create');
            Route::post('/', [SeoController::class, 'storeSite'])->name('store');
            Route::get('{seoSite}/edit', [SeoController::class, 'editSite'])->name('edit');
            Route::put('{seoSite}', [SeoController::class, 'updateSite'])->name('update');
            Route::delete('{seoSite}', [SeoController::class, 'destroySite'])->name('destroy');
        });

        // AI官网SEO - 发布任务
        Route::prefix('seo/tasks')->name('seo.tasks.')->group(function () {
            Route::get('/', [SeoController::class, 'tasks'])->name('index');
            Route::get('create', [SeoController::class, 'createTask'])->name('create');
            Route::post('/', [SeoController::class, 'storeTask'])->name('store');
            Route::post('{seoPublishTask}/execute', [SeoController::class, 'executeTask'])->name('execute');
            Route::delete('{seoPublishTask}', [SeoController::class, 'destroyTask'])->name('destroy');
        });

        // 业务页面
        Route::get('materials', [MaterialsController::class, 'index'])->name('materials.index');
        Route::get('url-import', [UrlImportController::class, 'index'])->name('url-import');
        Route::post('url-import', [UrlImportController::class, 'store'])->name('url-import.store');
        Route::get('url-import/history', [UrlImportController::class, 'history'])->name('url-import.history');
        Route::post('url-import/{jobId}/run', [UrlImportController::class, 'run'])
            ->name('url-import.run')
            ->whereNumber('jobId');
        Route::get('url-import/{jobId}/status', [UrlImportController::class, 'status'])
            ->name('url-import.status')
            ->whereNumber('jobId');
        Route::post('url-import/{jobId}/commit', [UrlImportController::class, 'commit'])
            ->name('url-import.commit')
            ->whereNumber('jobId');
        Route::get('url-import/{jobId}', [UrlImportController::class, 'show'])
            ->name('url-import.show')
            ->whereNumber('jobId');

        // AI 配置模块（配置器 / 模型 / 提示词）
        Route::group([], function () {
            Route::get('ai-configurator', [LegacyController::class, 'aiConfigurator'])->name('ai.configurator');
            Route::prefix('ai-models')->name('ai-models.')->group(function () {
                Route::get('/', [AiModelController::class, 'index'])->name('index');
                Route::post('create', [AiModelController::class, 'store'])->name('store');
                Route::put('{modelId}', [AiModelController::class, 'update'])->name('update');
                Route::post('{modelId}/test', [AiModelController::class, 'testConnection'])->name('test');
                Route::post('{modelId}/delete', [AiModelController::class, 'destroy'])->name('delete');
                Route::post('default-embedding', [AiModelController::class, 'updateDefaultEmbedding'])->name('default-embedding');
                Route::post('chunking-config', [AiModelController::class, 'updateChunkingConfig'])->name('chunking-config');
            });
            Route::get('ai-prompts', [AiPromptController::class, 'index'])->name('ai-prompts');
            Route::post('ai-prompts/create', [AiPromptController::class, 'store'])->name('ai-prompts.store');
            Route::put('ai-prompts/{promptId}', [AiPromptController::class, 'update'])->name('ai-prompts.update');
            Route::post('ai-prompts/{promptId}/delete', [AiPromptController::class, 'destroy'])->name('ai-prompts.delete');
            Route::get('ai-special-prompts', [AiSpecialPromptController::class, 'index'])->name('ai-special-prompts');
            Route::post('ai-special-prompts/keyword', [AiSpecialPromptController::class, 'updateKeyword'])->name('ai-special-prompts.keyword');
            Route::post('ai-special-prompts/description', [AiSpecialPromptController::class, 'updateDescription'])->name('ai-special-prompts.description');
        });

        Route::prefix('site-settings')->name('site-settings.')->group(function () {
            Route::get('/', [SiteSettingsController::class, 'index'])->name('index');
            Route::post('/', [SiteSettingsController::class, 'update'])->name('update');
            Route::post('theme', [SiteSettingsController::class, 'updateTheme'])->name('theme');
            Route::post('article-detail-ads', [SiteSettingsController::class, 'updateArticleDetailAds'])->name('ads');
            Route::get('sensitive-words', [SecuritySettingsController::class, 'index'])->name('sensitive-words');
            Route::post('sensitive-words', [SecuritySettingsController::class, 'storeSensitiveWords'])->name('sensitive-words.store');
            Route::post('sensitive-words/{wordId}/delete', [SecuritySettingsController::class, 'destroySensitiveWord'])
                ->name('sensitive-words.delete')
                ->whereNumber('wordId');
        });
        Route::prefix('security-settings')->name('security-settings.')->group(function () {
            Route::get('/', fn () => redirect()->route('admin.site-settings.sensitive-words'))->name('index');
            Route::post('sensitive-words', [SecuritySettingsController::class, 'storeSensitiveWords'])->name('words.store');
            Route::post('sensitive-words/{wordId}/delete', [SecuritySettingsController::class, 'destroySensitiveWord'])->name('words.delete');
            Route::post('password', [SecuritySettingsController::class, 'updatePassword'])->name('password.update');
        });

        // 超级管理员功能
        Route::middleware('admin.super')->group(function () {
            Route::prefix('admin-users')->name('admin-users.')->group(function () {
                Route::get('/', [AdminUserController::class, 'index'])->name('index');
                Route::post('create', [AdminUserController::class, 'store'])->name('store');
                Route::post('{adminId}/update', [AdminUserController::class, 'update'])->name('update');
                Route::post('{adminId}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('toggle-status');
                Route::post('{adminId}/delete', [AdminUserController::class, 'destroy'])->name('delete');
            });
            Route::get('admin-activity-logs', [AdminActivityLogController::class, 'index'])->name('admin-activity-logs');
            Route::prefix('api-tokens')->name('api-tokens.')->group(function () {
                Route::get('/', [ApiTokenController::class, 'index'])->name('index');
                Route::post('/', [ApiTokenController::class, 'store'])->name('store');
                Route::post('{tokenId}/revoke', [ApiTokenController::class, 'revoke'])->name('revoke');
            });
        });
    });
});
