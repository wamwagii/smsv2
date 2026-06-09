<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table = 'invoices';
    
    protected $fillable = [
        'invoice_number',
        'student_id',
        'fees_structure_id',
        'term',
        'amount',
        'amount_paid',
        'balance',
        'due_date',
        'status',
        'notes',
    ];
    
    protected $casts = [
        'amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
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
        
        // Get the last invoice number for this year
        $lastInvoice = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
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
        
        return "INV/{$year}/{$newNumber}";
    }
    
    /**
     * Alternative: Generate invoice number with month (more detailed)
     * Format: INV/YYYY/MM-XXXX (e.g., INV/2024/12-0001)
     */
    public static function generateDetailedInvoiceNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        
        // Get the last invoice number for this year and month
        $lastInvoice = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastInvoice && $lastInvoice->invoice_number) {
            // Extract the sequential number from the last invoice
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
        
        return "INV/{$year}/{$month}-{$newNumber}";
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
    public function feesStructure()
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
     * Update balance when payment is made
     */
    public function updateBalance()
    {
        $totalPaid = $this->payments()->where('status', 'completed')->sum('amount');
        $this->amount_paid = $totalPaid;
        $this->balance = $this->amount - $totalPaid;
        
        if ($this->balance <= 0) {
            $this->status = 'paid';
        } elseif ($this->amount_paid > 0) {
            $this->status = 'partially_paid';
        } elseif ($this->due_date && $this->due_date->isPast()) {
            $this->status = 'overdue';
        } else {
            $this->status = 'pending';
        }
        
        $this->saveQuietly(); // Use saveQuietly to avoid infinite loops
        return $this;
    }
    
    /**
     * Get the formatted invoice number with prefix (accessor)
     */
    public function getFormattedInvoiceNumberAttribute(): string
    {
        return $this->invoice_number;
    }
    
    /**
     * Get the balance in KES format
     */
    public function getBalanceFormattedAttribute(): string
    {
        return 'KES ' . number_format($this->balance, 2);
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