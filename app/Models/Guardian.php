<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guardian extends Authenticatable
{
    protected $table = 'parents';
    
    protected $fillable = [
        'first_name',
        'last_name',
        'phone_number',
        'email',
        'password',
        'national_id',
        'physical_address',
        'relationship',
        'occupation',
        'status',
    ];
    
    protected $hidden = [
        'password',
        'remember_token',
    ];
    
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    
    /* -----------------------------------------------------------------
     |  Relationships
     | -----------------------------------------------------------------
     */
    
    /**
     * Students linked to this guardian through the student_parent pivot.
     *
     * Pivot columns:
     *  - is_primary_contact     (bool)
     *  - receives_notifications (bool)
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(
                Student::class,
                'student_parent',
                'parent_id',
                'student_id'
            )
            ->withPivot(['is_primary_contact', 'receives_notifications'])
            ->withTimestamps();
    }
    
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'parent_id');
    }
    
    /* -----------------------------------------------------------------
     |  Helpers
     | -----------------------------------------------------------------
     */
    
    /**
     * Check if this guardian is the primary contact for a given student.
     */
    public function isPrimaryContactFor(Student $student): bool
    {
        return $this->students()
            ->wherePivot('student_id', $student->id)
            ->wherePivot('is_primary_contact', true)
            ->exists();
    }
    
    /**
     * Students this guardian should receive notifications for.
     */
    public function notificationStudents()
    {
        return $this->students()
            ->wherePivot('receives_notifications', true)
            ->get();
    }
    
    /* -----------------------------------------------------------------
     |  Accessors
     | -----------------------------------------------------------------
     */
    
    /**
     * Full name accessor: "First Last".
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}