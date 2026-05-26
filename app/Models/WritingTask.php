<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WritingTask extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'keyword_library_id',
        'category_id',
        'image_library_id',
        'image_count',
        'knowledge_base_id',
        'instruction_id',
        'max_articles',
        'created_count',
        'status',
        'error_message',
        'last_written_at',
    ];

    protected $casts = [
        'last_written_at' => 'datetime',
        'image_count' => 'integer',
        'max_articles' => 'integer',
        'created_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'writing_task_id');
    }

    public function keywordLibrary(): BelongsTo
    {
        return $this->belongsTo(KeywordLibrary::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function imageLibrary(): BelongsTo
    {
        return $this->belongsTo(ImageLibrary::class);
    }

    public function knowledgeBase(): BelongsTo
    {
        return $this->belongsTo(KnowledgeBase::class);
    }

    public function instruction(): BelongsTo
    {
        return $this->belongsTo(WritingInstruction::class);
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
}
