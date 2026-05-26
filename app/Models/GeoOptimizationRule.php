<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeoOptimizationRule extends Model
{
    protected $table = 'geo_optimization_rules';

    protected $fillable = [
        'name',
        'dataset',
        'rules',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'rules' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
