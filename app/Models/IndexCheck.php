<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IndexCheck extends Model
{
    protected $fillable = [
        'user_id',
        'question',
        'brand_name',
        'platforms',
        'results',
        'total_indexed',
    ];

    protected $casts = [
        'platforms' => 'array',
        'results' => 'array',
        'total_indexed' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(IndexCheckDetail::class, 'check_id');
    }

    public function getIndexRateAttribute(): float
    {
        if (empty($this->platforms)) {
            return 0;
        }
        return round($this->total_indexed / count($this->platforms) * 100, 1);
    }
}
