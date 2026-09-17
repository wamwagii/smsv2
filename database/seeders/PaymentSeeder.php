<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\Guardian;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        // Seed payments for ALL invoices — including paid ones.
        $invoices = Invoice::all();

        if ($invoices->isEmpty()) {
            $this->command->warn('No invoices found. Please run InvoiceSeeder first.');
            return;
        }

        // Clear existing payments for idempotency
        Payment::query()->delete();

        $paymentMethods = ['mpesa', 'bank_transfer', 'cash', 'cheque', 'card'];
        $bankNames = [
            'Equity Bank', 'KCB Bank', 'Co-operative Bank',
            'Absa Bank', 'Stanbic Bank', 'NCBA Bank',
        ];

        $payments = [];
        $receiptCounter = 1;

        foreach ($invoices as $invoice) {
            // Determine target paid amount based on invoice status
            $targetPaid = match ($invoice->status) {
                'paid'           => (float) $invoice->amount,
                'partially_paid' => round((float) $invoice->amount * (rand(30, 70) / 100), 2),
                'waived'         => 0.0,
                default          => 0.0,
            };

            if ($targetPaid <= 0) {
                $this->syncInvoice($invoice, 0.0);
                continue;
            }

            $numPayments = rand(1, 3);
            $remaining = $targetPaid;
            $totalPaid = 0.0;

            for ($i = 1; $i <= $numPayments && $remaining > 0.01; $i++) {
                $paymentMethod = $paymentMethods[array_rand($paymentMethods)];

                if ($i === $numPayments || $remaining < 5000) {
                    $amount = round($remaining, 2);
                } else {
                    $amount = round($remaining * (rand(30, 70) / 100), 2);
                }

                $paymentDate = $this->randomDateBetween(
                    $invoice->created_at ?? now()->subMonths(3),
                    now()
                );

                $payment = [
                    'idempotency_key'       => (string) Str::uuid(),
                    'invoice_id'            => $invoice->id,
                    'student_id'            => $invoice->student_id,
                    'parent_id'             => $this->getRandomParentId($invoice->student_id),
                    'amount'                => $amount,
                    'payment_method'        => $paymentMethod,
                    'status'                => 'completed',
                    'payment_date'          => $paymentDate,
                    'payment_time'          => $this->randomTime(),
                    'receipt_number'        => 'RCT/' . date('Y') . '/' . str_pad((string) $receiptCounter, 5, '0', STR_PAD_LEFT),
                    'mpesa_receipt'         => null,
                    'checkout_request_id'   => null,
                    'merchant_request_id'   => null,
                    'transaction_reference' => null,
                    'bank_name'             => null,
                    'card_last_four'        => null,
                    'notes'                 => null,
                    'gateway_response'      => null,
                    'created_at'            => $paymentDate,
                    'updated_at'            => $paymentDate,
                ];

                match ($paymentMethod) {
                    'mpesa'                 => $this->fillMpesa($payment, $paymentDate),
                    'bank_transfer', 'card' => $this->fillBank($payment, $bankNames, $paymentMethod),
                    'cheque'                => $this->fillCheque($payment),
                    'cash'                  => $this->fillCash($payment),
                    default                 => null,
                };

                $payments[] = $payment;

                $remaining -= $amount;
                $totalPaid += $amount;
                $receiptCounter++;
            }

            $this->syncInvoice($invoice, $totalPaid);
        }

        if (!empty($payments)) {
    \Illuminate\Database\Eloquent\Model::withoutEvents(function () use ($payments) {
        foreach ($payments as $payment) {
            Payment::create($payment);
        }
    });
    $this->command->info(count($payments) . ' payments created successfully.');
} else {
    $this->command->warn('No payments were created.');
}
    }

    /* -----------------------------------------------------------------
     |  Helpers
     | -----------------------------------------------------------------
     */

    private function fillMpesa(array &$payment, Carbon $paymentDate): void
    {
        $payment['mpesa_receipt']       = $this->generateMpesaReceipt();
        $payment['checkout_request_id'] = 'CO_' . Str::random(20);
        $payment['merchant_request_id'] = 'MR_' . Str::random(20);

        // Plain array — Eloquent's 'array' cast will encode it on save
        $payment['gateway_response'] = [
            'ResultCode'      => 0,
            'ResultDesc'      => 'Success',
            'TransactionDate' => $paymentDate->format('YmdHis'),
            'ReceiptNumber'   => $payment['mpesa_receipt'],
        ];
    }

    private function fillBank(array &$payment, array $bankNames, string $method): void
    {
        $payment['transaction_reference'] = 'TRX_' . strtoupper(Str::random(15));
        $payment['bank_name']             = $bankNames[array_rand($bankNames)];

        if ($method === 'card') {
            $payment['card_last_four'] = (string) rand(1000, 9999);
        }

        // Plain array — Eloquent's 'array' cast will encode it on save
        $payment['gateway_response'] = [
            'ResultCode'           => 0,
            'ResultDesc'           => 'Success',
            'TransactionReference' => $payment['transaction_reference'],
        ];
    }

    private function fillCheque(array &$payment): void
    {
        $payment['transaction_reference'] = 'CHQ_' . strtoupper(Str::random(10));
        $payment['notes']                 = 'Cheque payment received';
    }

    private function fillCash(array &$payment): void
    {
        $payment['notes'] = 'Cash payment received';
    }

    private function syncInvoice(Invoice $invoice, float $totalPaid): void
    {
        $dueDate = $invoice->due_date
            ? Carbon::parse($invoice->due_date)
            : null;

        $status = match (true) {
            $totalPaid >= (float) $invoice->amount => 'paid',
            $totalPaid > 0                         => 'partially_paid',
            $dueDate && $dueDate->isPast()         => 'overdue',
            default                                => 'pending',
        };

        DB::table('invoices')->where('id', $invoice->id)->update([
            'amount_paid' => $totalPaid,
            'status'      => $status,
            'updated_at'  => now(),
        ]);
    }

    private function getRandomParentId(?int $studentId): ?int
    {
        if (!$studentId) {
            return Guardian::inRandomOrder()->value('id');
        }

        $student = Student::with('parents')->find($studentId);

        if ($student && $student->parents->isNotEmpty()) {
            return $student->parents->random()->id;
        }

        return Guardian::inRandomOrder()->value('id');
    }

    private function randomDateBetween(Carbon $startDate, Carbon $endDate): Carbon
    {
        return Carbon::createFromTimestamp(
            rand($startDate->timestamp, $endDate->timestamp)
        );
    }

    private function randomTime(): Carbon
    {
        return Carbon::createFromTime(rand(8, 17), rand(0, 59), rand(0, 59));
    }

private function generateMpesaReceipt(): string
{
    $prefixes = ['QWE', 'RTY', 'UIO', 'PAS', 'DFG', 'HJK', 'LZX', 'CVB', 'NMB', 'WER'];

    do {
        $prefix = $prefixes[array_rand($prefixes)];
        $code   = $prefix . $prefix . rand(100, 999) . 'T' . rand(1, 9);
    } while (Payment::where('mpesa_receipt', $code)->exists());

    return $code;
}
}