<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\SeoPublishTask;
use App\Models\SeoSite;
use Illuminate\Http\Request;

class SeoController extends Controller
{
    // === 站点管理 ===

    public function sites()
    {
        $sites = SeoSite::where('user_id', auth('admin')->id())
            ->withCount('publishTasks')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.seo.sites.index', [
            'sites' => $sites,
            'siteTypes' => SeoSite::SITE_TYPES,
        ]);
    }

    public function createSite()
    {
        return view('admin.seo.sites.create', [
            'siteTypes' => SeoSite::SITE_TYPES,
        ]);
    }

    public function storeSite(Request $request)
    {
        $validated = $request->validate([
            'domain' => 'required|string|max:200',
            'site_type' => 'nullable|string|in:' . implode(',', array_keys(SeoSite::SITE_TYPES)),
            'remark' => 'nullable|string|max:500',
        ]);

        $validated['user_id'] = auth('admin')->id();

        SeoSite::create($validated);

        return redirect()->route('admin.seo.sites.index')
            ->with('message', '站点已添加');
    }

    public function editSite(SeoSite $seoSite)
    {
        if ($seoSite->user_id !== auth('admin')->id()) {
            abort(403);
        }
        return view('admin.seo.sites.edit', [
            'site' => $seoSite,
            'siteTypes' => SeoSite::SITE_TYPES,
        ]);
    }

    public function updateSite(Request $request, SeoSite $seoSite)
    {
        if ($seoSite->user_id !== auth('admin')->id()) {
            abort(403);
        }
        $validated = $request->validate([
            'domain' => 'required|string|max:200',
            'site_type' => 'nullable|string|in:' . implode(',', array_keys(SeoSite::SITE_TYPES)),
            'remark' => 'nullable|string|max:500',
        ]);

        $seoSite->update($validated);

        return redirect()->route('admin.seo.sites.index')
            ->with('message', '站点已更新');
    }

    public function destroySite(SeoSite $seoSite)
    {
        if ($seoSite->user_id !== auth('admin')->id()) {
            abort(403);
        }
        $seoSite->delete();

        return back()->with('message', '站点已删除');
    }

    // === SEO发布任务 ===

    public function tasks(Request $request)
    {
        $query = SeoPublishTask::with(['site', 'article'])
            ->where('user_id', auth('admin')->id());

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $tasks = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.seo.tasks.index', [
            'tasks' => $tasks,
            'currentStatus' => $request->get('status'),
        ]);
    }

    public function createTask()
    {
        return view('admin.seo.tasks.create', [
            'sites' => SeoSite::where('user_id', auth('admin')->id())->orderBy('domain')->get(),
            'articles' => Article::where('status', 'published')->orderBy('created_at', 'desc')->limit(100)->get(),
        ]);
    }

    public function storeTask(Request $request)
    {
        $validated = $request->validate([
            'site_id' => 'required|exists:seo_sites,id',
            'article_id' => 'required|exists:articles,id',
        ]);

        SeoPublishTask::create([
            'user_id' => auth('admin')->id(),
            'site_id' => $validated['site_id'],
            'article_id' => $validated['article_id'],
        ]);

        return redirect()->route('admin.seo.tasks.index')
            ->with('message', 'SEO发布任务已创建');
    }

    public function executeTask(SeoPublishTask $seoPublishTask)
    {
        if ($seoPublishTask->user_id !== auth('admin')->id()) {
            abort(403);
        }
        if (!$seoPublishTask->isPending() && !$seoPublishTask->isFailed()) {
            return back()->withErrors('只能执行待处理或失败的任务');
        }

        $seoPublishTask->update(['status' => 'running']);

        // TODO: Dispatch to publish service
        // For now, simulate completion
        $seoPublishTask->update([
            'status' => 'completed',
            'published_at' => now(),
            'published_url' => $seoPublishTask->site->domain . '/article/' . $seoPublishTask->article_id,
        ]);

        // Update site published count
        SeoSite::where('id', $seoPublishTask->site_id)
            ->increment('published_count');

        return back()->with('message', 'SEO发布任务已执行');
    }

    public function destroyTask(SeoPublishTask $seoPublishTask)
    {
        if ($seoPublishTask->user_id !== auth('admin')->id()) {
            abort(403);
        }
        $seoPublishTask->delete();

        return back()->with('message', 'SEO发布任务已删除');
    }
}
