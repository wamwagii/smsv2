<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exam extends Model
{
    use SoftDeletes;

    protected $table = 'exams';

    protected $fillable = [
        'name',
        'term',
        'academic_year_id',
        'start_date',
        'end_date',
        'total_marks',
        'passing_marks',
        'description',
        'status',
    ];

    protected $casts = [
        'start_date'    => 'date',
        'end_date'      => 'date',
        'total_marks'   => 'integer',
        'passing_marks' => 'integer',
    ];

    /* -----------------------------------------------------------------
     |  Relationships
     | -----------------------------------------------------------------
     */

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYears::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }

    /* -----------------------------------------------------------------
     |  Accessors
     | -----------------------------------------------------------------
     */

    public function getTermLabelAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', $this->term));
    }

    public function getStatusLabelAttribute(): string
    {
        return ucfirst($this->status);
    }

    public function getIsPublishedAttribute(): bool
    {
        return $this->status === 'published';
    }

    public function getIsOngoingAttribute(): bool
    {
        return $this->status === 'ongoing';
    }

    public function getIsUpcomingAttribute(): bool
    {
        return $this->status === 'upcoming';
    }

    /* -----------------------------------------------------------------
     |  Scopes
     | -----------------------------------------------------------------
     */

    /**
     * Exams that haven't finished yet (upcoming or ongoing).
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['upcoming', 'ongoing']);
    }

    /**
     * Exams that are currently running.
     */
    public function scopeOngoing($query)
    {
        return $query->where('status', 'ongoing');
    }

    /**
     * Exams that haven't started yet.
     * Uses whereDate so "today" still counts as upcoming.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('status', 'upcoming')
                     ->whereDate('start_date', '>=', today());
    }

    /**
     * Exams that have finished (completed or published).
     */
    public function scopeCompleted($query)
    {
        return $query->whereIn('status', ['completed', 'published']);
    }

    /**
     * Only published exams.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeForYear($query, int $yearId)
    {
        return $query->where('academic_year_id', $yearId);
    }

    public function scopeForTerm($query, string $term)
    {
        return $query->where('term', $term);
    }
}