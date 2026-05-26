<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeoSite extends Model
{
    protected $fillable = [
        'user_id',
        'site_type',
        'domain',
        'published_count',
        'remark',
    ];

    protected $casts = [
        'published_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function publishTasks(): HasMany
    {
        return $this->hasMany(SeoPublishTask::class, 'site_id');
    }

    const SITE_TYPES = [
        'wordpress' => 'WordPress',
        'typecho' => 'Typecho',
        'hexo' => 'Hexo',
        'hugo' => 'Hugo',
        'custom' => '自定义',
    ];
}
