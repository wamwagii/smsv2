<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guardian extends Authenticatable
{
    protected $table = 'parents';

    /**
     * Relationships considered "parent" and eligible to receive SMS.
     * Everyone else (guardian, other) is never contacted by SMS,
     * regardless of pivot opt-in or phone number.
     */
    public const SMS_RELATIONSHIPS = ['father', 'mother'];

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
     |  Scopes
     | -----------------------------------------------------------------
     */

    /**
     * Only rows whose relationship is eligible for SMS.
     * The single source of truth for the parents-only policy.
     */
    public function scopeReceivesSms(Builder $query): Builder
    {
        return $query->whereIn('relationship', self::SMS_RELATIONSHIPS);
    }

    /* -----------------------------------------------------------------
     |  Helpers
     | -----------------------------------------------------------------
     */

    /**
     * True when this contact is a parent (father or mother) and therefore
     * eligible to receive SMS under the current policy.
     */
    public function isParent(): bool
    {
        return in_array($this->relationship, self::SMS_RELATIONSHIPS, true);
    }

    public function isPrimaryContactFor(Student $student): bool
    {
        return $this->students()
            ->wherePivot('student_id', $student->id)
            ->wherePivot('is_primary_contact', true)
            ->exists();
    }

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

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}