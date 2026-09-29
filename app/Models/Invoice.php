<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Invoice extends Model
{
    protected $table = 'invoices';

    protected $fillable = [
        'invoice_number',
        'student_id',
        'fee_structure_id',
        'term',
        'amount',
        'amount_paid',
        'due_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'due_date'    => 'date',
        'status'      => InvoiceStatus::class,
    ];

    /* -----------------------------------------------------------------
     |  Boot
     | -----------------------------------------------------------------
     */

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = static::generateInvoiceNumber();
            }
        });

        // NOTE: No `saving` hook here. `balance` is a generated column
        // (storedAs('amount - amount_paid')) and cannot be written to.
        // The accessor below handles the written-off override.
    }

    /* -----------------------------------------------------------------
     |  Invoice number generation
     | -----------------------------------------------------------------
     */

    public static function generateInvoiceNumber(): string
    {
        $year   = date('Y');
        $prefix = "INV/{$year}/";

        $last = static::where('invoice_number', 'like', $prefix . '%')
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $next = 1;
        if ($last) {
            $serialPart = substr($last, strlen($prefix));
            $next = ((int) $serialPart) + 1;
        }

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    /* -----------------------------------------------------------------
     |  Relationships
     | -----------------------------------------------------------------
     */

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function feeStructure(): BelongsTo
    {
        return $this->belongsTo(FeeStructure::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /* -----------------------------------------------------------------
     |  Business rules
     | -----------------------------------------------------------------
     */

    /**
     * Whether this invoice can be deleted.
     * Blocked when:
     *  - the invoice has any payments, OR
     *  - the invoice is already marked paid
     */
    public function canBeDeleted(): bool
    {
        if ($this->payments()->exists()) {
            return false;
        }

        return $this->status !== InvoiceStatus::Paid;
    }

    /**
     * Recalculate amount_paid and status from completed payments.
     *
     * The invoice row is locked so two concurrent payment recordings
     * cannot interleave and produce a lost update.
     */
    public function updateAfterPayment(): self
    {
        return DB::transaction(function () {
            // Lock the invoice row before reading, so concurrent calls
            // serialise on this single row.
            self::query()
                ->whereKey($this->getKey())
                ->lockForUpdate()
                ->first();

            $this->refresh();

            $totalPaid = (float) $this->payments()
                ->where('status', 'completed')
                ->sum('amount');

            $this->amount_paid = $totalPaid;
            $this->status      = $this->resolveStatus($totalPaid);
            $this->save();

            return $this;
        });
    }

    protected function resolveStatus(float $totalPaid): InvoiceStatus
    {
        // Written-off invoices keep their status — don't overwrite waived/cancelled.
        if ($this->status instanceof InvoiceStatus
            && in_array($this->status->value, InvoiceStatus::writtenOff(), true)) {
            return $this->status;
        }

        if ($totalPaid >= (float) $this->amount) {
            return InvoiceStatus::Paid;
        }

        if ($totalPaid > 0) {
            return InvoiceStatus::PartiallyPaid;
        }

        if ($this->due_date && $this->due_date->endOfDay()->isPast()) {
            return InvoiceStatus::Overdue;
        }

        return InvoiceStatus::Pending;
    }

    /* -----------------------------------------------------------------
     |  Accessors
     | -----------------------------------------------------------------
     */

    /**
     * Balance accessor.
     *
     * Written-off invoices (waived, cancelled) always show a zero
     * balance, regardless of what the generated DB column contains.
     * For active invoices, trust the stored generated column.
     */
    public function getBalanceAttribute(): float
    {
        if ($this->status instanceof InvoiceStatus
            && in_array($this->status->value, InvoiceStatus::writtenOff(), true)) {
            return 0.0;
        }

        return (float) (
            $this->attributes['balance']
            ?? ((float) $this->amount - (float) $this->amount_paid)
        );
    }

    public function getBalanceFormattedAttribute(): string
    {
        return 'KES ' . number_format($this->getBalanceAttribute(), 2);
    }

    public function getAmountFormattedAttribute(): string
    {
        return 'KES ' . number_format((float) $this->amount, 2);
    }

    /* -----------------------------------------------------------------
     |  Scopes
     | -----------------------------------------------------------------
     */

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereDate('due_date', '<', today())
                     ->whereNotIn('status', [
                         InvoiceStatus::Paid->value,
                         InvoiceStatus::Waived->value,
                         InvoiceStatus::Cancelled->value,
                     ]);
    }

    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->whereColumn('amount', '>', 'amount_paid')
                     ->whereNotIn('status', InvoiceStatus::writtenOff());
    }

    public function scopeWrittenOff(Builder $query): Builder
    {
        return $query->whereIn('status', InvoiceStatus::writtenOff());
    }

    public function scopeForYear(Builder $query, int $year): Builder
    {
        return $query->whereBetween('created_at', [
            "{$year}-01-01 00:00:00",
            "{$year}-12-31 23:59:59",
        ]);
    }

    public function scopeForTerm(Builder $query, string $term): Builder
    {
        return $query->where('term', $term);
    }

    public function scopeForStudent(Builder $query, int $studentId): Builder
    {
        return $query->where('student_id', $studentId);
    }
}