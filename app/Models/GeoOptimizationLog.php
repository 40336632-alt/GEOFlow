<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeoOptimizationLog extends Model
{
    protected $table = 'geo_optimization_logs';

    protected $fillable = [
        'task_id',
        'article_id',
        'dataset',
        'engine_llm',
        'original_content',
        'optimized_content',
        'geo_scores',
        'status',
        'error_message',
        'duration_seconds',
    ];

    protected function casts(): array
    {
        return [
            'task_id' => 'integer',
            'article_id' => 'integer',
            'geo_scores' => 'array',
            'duration_seconds' => 'float',
        ];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'article_id');
    }
}
