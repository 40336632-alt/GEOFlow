<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BatchPublishLog extends Model
{
    protected $table = 'batch_publish_logs';

    protected $fillable = [
        'article_id',
        'account_name',
        'account_id',
        'platform',
        'task_id',
        'title',
        'status',
        'verify_status',
        'source',
        'raw_data',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'raw_data' => 'array',
        ];
    }
}
