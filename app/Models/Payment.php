<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

class Payment extends Model
{
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
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'payment_time' => 'datetime',
        'gateway_response' => 'array',
    ];
    
    protected static function boot()
    {
        parent::boot();
        
        // Auto-generate idempotency key when creating
        static::creating(function ($payment) {
            if (empty($payment->idempotency_key)) {
                $payment->idempotency_key = (string) Str::uuid();
            }
            
            // Auto-generate receipt number when creating
            if (empty($payment->receipt_number)) {
                $payment->receipt_number = static::generateReceiptNumber();
            }
        });
        
        // After payment is created or updated, update the invoice
        static::saved(function ($payment) {
            if ($payment->invoice) {
                $payment->invoice->updateAfterPayment();
            }
        });
        
        // Send notification when payment status changes to completed
        static::updated(function ($payment) {
            if ($payment->wasChanged('status') && $payment->status === 'completed') {
                $payment->sendPaymentNotification();
            }
        });
    }
    
    /**
     * Generate a unique receipt number with transaction lock to prevent duplicates
     * Format: RCT/YYYY/XXXXX (e.g., RCT/2024/00001)
     */
    public static function generateReceiptNumber(): string
    {
        $year = date('Y');
        
        return DB::transaction(function () use ($year) {
            // Get the last receipt number for this year with lock
            $lastPayment = self::where('receipt_number', 'like', "RCT/{$year}/%")
                ->lockForUpdate()
                ->orderBy('receipt_number', 'desc')
                ->first();
            
            if ($lastPayment && $lastPayment->receipt_number) {
                // Extract the sequential number from the last receipt
                preg_match('/RCT\/' . $year . '\/(\d+)/', $lastPayment->receipt_number, $matches);
                if (isset($matches[1])) {
                    $lastNumber = (int)$matches[1];
                    $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
                } else {
                    $newNumber = '00001';
                }
            } else {
                $newNumber = '00001';
            }
            
            $receiptNumber = "RCT/{$year}/{$newNumber}";
            
            // Double-check uniqueness
            while (self::where('receipt_number', $receiptNumber)->exists()) {
                $newNumber = str_pad((int)$newNumber + 1, 5, '0', STR_PAD_LEFT);
                $receiptNumber = "RCT/{$year}/{$newNumber}";
            }
            
            return $receiptNumber;
        });
    }
    
    /**
     * Send payment notification with PDF receipt to guardian
     */
    public function sendPaymentNotification()
    {
        $guardian = $this->parent;
        $student = $this->student;
        
        if ($guardian && $guardian->email) {
            try {
                // Generate PDF receipt
                $pdfPath = $this->generateReceiptPdf();
                
                // Send email notification
                Mail::to($guardian->email)->send(new \App\Mail\PaymentReceiptMail($this, $pdfPath));
                
                // Update receipt path
                $this->receipt_path = $pdfPath;
                $this->saveQuietly();
            } catch (\Exception $e) {
                // Log error but don't fail the payment
                \Log::error('Failed to send payment notification: ' . $e->getMessage());
            }
        }
    }
    
    /**
     * Generate PDF receipt
     */
    public function generateReceiptPdf()
    {
        // Create receipts directory if it doesn't exist
        $directory = storage_path('app/public/receipts');
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }
        
        $filename = 'receipt_' . $this->receipt_number . '_' . date('Ymd_His') . '.pdf';
        $path = 'receipts/' . $filename;
        $fullPath = storage_path('app/public/' . $path);
        
        // Generate PDF
        $pdf = Pdf::loadView('pdf.payment_receipt', [
            'payment' => $this,
            'student' => $this->student,
            'guardian' => $this->parent,
            'invoice' => $this->invoice,
        ]);
        
        $pdf->save($fullPath);
        
        return $path;
    }
    
    /**
     * Get the formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'KES ' . number_format($this->amount, 2);
    }
    
    /**
     * Get the payment method display name
     */
    public function getPaymentMethodDisplayAttribute(): string
    {
        return match($this->payment_method) {
            'mpesa' => 'M-Pesa',
            'bank_transfer' => 'Bank Transfer',
            'cash' => 'Cash',
            'cheque' => 'Cheque',
            'card' => 'Card',
            default => ucfirst($this->payment_method),
        };
    }
    
    /**
     * Get the status display name with badge color
     */
    public function getStatusDisplayAttribute(): array
    {
        return match($this->status) {
            'completed' => ['text' => 'Completed', 'color' => 'success'],
            'pending' => ['text' => 'Pending', 'color' => 'warning'],
            'processing' => ['text' => 'Processing', 'color' => 'info'],
            'failed' => ['text' => 'Failed', 'color' => 'danger'],
            'refunded' => ['text' => 'Refunded', 'color' => 'secondary'],
            default => ['text' => ucfirst($this->status), 'color' => 'secondary'],
        };
    }
    
    // Relationships
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
    
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
    
    public function parent()
    {
        return $this->belongsTo(Guardian::class, 'parent_id');
    }
    
    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
    
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }
    
    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }
    
    public function scopeMpesa($query)
    {
        return $query->where('payment_method', 'mpesa');
    }
    
    public function scopeCash($query)
    {
        return $query->where('payment_method', 'cash');
    }
}