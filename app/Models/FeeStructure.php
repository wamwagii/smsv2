<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeStructure extends Model
{
    protected $table = 'fee_structures';

    protected $fillable = [
        'class_id',
        'academic_year_id',
        'tuition_fees',
        'activity_fees',
        'library_fees',
        'sports_fees',
        'medical_fees',
        'transport_fees',
        'boarding_fees',
        'uniform_fees',
        'other_fees',
        'is_active',
        'payment_plan',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'payment_plan' => 'array',
        'tuition_fees' => 'decimal:2',
        'activity_fees' => 'decimal:2',
        'library_fees' => 'decimal:2',
        'sports_fees' => 'decimal:2',
        'medical_fees' => 'decimal:2',
        'transport_fees' => 'decimal:2',
        'boarding_fees' => 'decimal:2',
        'uniform_fees' => 'decimal:2',
        'other_fees' => 'decimal:2',
    ];

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

    /* -----------------------------------------------------------------
     |  Accessors
     | -----------------------------------------------------------------
     */

    public function getPaymentPlanCountAttribute(): int
    {
        $plan = $this->payment_plan;

        return is_array($plan) ? count($plan) : 0;
    }

    /**
     * Sum of the individual fee components (excludes total_fees,
     * which isn't a stored column on this model — see getTotalFeesAttribute).
     */
    public function getComponentsTotalAttribute(): float
    {
        return (float) (
            $this->tuition_fees
            + $this->activity_fees
            + $this->library_fees
            + $this->sports_fees
            + $this->medical_fees
            + $this->transport_fees
            + $this->boarding_fees
            + $this->uniform_fees
            + $this->other_fees
        );
    }

    /**
     * Total of the fee components. Aliased as `total_fees` so table
     * columns and templates can use `$record->total_fees`.
     */
    public function getTotalFeesAttribute(): float
    {
        return $this->components_total;
    }

    /**
     * Total of the payment plan installments.
     */
    public function getInstallmentsTotalAttribute(): float
    {
        return collect($this->payment_plan ?? [])
            ->sum(fn ($row) => (float) ($row['amount'] ?? 0));
    }

    /**
     * True when the installments sum to the component total.
     */
    public function getInstallmentsBalancedAttribute(): bool
    {
        return abs($this->installments_total - $this->total_fees) < 0.01;
    }

    /* -----------------------------------------------------------------
     |  Static helpers
     | -----------------------------------------------------------------
     */

    /**
     * Build the canonical 3-term payment plan.
     *
     * - Term 2 and Term 3 are rounded to the nearest 1,000 and Term 1
     *   absorbs the remainder, so the sum always equals the total.
     * - Very small totals (< 3,000) fall back to whole-shilling
     *   rounding so Term 2 and Term 3 don't both collapse to 0.
     * - Due dates: Term 1 → 15 March, Term 2 → 15 July, Term 3 → 15 November.
     *
     * On a 48,000 total this produces 18,000 / 16,000 / 14,000.
     */
    public static function buildDefaultPaymentPlan(float $total, ?int $year = null): array
    {
        $year ??= (int) now()->year;

        if ($total < 3000) {
            $term3 = round($total * 0.29, 2);
            $term2 = round($total * 0.33, 2);
        } else {
            $term3 = round($total * 0.29, -3);
            $term2 = round($total * 0.33, -3);
        }

        $term1 = $total - $term2 - $term3;

        return [
            [
                'term' => 'term_1',
                'amount' => $term1,
                'due_date' => sprintf('%04d-03-15', $year),
            ],
            [
                'term' => 'term_2',
                'amount' => $term2,
                'due_date' => sprintf('%04d-07-15', $year),
            ],
            [
                'term' => 'term_3',
                'amount' => $term3,
                'due_date' => sprintf('%04d-11-15', $year),
            ],
        ];
    }

    /**
     * Extract a 4-digit year from an academic-year name.
     * Handles "2026", "2025/2026", "2025-2026", "2026 Academic Year".
     */
    public static function yearFromAcademicYearName(?string $name): int
    {
        if ($name && preg_match('/(\d{4})/', $name, $m)) {
            return (int) $m[1];
        }

        return (int) now()->year;
    }
}