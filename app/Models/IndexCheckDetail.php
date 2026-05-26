<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndexCheckDetail extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'check_id',
        'platform',
        'is_indexed',
        'answer_text',
        'screenshot_url',
        'error_message',
        'checked_at',
    ];

    protected $casts = [
        'is_indexed' => 'boolean',
        'checked_at' => 'datetime',
    ];

    public function check(): BelongsTo
    {
        return $this->belongsTo(IndexCheck::class, 'check_id');
    }
}
