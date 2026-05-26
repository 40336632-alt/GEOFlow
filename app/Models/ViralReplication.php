<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ViralReplication extends Model
{
    protected $fillable = [
        'user_id',
        'source_url',
        'source_title',
        'source_content',
        'category_id',
        'image_library_id',
        'instruction_id',
        'rewritten_title',
        'rewritten_content',
        'status',
    ];

    protected $casts = [
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function imageLibrary(): BelongsTo
    {
        return $this->belongsTo(ImageLibrary::class);
    }

    public function instruction(): BelongsTo
    {
        return $this->belongsTo(WritingInstruction::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRewriting(): bool
    {
        return $this->status === 'rewriting';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
