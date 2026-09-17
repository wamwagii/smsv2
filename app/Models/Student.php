<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Student extends Model
{
    use SoftDeletes;
    
    protected $table = 'students';
    
    protected $fillable = [
        'admission_number',
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'gender',
        'photo',
        'birth_certificate_number',
        'phone_number',
        'email',
        'physical_address',
        'class_id',
        'academic_year_id',
        'roll_number',
        'kcpse_index_number',
        'kcpe_grade',
        'kcpe_score',
        'father_name',
        'father_phone',
        'mother_name',
        'mother_phone',
        'guardian_name',
        'guardian_phone',
        'guardian_relation',
        'status',
        'enrollment_date',
        'graduation_date',
        'medical_notes',
    ];
    
    protected $casts = [
        'date_of_birth'   => 'date',
        'enrollment_date' => 'date',
        'graduation_date' => 'date',
        'deleted_at'      => 'datetime',
    ];
    
    /* -----------------------------------------------------------------
     |  Boot — auto-generate roll number
     | -----------------------------------------------------------------
     */
    
    protected static function booted(): void
    {
        static::creating(function (Student $student) {
            if (empty($student->roll_number)) {
                $student->roll_number = static::nextRollNumber(
                    $student->class_id,
                    $student->academic_year_id
                );
            }
        });
    }
    
    /* -----------------------------------------------------------------
     |  Relationships
     | -----------------------------------------------------------------
     */
    
    public function class(): BelongsTo
    {
        return $this->belongsTo(Classes::class);
    }
    
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYears::class);
    }
    
    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }
    
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
    
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
    
    /**
     * Parents / Guardians linked to this student.
     *
     * Pivot columns:
     *  - is_primary_contact     (bool)
     *  - receives_notifications (bool)
     */
    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(
                Guardian::class,
                'student_parent',
                'student_id',
                'parent_id'
            )
            ->withPivot(['is_primary_contact', 'receives_notifications'])
            ->withTimestamps();
    }
    
    /**
     * Alias for parents() — some parts of the app may call guardians().
     */
    public function guardians(): BelongsToMany
    {
        return $this->parents();
    }
    
    /* -----------------------------------------------------------------
     |  Roll number generation
     | -----------------------------------------------------------------
     */
    
    /**
     * Compute the next available roll number for a given class + academic year.
     *
     * Returns a zero-padded string ("01", "02", ..., "99", "100").
     * DB-agnostic — works on both SQLite and MySQL.
     *
     * The result is the max existing numeric roll number + 1 for that
     * (class, academic year) pair. Non-numeric roll numbers are ignored
     * (mapped to 0) so they don't break the sequence.
     */
    public static function nextRollNumber(?int $classId, ?int $academicYearId): ?string
    {
        if (!$classId || !$academicYearId) {
            return null;
        }
    
        $max = static::query()
            ->where('class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->whereNotNull('roll_number')
            ->pluck('roll_number')
            ->map(fn ($r) => (int) $r)
            ->max();
    
        return str_pad((string) (($max ?? 0) + 1), 2, '0', STR_PAD_LEFT);
    }
    
    /**
     * Bulk reassign roll numbers for a class + academic year, alphabetically.
     * Useful at the start of a new academic year or when re-sorting a class.
     *
     * Returns the number of students affected.
     */
    public static function reassignRollNumbers(int $classId, int $academicYearId): int
    {
        return DB::transaction(function () use ($classId, $academicYearId) {
            $students = static::query()
                ->where('class_id', $classId)
                ->where('academic_year_id', $academicYearId)
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();
    
            $counter = 1;
            foreach ($students as $student) {
                $student->roll_number = str_pad((string) $counter, 2, '0', STR_PAD_LEFT);
                $student->saveQuietly();
                $counter++;
            }
    
            return $students->count();
        });
    }
    
    /* -----------------------------------------------------------------
     |  Helpers
     | -----------------------------------------------------------------
     */
    
    /**
     * Get the primary contact parent/guardian for this student.
     */
    public function primaryParent(): ?Guardian
    {
        return $this->parents()
            ->wherePivot('is_primary_contact', true)
            ->first();
    }
    
    /**
     * Get all parents/guardians who should receive notifications.
     */
    public function notificationRecipients()
    {
        return $this->parents()
            ->wherePivot('receives_notifications', true)
            ->get();
    }
    
    /* -----------------------------------------------------------------
     |  Accessors
     | -----------------------------------------------------------------
     */
    
    /**
     * Full name accessor: "First Middle Last" (trims extra spaces).
     */
    public function getFullNameAttribute(): string
    {
        return trim(
            $this->first_name
            . ' ' . ($this->middle_name ? $this->middle_name . ' ' : '')
            . $this->last_name
        );
    }
    
    /* -----------------------------------------------------------------
     |  Scopes
     | -----------------------------------------------------------------
     */
    
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
    /* -----------------------------------------------------------------
 |  Fee balance helpers
 | -----------------------------------------------------------------
 */

/**
 * Total billed across all this student's invoices.
 */
public function getTotalBilledAttribute(): float
{
    return (float) $this->invoices()->sum('amount');
}

/**
 * Total paid across all this student's invoices.
 * Uses the invoices' own amount_paid column, so it stays in sync
 * with invoice-level partial-payment tracking.
 */
public function getTotalPaidAttribute(): float
{
    return (float) $this->invoices()->sum('amount_paid');
}

/**
 * Outstanding balance. Positive = owes money. Negative = overpaid.
 */
public function getBalanceAttribute(): float
{
    return round($this->total_billed - $this->total_paid, 2);
}

/**
 * True when the student owes money.
 */
public function getHasBalanceAttribute(): bool
{
    return $this->balance > 0.01;
}

/**
 * True when the student has paid exactly (or more than) what they owe
 * AND has at least one invoice.
 */
public function getIsFullyPaidAttribute(): bool
{
    return $this->invoices()->exists() && $this->balance <= 0.01;
}

/**
 * Number of unpaid (pending / partially_paid / overdue) invoices.
 */
public function getUnpaidInvoicesCountAttribute(): int
{
    return $this->invoices()
        ->whereIn('status', ['pending', 'partially_paid', 'overdue'])
        ->count();
}
}