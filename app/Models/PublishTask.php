<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PublishTask extends Model
{
    protected $fillable = [
        'user_id',
        'article_id',
        'profile_id',
        'media_id',
        'platform',
        'publish_type',
        'status',
        'sync_status',
        'remote_article_id',
        'sync_error_message',
        'synced_at',
        'error_message',
        'published_url',
        'published_at',
    ];

    protected $casts = [
        'synced_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(BrowserProfile::class, 'profile_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(PublishLog::class, 'task_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    public function isSynced(): bool
    {
        return $this->sync_status === 'synced';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    const TYPES = [
        'personal' => '个人自媒体',
        'kol' => '自媒体大V',
        'webmedia' => '网站媒体',
    ];
}
