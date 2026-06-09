<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'due_date' => 'date',
    ];
    
    /**
     * Boot the model to add auto-generation of invoice number
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = self::generateInvoiceNumber();
            }
        });
    }
    
    /**
     * Generate a unique invoice number
     * Format: INV/YYYY/XXXX (e.g., INV/2024/0001)
     * Resets counter each year
     */
    public static function generateInvoiceNumber(): string
    {
        $year = date('Y');
        
        // Use a lock to prevent race conditions
        return \Illuminate\Support\Facades\DB::transaction(function () use ($year) {
            // Get the last invoice number for this year with lock
            $lastInvoice = self::where('invoice_number', 'like', "INV/{$year}/%")
                ->lockForUpdate()
                ->orderBy('invoice_number', 'desc')
                ->first();
            
            if ($lastInvoice && $lastInvoice->invoice_number) {
                // Extract the sequential number from the last invoice
                preg_match('/INV\/' . $year . '\/(\d+)/', $lastInvoice->invoice_number, $matches);
                if (isset($matches[1])) {
                    $lastNumber = (int)$matches[1];
                    $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
                } else {
                    $newNumber = '0001';
                }
            } else {
                $newNumber = '0001';
            }
            
            $invoiceNumber = "INV/{$year}/{$newNumber}";
            
            // Double-check uniqueness (shouldn't be needed with lock, but safe)
            while (self::where('invoice_number', $invoiceNumber)->exists()) {
                $newNumber = str_pad((int)$newNumber + 1, 4, '0', STR_PAD_LEFT);
                $invoiceNumber = "INV/{$year}/{$newNumber}";
            }
            
            return $invoiceNumber;
        });
    }
    
    /**
     * Alternative: Generate invoice number with month (more detailed)
     * Format: INV/YYYY/MM-XXXX (e.g., INV/2024/12-0001)
     */
    public static function generateDetailedInvoiceNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        
        return \Illuminate\Support\Facades\DB::transaction(function () use ($year, $month) {
            $lastInvoice = self::where('invoice_number', 'like', "INV/{$year}/{$month}-%")
                ->lockForUpdate()
                ->orderBy('invoice_number', 'desc')
                ->first();
            
            if ($lastInvoice && $lastInvoice->invoice_number) {
                preg_match('/INV\/' . $year . '\/' . $month . '-(\d+)/', $lastInvoice->invoice_number, $matches);
                if (isset($matches[1])) {
                    $lastNumber = (int)$matches[1];
                    $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
                } else {
                    $newNumber = '0001';
                }
            } else {
                $newNumber = '0001';
            }
            
            $invoiceNumber = "INV/{$year}/{$month}-{$newNumber}";
            
            while (self::where('invoice_number', $invoiceNumber)->exists()) {
                $newNumber = str_pad((int)$newNumber + 1, 4, '0', STR_PAD_LEFT);
                $invoiceNumber = "INV/{$year}/{$month}-{$newNumber}";
            }
            
            return $invoiceNumber;
        });
    }
    
    /**
     * Get the student associated with the invoice
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
    
    /**
     * Get the fee structure associated with the invoice
     */
    public function feeStructure()
    {
        return $this->belongsTo(FeeStructure::class);
    }
    
    /**
     * Get the payments for this invoice
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
    
    /**
     * Update invoice when payment is made (balance is auto-calculated by DB)
     */
    public function updateAfterPayment()
    {
        $totalPaid = $this->payments()->where('status', 'completed')->sum('amount');
        
        $this->amount_paid = $totalPaid;
        
        // Determine new status based on amount_paid vs amount
        if ($totalPaid >= $this->amount) {
            $this->status = 'paid';
        } elseif ($totalPaid > 0) {
            $this->status = 'partially_paid';
        } elseif ($this->due_date && $this->due_date->isPast()) {
            $this->status = 'overdue';
        } else {
            $this->status = 'pending';
        }
        
        // Note: balance is NOT updated here - it's a generated column in the database
        // The database automatically calculates balance = amount - amount_paid
        
        $this->saveQuietly();
        return $this;
    }
    
    /**
     * Get the formatted invoice number (accessor)
     */
    public function getFormattedInvoiceNumberAttribute(): string
    {
        return $this->invoice_number;
    }
    
    /**
     * Get the balance (accessor that calculates from database values)
     */
    public function getBalanceAttribute(): float
    {
        return $this->amount - $this->amount_paid;
    }
    
    /**
     * Get the balance in KES format
     */
    public function getBalanceFormattedAttribute(): string
    {
        return 'KES ' . number_format($this->getBalanceAttribute(), 2);
    }
    
    /**
     * Get the amount in KES format
     */
    public function getAmountFormattedAttribute(): string
    {
        return 'KES ' . number_format($this->amount, 2);
    }
    
    /**
     * Scope a query to only include overdue invoices
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                     ->where('status', '!=', 'paid');
    }
    
    /**
     * Scope a query to only include unpaid invoices
     */
    public function scopeUnpaid($query)
    {
        return $query->whereColumn('amount', '>', 'amount_paid');
    }
    
    /**
     * Scope a query to only include invoices by year
     */
    public function scopeForYear($query, $year)
    {
        return $query->whereYear('created_at', $year);
    }
    
    /**
     * Scope a query to only include invoices by term
     */
    public function scopeForTerm($query, $term)
    {
        return $query->where('term', $term);
    }
    
    /**
     * Scope a query to only include invoices by student
     */
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }
}