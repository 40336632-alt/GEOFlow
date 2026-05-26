<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiagnosisTask extends Model
{
    protected $fillable = [
        'user_id',
        'main_keyword',
        'brand_name',
        'column_a',
        'column_b',
        'column_c',
        'column_d',
        'column_e',
        'platforms',
        'status',
        'total_queries',
        'brand_mentioned',
        'visibility_score',
    ];

    protected $casts = [
        'column_a' => 'array',
        'column_b' => 'array',
        'column_e' => 'array',
        'platforms' => 'array',
        'total_queries' => 'integer',
        'brand_mentioned' => 'integer',
        'visibility_score' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(DiagnosisResult::class, 'task_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function getVisibilityRateAttribute(): float
    {
        if ($this->total_queries === 0) {
            return 0;
        }
        return round($this->brand_mentioned / $this->total_queries * 100, 1);
    }

    const PLATFORMS = [
        'deepseek' => 'DeepSeek',
        'doubao' => '豆包AI',
        'yuanbao' => '腾讯元宝',
        'tongyi' => '通义千问',
        'wenxin' => '文心一言',
        'nano' => '纳米AI',
        'kimi' => 'Kimi',
        'zhipu' => '智谱清言',
    ];
}
