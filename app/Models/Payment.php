<?php

namespace App\Models;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class Payment extends Model
{


    use SoftDeletes;
    protected $table = 'payments';

    protected $fillable = [
        'idempotency_key',
        'invoice_id',
        'student_id',
        'parent_id',
        'amount',
        'payment_method',
        'status',
        'mpesa_receipt',
        'checkout_request_id',
        'merchant_request_id',
        'transaction_reference',
        'bank_name',
        'card_last_four',
        'payment_date',
        'payment_time',
        'notes',
        'gateway_response',
        'receipt_number',
        'receipt_path',
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        'payment_date'     => 'date',
        'payment_time'     => 'datetime',
        'gateway_response' => 'array',
    ];

    /* -----------------------------------------------------------------
     |  Boot
     | -----------------------------------------------------------------
     |
     | The `saved`, `deleted`, and `restored` hooks all call
     | `$payment->invoice?->updateAfterPayment()` so the parent invoice
     | stays consistent through every step of the payment lifecycle:
     |
     |   - created  → invoice.amount_paid increases
     |   - updated  → invoice.amount_paid re-syncs
     |   - soft-deleted → invoice.amount_paid recomputes (excludes this row)
     |   - restored → invoice.amount_paid recomputes (includes this row again)
     |
     | Callers should NOT call updateAfterPayment() themselves — that
     | would duplicate the work and take an extra row lock.
     */

    protected static function booted(): void
    {
        // Auto-generate idempotency key + receipt number on create.
        static::creating(function (Payment $payment) {
            if (empty($payment->idempotency_key)) {
                $payment->idempotency_key = (string) Str::uuid();
            }

            if (empty($payment->receipt_number)) {
                $payment->receipt_number = static::generateReceiptNumber();
            }
        });

        // Sync the parent invoice after any save.
        static::saved(function (Payment $payment) {
            $payment->invoice?->updateAfterPayment();
        });

        // Recompute when a payment is soft-deleted.
        static::deleted(function (Payment $payment) {
            $payment->invoice?->updateAfterPayment();
        });

        // Recompute when a soft-deleted payment is restored.
        static::restored(function (Payment $payment) {
            $payment->invoice?->updateAfterPayment();
        });

        // Send the receipt email when a payment transitions to "completed".
        static::updated(function (Payment $payment) {
            if ($payment->wasChanged('status') && $payment->status === 'completed') {
                $payment->sendPaymentNotification();
            }
        });
    }

    /* -----------------------------------------------------------------
     |  Receipt number generation
     | -----------------------------------------------------------------
     */

    /**
     * Generate a unique receipt number with a transaction lock
     * to prevent duplicates under concurrency.
     *
     * Format: RCT/YYYY/XXXXX (e.g., RCT/2024/00001)
     */
    public static function generateReceiptNumber(): string
    {
        $year = date('Y');

        return DB::transaction(function () use ($year) {
            $lastPayment = self::where('receipt_number', 'like', "RCT/{$year}/%")
                ->lockForUpdate()
                ->orderByDesc('receipt_number')
                ->first();

            $newNumber = '00001';

            if ($lastPayment && $lastPayment->receipt_number) {
                preg_match('/RCT\/' . $year . '\/(\d+)/', $lastPayment->receipt_number, $matches);
                if (isset($matches[1])) {
                    $lastNumber = (int) $matches[1];
                    $newNumber = str_pad((string) ($lastNumber + 1), 5, '0', STR_PAD_LEFT);
                }
            }

            $receiptNumber = "RCT/{$year}/{$newNumber}";

            while (self::where('receipt_number', $receiptNumber)->exists()) {
                $newNumber = str_pad((string) ((int) $newNumber + 1), 5, '0', STR_PAD_LEFT);
                $receiptNumber = "RCT/{$year}/{$newNumber}";
            }

            return $receiptNumber;
        });
    }

    /* -----------------------------------------------------------------
     |  Business rules
     | -----------------------------------------------------------------
     */

    /**
     * Whether this payment can be deleted.
     * Only non-completed payments can be removed.
     */
    public function canBeDeleted(): bool
    {
        return $this->status !== 'completed';
    }

    /* -----------------------------------------------------------------
     |  Notifications
     | -----------------------------------------------------------------
     */

    /**
     * Send payment notification with PDF receipt to guardian.
     */
    public function sendPaymentNotification(): void
    {
        $guardian = $this->parent;
        $student  = $this->student;

        if (! $guardian || ! $guardian->email) {
            return;
        }

        try {
            $pdfPath = $this->generateReceiptPdf();

            Mail::to($guardian->email)
                ->send(new \App\Mail\PaymentReceiptMail($this, $pdfPath));

            $this->receipt_path = $pdfPath;
            $this->saveQuietly();
        } catch (\Throwable $e) {
            Log::error('Failed to send payment notification: ' . $e->getMessage());
        }
    }

    /**
     * Generate PDF receipt and store it in public/receipts.
     * Returns the relative path (for use in Storage::url()).
     */
    public function generateReceiptPdf(): string
    {
        $directory = storage_path('app/public/receipts');

        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $filename = 'receipt_' . $this->receipt_number . '_' . date('Ymd_His') . '.pdf';
        $path     = 'receipts/' . $filename;
        $fullPath = storage_path('app/public/' . $path);

        $pdf = Pdf::loadView('pdf.payment_receipt', [
            'payment'  => $this,
            'student'  => $this->student,
            'guardian' => $this->parent,
            'invoice'  => $this->invoice,
        ]);

        $pdf->save($fullPath);

        return $path;
    }

    /* -----------------------------------------------------------------
     |  Accessors
     | -----------------------------------------------------------------
     */

    public function getFormattedAmountAttribute(): string
    {
        return 'KES ' . number_format((float) $this->amount, 2);
    }

    public function getPaymentMethodDisplayAttribute(): string
    {
        return match ($this->payment_method) {
            'mpesa'         => 'M-Pesa',
            'bank_transfer' => 'Bank Transfer',
            'cash'          => 'Cash',
            'cheque'        => 'Cheque',
            'card'          => 'Card',
            default         => ucfirst((string) $this->payment_method),
        };
    }

    /**
     * Return ['text' => 'Completed', 'color' => 'success'] style tuple.
     *
     * @return array{text: string, color: string}
     */
    public function getStatusDisplayAttribute(): array
    {
        return match ($this->status) {
            'completed'  => ['text' => 'Completed',  'color' => 'success'],
            'pending'    => ['text' => 'Pending',    'color' => 'warning'],
            'processing' => ['text' => 'Processing', 'color' => 'info'],
            'failed'     => ['text' => 'Failed',     'color' => 'danger'],
            'refunded'   => ['text' => 'Refunded',   'color' => 'secondary'],
            default      => ['text' => ucfirst((string) $this->status), 'color' => 'secondary'],
        };
    }

    /* -----------------------------------------------------------------
     |  Relationships
     | -----------------------------------------------------------------
     */

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Guardian::class, 'parent_id');
    }

    /* -----------------------------------------------------------------
     |  Scopes
     | -----------------------------------------------------------------
     */

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeForStudent(Builder $query, int $studentId): Builder
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForDateRange(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    public function scopeMpesa(Builder $query): Builder
    {
        return $query->where('payment_method', 'mpesa');
    }

    public function scopeCash(Builder $query): Builder
    {
        return $query->where('payment_method', 'cash');
    }
}