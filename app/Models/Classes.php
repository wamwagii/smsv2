<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Classes extends Model
{
    protected $table = 'classes';

    protected $fillable = [
        'name',
        'level',
        'stream',
        'class_code',
        'capacity',
        'current_enrollment',
        'class_teacher_id',
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

    /**
     * Students in this class.
     * FK is 'class_id', NOT 'classes_id' (Laravel's auto-inference would
     * produce 'classes_id' from the class name 'Classes', which is wrong).
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    /**
     * The fee structure for this class.
     * Explicit FK to avoid inference issues.
     */
    public function feeStructure(): HasOne
    {
        return $this->hasOne(FeeStructure::class, 'class_id');
    }

    /**
     * All fee structures for this class (across academic years).
     */
    public function feeStructures(): HasMany
    {
        return $this->hasMany(FeeStructure::class, 'class_id');
    }

    /**
     * Invoices tied to this class.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'class_id');
    }

    /**
     * Results tied to this class.
     */
    public function results(): HasMany
    {
        return $this->hasMany(Result::class, 'class_id');
    }

    /**
     * Attendance records tied to this class.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'class_id');
    }

    /**
     * The class teacher (staff member).
     * Note: staff table uses 'staff' as table name, but the FK is 'class_teacher_id'.
     */
    public function classTeacher(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'class_teacher_id');
    }

    /* -----------------------------------------------------------------
     |  Accessors
     | -----------------------------------------------------------------
     */

    public function getGradeLevelAttribute(): int
    {
        return (int) $this->level;
    }

    /**
     * Full class label — e.g., "Grade 5A" if stream is set, else "Grade 5".
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->stream
            ? "{$this->name} {$this->stream}"
            : $this->name;
    }

    /* -----------------------------------------------------------------
     |  Scopes
     | -----------------------------------------------------------------
     */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeGrade($query, $level)
    {
        return $query->where('level', $level);
    }
}