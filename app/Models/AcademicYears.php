<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYears extends Model
{
    protected $table = 'academic_years';

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'status',
        'is_current',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_current' => 'boolean',
    ];

    /* -----------------------------------------------------------------
     |  Relationships
     | -----------------------------------------------------------------
     */

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'academic_year_id');
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class, 'academic_year_id');
    }

    public function feeStructures(): HasMany
    {
        return $this->hasMany(FeeStructure::class, 'academic_year_id');
    }

    /* -----------------------------------------------------------------
     |  Accessors
     | -----------------------------------------------------------------
     */

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    public function getIsArchivedAttribute(): bool
    {
        return $this->status === 'archived';
    }

    /* -----------------------------------------------------------------
     |  Scopes
     | -----------------------------------------------------------------
     */

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /* -----------------------------------------------------------------
     |  Helpers
     | -----------------------------------------------------------------
     */

    /**
     * Whether this academic year has any dependent data.
     * Used to block deletion.
     */
    public function hasRelatedData(): bool
    {
        return $this->students()->exists()
            || $this->exams()->exists()
            || $this->feeStructures()->exists();
    }
}