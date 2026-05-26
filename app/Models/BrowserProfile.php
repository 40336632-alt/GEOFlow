<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        return $this->isAuthorized() && $this->today_published < $this->daily_limit;
    }

    const PLATFORMS = [
        'weixin' => '微信公众号',
        'weibo' => '微博',
        'douyin' => '抖音',
        'kuaishou' => '快手',
        'xiaohongshu' => '小红书',
        'zhihu' => '知乎',
        'baidu' => '百家号',
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
