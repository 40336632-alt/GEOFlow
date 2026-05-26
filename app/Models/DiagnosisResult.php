<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiagnosisResult extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'task_id',
        'query',
        'platform',
        'answer',
        'brand_mentioned',
        'mention_position',
        'screenshot_url',
        'created_at',
    ];

    protected $casts = [
        'brand_mentioned' => 'boolean',
        'mention_position' => 'integer',
        'created_at' => 'datetime',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(DiagnosisTask::class, 'task_id');
    }
}
