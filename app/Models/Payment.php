<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
        
        // Send notification when payment status changes to completed
        static::updated(function ($payment) {
            if ($payment->wasChanged('status') && $payment->status === 'completed') {
                $payment->sendPaymentNotification();
            }
        });
    }
    
    /**
     * Generate a unique receipt number
     * Format: RCT/YYYY/XXXXX (e.g., RCT/2024/00001)
     */
    public static function generateReceiptNumber(): string
    {
        $year = date('Y');
        
        // Get the last receipt number for this year
        $lastPayment = self::whereYear('payment_date', $year)
            ->whereNotNull('receipt_number')
            ->orderBy('id', 'desc')
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
        
        return "RCT/{$year}/{$newNumber}";
    }
    
    /**
     * Send payment notification with PDF receipt to guardian
     */
    public function sendPaymentNotification()
    {
        $guardian = $this->parent;
        $student = $this->student;
        
        if ($guardian && $guardian->email) {
            // Generate PDF receipt
            $pdfPath = $this->generateReceiptPdf();
            
            // Send email notification
            \Mail::to($guardian->email)->send(new \App\Mail\PaymentReceiptMail($this, $pdfPath));
            
            // Update receipt path
            $this->receipt_path = $pdfPath;
            $this->saveQuietly();
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
        
        // Generate PDF using Laravel's PDF package
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.payment_receipt', [
            'payment' => $this,
            'student' => $this->student,
            'guardian' => $this->parent,
            'invoice' => $this->invoice,
        ]);
        
        $pdf->save($fullPath);
        
        return $path;
    }
    
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
    
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
    
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}