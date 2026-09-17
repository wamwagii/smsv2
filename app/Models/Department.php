<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Department extends Model
{
    protected $table = 'departments';

    protected $fillable = [
        'name',
        'code',
        'head_of_department_id',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /* -----------------------------------------------------------------
     |  Relationships
     | -----------------------------------------------------------------
     */

    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class);
    }

    public function headOfDepartment(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'head_of_department_id');
    }

    /* -----------------------------------------------------------------
     |  Accessors
     | -----------------------------------------------------------------
     */

    public function getFullNameAttribute(): ?string
    {
        return $this->headOfDepartment?->full_name;
    }

    /* -----------------------------------------------------------------
     |  Boot — keep navigation badge cache fresh
     | -----------------------------------------------------------------
     */

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('departments.active_count'));
        static::deleted(fn () => Cache::forget('departments.active_count'));
    }
}