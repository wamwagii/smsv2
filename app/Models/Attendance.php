<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $table = 'attendances';

    protected $fillable = [
        'student_id',
        'class_id',
        'date',
        'status',
        'arrival_time',
        'departure_time',
        'reason',
        'marked_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /* -----------------------------------------------------------------
     |  Relationships
     | -----------------------------------------------------------------
     */

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(Classes::class);
    }

    public function markedBy(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'marked_by');
    }

    /* -----------------------------------------------------------------
     |  Scopes
     | -----------------------------------------------------------------
     */

    public function scopeOnDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    public function scopeForClass($query, int $classId)
    {
        return $query->where('class_id', $classId);
    }

    public function scopeForStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopePresent($query)
    {
        return $query->where('status', 'present');
    }

    public function scopeAbsent($query)
    {
        return $query->whereIn('status', ['absent', 'late', 'excused']);
    }

    /* -----------------------------------------------------------------
     |  Accessors
     | -----------------------------------------------------------------
     */

    public function getStatusLabelAttribute(): string
    {
        return ucfirst($this->status ?? '-');
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'present' => 'success',
            'absent'  => 'danger',
            'late'    => 'warning',
            'excused' => 'info',
            'holiday' => 'gray',
            default   => 'gray',
        };
    }

    public function getIsPresentAttribute(): bool
    {
        return $this->status === 'present';
    }
}