<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\BatchPublishLog;

class BrowserProfile extends Model
{
    protected $fillable = [
        'user_id',
        'platform',
        'profile_id',
        'profile_name',
        'account_name',
        'status',
        'daily_limit',
        'today_published',
        'last_used_at',
    ];

    protected $casts = [
        'daily_limit' => 'integer',
        'today_published' => 'integer',
        'last_used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function publishTasks(): HasMany
    {
        return $this->hasMany(PublishTask::class, 'profile_id');
    }

    public function isAuthorized(): bool
    {
        return $this->status === 'authorized';
    }

    public function canPublish(): bool
    {
        return $this->isAuthorized() && $this->getEffectiveTodayPublished() < $this->daily_limit;
    }

    /**
     * 从 BatchPublishLog 实时计算今日发布数（兼容旧数据）
     */
    public function getEffectiveTodayPublished(): int
    {
        $accountName = $this->account_name ?? $this->profile_name ?? '';
        if (!$accountName) {
            return $this->today_published ?? 0;
        }

        $logCount = BatchPublishLog::query()
            ->where('account_name', $accountName)
            ->whereDate('created_at', today())
            ->where('status', 'published')
            ->count();

        return max($logCount, $this->today_published ?? 0);
    }

    const PLATFORMS = [
        'weixin' => '微信公众号',
        'weibo' => '微博',
        'douyin' => '抖音',
        'kuaishou' => '快手',
        'xiaohongshu' => '小红书',
        'zhihu' => '知乎',
        'zhihu_answer' => '知乎答题',
        'zhihu_article' => '知乎专栏',
        'baidu' => '百家号',
        'baijia' => '百家号',
        'tencent' => '企鹅号',
        'toutiao' => '今日头条',
        'bilibili' => 'B站',
        'sohu' => '搜狐号',
        '163' => '网易号',
        'sina' => '新浪看点',
        'yidian' => '一点资讯',
        'qq' => 'QQ公众号',
        'wechat' => '微信',
        'douban' => '豆瓣',
        'tieba' => '百度贴吧',
    ];
}
