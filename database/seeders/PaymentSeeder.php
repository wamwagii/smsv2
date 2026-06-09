<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\Guardian;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        // Get all invoices that are not fully paid
        $invoices = Invoice::where('status', '!=', 'paid')->get();
        
        if ($invoices->isEmpty()) {
            $this->command->warn('No invoices found. Please run InvoiceSeeder first.');
            return;
        }
        
        $paymentMethods = ['mpesa', 'bank_transfer', 'cash', 'cheque', 'card'];
        $bankNames = ['Equity Bank', 'KCB Bank', 'Co-operative Bank', 'Absa Bank', 'Stanbic Bank', 'NCBA Bank'];
        
        $payments = [];
        $receiptCounter = 1;
        
        foreach ($invoices as $invoice) {
            // Determine number of payments per invoice (1-2 payments)
            $numPayments = rand(1, 2);
            $remainingAmount = $invoice->amount - $invoice->amount_paid;
            $paidAmount = $invoice->amount_paid;
            
            for ($i = 1; $i <= $numPayments && $remainingAmount > 0; $i++) {
                $paymentMethod = $paymentMethods[array_rand($paymentMethods)];
                
                // Determine payment amount
                if ($i === $numPayments || $remainingAmount < 5000) {
                    $amount = $remainingAmount;
                } else {
                    $amount = round($remainingAmount * (rand(30, 70) / 100), 2);
                }
                
                $paymentDate = $this->randomDateBetween(
                    $invoice->created_at ?? now()->subMonths(3),
                    now()
                );
                
                $status = $amount >= $remainingAmount ? 'completed' : 'completed';
                
                // Initialize all possible fields with null
                $payment = [
                    'idempotency_key' => (string) Str::uuid(),
                    'invoice_id' => $invoice->id,
                    'student_id' => $invoice->student_id,
                    'parent_id' => $this->getRandomParentId($invoice->student_id),
                    'amount' => $amount,
                    'payment_method' => $paymentMethod,
                    'status' => $status,
                    'payment_date' => $paymentDate,
                    'payment_time' => $this->randomTime(),
                    'receipt_number' => 'RCT/' . date('Y') . '/' . str_pad($receiptCounter, 5, '0', STR_PAD_LEFT),
                    'mpesa_receipt' => null,
                    'checkout_request_id' => null,
                    'merchant_request_id' => null,
                    'transaction_reference' => null,
                    'bank_name' => null,
                    'card_last_four' => null,
                    'notes' => null,
                    'gateway_response' => null,
                    'created_at' => $paymentDate,
                    'updated_at' => $paymentDate,
                ];
                
                // Add method-specific fields
                if ($paymentMethod === 'mpesa') {
                    $payment['mpesa_receipt'] = $this->generateMpesaReceipt();
                    $payment['checkout_request_id'] = 'CO_' . Str::random(20);
                    $payment['merchant_request_id'] = 'MR_' . Str::random(20);
                    $payment['gateway_response'] = json_encode([
                        'ResultCode' => 0,
                        'ResultDesc' => 'Success',
                        'TransactionDate' => $paymentDate->format('YmdHis'),
                        'ReceiptNumber' => $payment['mpesa_receipt'],
                    ]);
                } elseif (in_array($paymentMethod, ['bank_transfer', 'card'])) {
                    $payment['transaction_reference'] = 'TRX_' . strtoupper(Str::random(15));
                    $payment['bank_name'] = $bankNames[array_rand($bankNames)];
                    if ($paymentMethod === 'card') {
                        $payment['card_last_four'] = (string) rand(1000, 9999);
                    }
                    $payment['gateway_response'] = json_encode([
                        'ResultCode' => 0,
                        'ResultDesc' => 'Success',
                        'TransactionReference' => $payment['transaction_reference'],
                    ]);
                } elseif ($paymentMethod === 'cheque') {
                    $payment['transaction_reference'] = 'CHQ_' . strtoupper(Str::random(10));
                    $payment['notes'] = 'Cheque payment received';
                } elseif ($paymentMethod === 'cash') {
                    $payment['notes'] = 'Cash payment received';
                }
                
                // Add notes for partial payments
                if ($amount < $remainingAmount && $i < $numPayments) {
                    $payment['notes'] = ($payment['notes'] ? $payment['notes'] . '. ' : '') . 
                        'Partial payment. Remaining: KES ' . number_format($remainingAmount - $amount, 2);
                }
                
                $payments[] = $payment;
                
                $remainingAmount -= $amount;
                $paidAmount += $amount;
                $receiptCounter++;
            }
            
            // Update invoice after payments
            $this->updateInvoiceAfterPayments($invoice, $paidAmount);
        }
        
        // Insert payments in chunks
        if (!empty($payments)) {
            foreach (array_chunk($payments, 50) as $chunk) {
                Payment::insert($chunk);
            }
            $this->command->info(count($payments) . ' payments created successfully.');
            $this->command->info('Invoices have been updated with payment statuses.');
        } else {
            $this->command->warn('No payments were created.');
        }
    }
    
    private function getRandomParentId($studentId)
    {
        $student = Student::with('parents')->find($studentId);
        if ($student && $student->parents && $student->parents->count() > 0) {
            return $student->parents->random()->id;
        }
        return Guardian::inRandomOrder()->first()?->id;
    }
    
    private function randomDateBetween($startDate, $endDate)
    {
        $timestamp = rand($startDate->timestamp, $endDate->timestamp);
        return \Carbon\Carbon::createFromTimestamp($timestamp);
    }
    
    private function randomTime()
    {
        return \Carbon\Carbon::createFromTime(rand(8, 17), rand(0, 59), rand(0, 59));
    }
    
    private function generateMpesaReceipt()
    {
        $prefixes = ['QWE', 'RTY', 'UIO', 'PAS', 'DFG', 'HJK', 'LZX', 'CVB', 'NMB', 'WER'];
        $prefix = $prefixes[array_rand($prefixes)];
        return $prefix . $prefix . rand(100, 999) . 'T' . rand(1, 9);
    }
    
    private function updateInvoiceAfterPayments($invoice, $totalPaid)
    {
        $balance = $invoice->amount - $totalPaid;
        
        $status = match(true) {
            $balance <= 0 => 'paid',
            $totalPaid > 0 => 'partially_paid',
            default => 'pending',
        };
        
        $invoice->update([
            'amount_paid' => $totalPaid,
            'status' => $status,
        ]);
    }
}