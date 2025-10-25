<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Banner extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'image',
        'url',
        'is_active',
        'display_order',
        'start_date',
        'end_date',
        'target_platform',
        'metadata'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'metadata' => 'array',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function($q) {
                $now = now();
                $q->whereNull('start_date')
                  ->orWhere('start_date', '<=', $now);
            })
            ->where(function($q) {
                $now = now();
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', $now);
            });
    }

    public function scopeForPlatform($query, $platform = 'all')
    {
        return $query->whereIn('target_platform', ['all', $platform]);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('created_at', 'desc');
    }

    // Helper methods
    public function isActive()
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        
        if ($this->start_date && $this->start_date->gt($now)) {
            return false;
        }

        if ($this->end_date && $this->end_date->lt($now)) {
            return false;
        }

        return true;
    }

    // Accessors
    public function getImageUrlAttribute()
    {
        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }
        
        return asset('storage/' . $this->image);
    }
}
