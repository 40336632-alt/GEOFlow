<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BrowserProfile;
use App\Services\BitBrowserService;
use Illuminate\Http\Request;

class BrowserProfileController extends Controller
{
    protected BitBrowserService $bitBrowserService;

    public function __construct(BitBrowserService $bitBrowserService)
    {
        $this->bitBrowserService = $bitBrowserService;
    }

    public function index(Request $request)
    {
        $query = BrowserProfile::where('user_id', auth('admin')->id());

        if ($platform = $request->get('platform')) {
            $query->where('platform', $platform);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $profiles = $query->orderBy('created_at', 'desc')->paginate(15);

        $bitBrowserRunning = $this->bitBrowserService->isRunning();

        return view('admin.publish.browser-profiles.index', [
            'profiles' => $profiles,
            'platforms' => BrowserProfile::PLATFORMS,
            'currentPlatform' => $request->get('platform'),
            'currentStatus' => $request->get('status'),
            'bitBrowserRunning' => $bitBrowserRunning,
        ]);
    }

    public function create()
    {
        return view('admin.publish.browser-profiles.create', [
            'platforms' => BrowserProfile::PLATFORMS,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'platform' => 'required|string|in:' . implode(',', array_keys(BrowserProfile::PLATFORMS)),
            'profile_id' => 'required|string|max:100',
            'profile_name' => 'nullable|string|max:200',
            'account_name' => 'nullable|string|max:200',
            'daily_limit' => 'nullable|integer|min:1|max:50',
        ]);

        $validated['user_id'] = auth('admin')->id();

        BrowserProfile::create($validated);

        return redirect()->route('admin.browser-profiles.index')
            ->with('message', '浏览器配置已添加');
    }

    public function edit(BrowserProfile $browserProfile)
    {
        if ($browserProfile->user_id !== auth('admin')->id()) {
            abort(403);
        }
        return view('admin.publish.browser-profiles.edit', [
            'profile' => $browserProfile,
            'platforms' => BrowserProfile::PLATFORMS,
        ]);
    }

    public function update(Request $request, BrowserProfile $browserProfile)
    {
        if ($browserProfile->user_id !== auth('admin')->id()) {
            abort(403);
        }
        $validated = $request->validate([
            'profile_name' => 'nullable|string|max:200',
            'account_name' => 'nullable|string|max:200',
            'daily_limit' => 'nullable|integer|min:1|max:50',
            'status' => 'nullable|string|in:authorized,expired,disabled',
        ]);

        $browserProfile->update($validated);

        return redirect()->route('admin.browser-profiles.index')
            ->with('message', '浏览器配置已更新');
    }

    public function destroy(BrowserProfile $browserProfile)
    {
        if ($browserProfile->user_id !== auth('admin')->id()) {
            abort(403);
        }
        $browserProfile->delete();

        return back()->with('message', '浏览器配置已删除');
    }

    public function sync()
    {
        $userId = auth('admin')->id();

        // 检查BitBrowser是否运行
        if (!$this->bitBrowserService->isRunning()) {
            return back()->withErrors('BitBrowser未运行，请先启动BitBrowser客户端');
        }

        // 同步浏览器配置
        $result = $this->bitBrowserService->syncProfiles($userId);

        return back()->with('message', "已同步 {$result['synced']} 个浏览器配置");
    }
}
