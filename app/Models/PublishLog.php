<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublishLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'task_id',
        'action',
        'status',
        'detail',
        'screenshot_url',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(PublishTask::class, 'task_id');
    }
}
