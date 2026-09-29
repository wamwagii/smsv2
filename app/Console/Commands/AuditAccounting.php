<?php

namespace App\Console\Commands;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AuditAccounting extends Command
{
    protected $signature = 'audit:accounting';

    protected $description = 'Verify accounting invariants across invoices, payments, and students.';

    public function handle(): int
    {
        $this->newLine();
        $this->info('═══════════════════════════════════════════════════');
        $this->info('  Accounting Integrity Audit');
        $this->info('═══════════════════════════════════════════════════');
        $this->newLine();

        $violations = 0;

        // ── Invariant 1: Invoice balance = amount - amount_paid ────────
        $this->line('1. Invoice balance calculation (excludes written-off)');

        $badBalance = Invoice::query()
            ->whereNotIn('status', InvoiceStatus::writtenOff())
            ->whereRaw('ROUND(amount - amount_paid, 2) != ROUND(balance, 2)')
            ->count();

        if ($badBalance > 0) {
            $this->error("   ✗ {$badBalance} invoices have incorrect balance");
            $violations += $badBalance;

            $samples = Invoice::query()
                ->whereNotIn('status', InvoiceStatus::writtenOff())
                ->whereRaw('ROUND(amount - amount_paid, 2) != ROUND(balance, 2)')
                ->take(5)
                ->get(['id', 'invoice_number', 'amount', 'amount_paid', 'balance', 'status']);

            $this->table(
                ['ID', 'Invoice #', 'Amount', 'Paid', 'Stored Balance', 'Expected'],
                $samples->map(fn ($i) => [
                    $i->id,
                    $i->invoice_number,
                    $i->amount,
                    $i->amount_paid,
                    $i->balance,
                    round($i->amount - $i->amount_paid, 2),
                ])->toArray()
            );
        } else {
            $this->info('   ✓ All active invoice balances correct');
        }
        $this->newLine();

        // ── Invariant 2: Invoice status matches amount_paid ───────────
        $this->line('2. Invoice status vs amount_paid');

        $badStatus = Invoice::query()
            ->whereNotIn('status', InvoiceStatus::writtenOff())
            ->where(function ($q) {
                $q->whereRaw("status = 'paid' AND amount_paid < amount")
                  ->orWhereRaw("status = 'pending' AND amount_paid > 0")
                  ->orWhereRaw("status = 'partially_paid' AND (amount_paid = 0 OR amount_paid >= amount)");
            })
            ->count();

        if ($badStatus > 0) {
            $this->error("   ✗ {$badStatus} invoices have status that doesn't match amount_paid");
            $violations += $badStatus;
        } else {
            $this->info('   ✓ All invoice statuses consistent');
        }
        $this->newLine();

        // ── Invariant 3: Student totals match sum of invoices ─────────
        $this->line('3. Student billed / paid / balance totals');

        $badStudents = Student::query()
            ->whereHas('invoices')
            ->with('invoices')
            ->get()
            ->filter(function (Student $student) {
                // Gross sums from the invoice collection (all invoices)
                $invoicesBilled = (float) $student->invoices->sum('amount');
                $invoicesPaid   = (float) $student->invoices->sum('amount_paid');

                // Sum of each invoice's balance accessor — this respects
                // the written-off override.
                $invoiceBalanceSum = (float) $student->invoices->sum(fn (Invoice $i) => $i->balance);

                return abs($invoicesBilled - $student->total_billed) > 0.01
                    || abs($invoicesPaid - $student->total_paid) > 0.01
                    || abs($invoiceBalanceSum - $student->balance) > 0.01;
            });

        if ($badStudents->count() > 0) {
            $this->error("   ✗ {$badStudents->count()} students have mismatched totals");
            $violations += $badStudents->count();

            $this->table(
                ['ID', 'Name', 'Billed', 'Paid', 'Balance', 'Inv. Balance Sum'],
                $badStudents->take(5)->map(fn ($s) => [
                    $s->id,
                    $s->full_name,
                    number_format($s->total_billed, 2),
                    number_format($s->total_paid, 2),
                    number_format($s->balance, 2),
                    number_format($s->invoices->sum(fn ($i) => $i->balance), 2),
                ])->toArray()
            );
        } else {
            $this->info('   ✓ All student totals match their invoices');
        }
        $this->newLine();

        // ── Invariant 4: Payment sum matches invoice.amount_paid ──────
        $this->line('4. Invoice amount_paid vs sum of linked payments');

        $mismatchedInvoices = Invoice::query()
            ->whereHas('payments')
            ->withSum(['payments as linked_payments' => fn ($q) => $q->where('status', 'completed')], 'amount')
            ->get()
            ->filter(function (Invoice $invoice) {
                $expectedPaid = (float) ($invoice->linked_payments ?? 0);
                $storedPaid   = (float) $invoice->amount_paid;

                return abs($expectedPaid - $storedPaid) > 0.01;
            });

        if ($mismatchedInvoices->count() > 0) {
            $this->error("   ✗ {$mismatchedInvoices->count()} invoices have amount_paid that doesn't match linked payments");
            $violations += $mismatchedInvoices->count();

            $this->table(
                ['Invoice #', 'amount_paid', 'Linked payments', 'Difference'],
                $mismatchedInvoices->take(5)->map(fn ($i) => [
                    $i->invoice_number,
                    $i->amount_paid,
                    $i->linked_payments ?? 0,
                    round(($i->linked_payments ?? 0) - $i->amount_paid, 2),
                ])->toArray()
            );
        } else {
            $this->info('   ✓ All invoices have amount_paid matching their payments');
        }
        $this->newLine();

        // ── Invariant 5: Overpayment check ────────────────────────────
        $this->line('5. Overpaid invoices (excludes written-off)');

        $overpaid = Invoice::query()
            ->whereNotIn('status', InvoiceStatus::writtenOff())
            ->whereRaw('amount_paid > amount')
            ->count();

        if ($overpaid > 0) {
            $this->warn("   ⚠ {$overpaid} invoices are overpaid (credits)");
        } else {
            $this->info('   ✓ No overpaid invoices');
        }
        $this->newLine();

        // ── Invariant 6: Orphan payments ──────────────────────────────
        $this->line('6. Orphan payments');

        $orphanPayments = Payment::query()
            ->whereNull('invoice_id')
            ->orWhereNull('student_id')
            ->count();

        if ($orphanPayments > 0) {
            $this->error("   ✗ {$orphanPayments} payments are missing invoice_id or student_id");
            $violations += $orphanPayments;
        } else {
            $this->info('   ✓ All payments are properly linked');
        }
        $this->newLine();

        // ── Invariant 7: System-wide balance reconciliation ──────────
        $this->line('7. Student balance vs sum of invoice balances');

        // Student balance — sum of each student's accessor.
        // The accessor respects the written-off override per invoice.
        $totalStudentBalance = (float) Student::query()
            ->whereHas('invoices')
            ->with('invoices')
            ->get()
            ->sum(fn (Student $s) => $s->balance);

        // Invoice balance — same logic computed in SQL for speed.
        // Written-off invoices contribute 0.
        $totalInvoiceBalance = (float) Invoice::query()
            ->selectRaw(
                'SUM(CASE WHEN status IN (?, ?) THEN 0 ELSE balance END) as total',
                [
                    InvoiceStatus::Waived->value,
                    InvoiceStatus::Cancelled->value,
                ]
            )
            ->value('total') ?? 0;

        $difference = round($totalStudentBalance - $totalInvoiceBalance, 2);

        $this->line("   Total student balance:  KES " . number_format($totalStudentBalance, 2));
        $this->line("   Total invoice balance:  KES " . number_format($totalInvoiceBalance, 2));
        $this->line("   Difference:             KES " . number_format($difference, 2));

        if (abs($difference) > 0.01) {
            $this->error("   ✗ System-wide balance doesn't reconcile");
            $violations++;
        } else {
            $this->info('   ✓ System-wide balances reconcile');
        }
        $this->newLine();

        // ── Summary ────────────────────────────────────────────────────
        $this->info('═══════════════════════════════════════════════════');

        if ($violations === 0) {
            $this->info('  ✓ ALL INVARIANTS PASS');
        } else {
            $this->error("  ✗ {$violations} violation(s) found");
        }

        $this->info('═══════════════════════════════════════════════════');
        $this->newLine();

        return $violations === 0 ? self::SUCCESS : self::FAILURE;
    }
}