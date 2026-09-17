<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncInvoices extends Command
{
    protected $signature = 'invoices:sync {--dry-run : Show what would change without updating}';
    protected $description = 'Recalculate amount_paid and status for all invoices from their completed payments';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $processed = 0;
        $changed = 0;

        Invoice::query()->chunkById(200, function ($invoices) use (&$processed, &$changed, $dryRun) {
            foreach ($invoices as $invoice) {
                $calculated = (float) $invoice->payments()
                    ->where('status', 'completed')
                    ->sum('amount');

                $status = match (true) {
                    $calculated >= (float) $invoice->amount => 'paid',
                    $calculated > 0                         => 'partially_paid',
                    $invoice->due_date && $invoice->due_date->isPast() => 'overdue',
                    default                                 => 'pending',
                };

                $needsUpdate = abs((float) $invoice->amount_paid - $calculated) > 0.01
                    || $invoice->status !== $status;

                if ($needsUpdate) {
                    $changed++;

                    $this->line(sprintf(
                        'INV#%d: amount_paid %s → %s, status %s → %s',
                        $invoice->id,
                        number_format((float) $invoice->amount_paid, 2),
                        number_format($calculated, 2),
                        $invoice->status,
                        $status,
                    ));

                    if (!$dryRun) {
                        DB::table('invoices')->where('id', $invoice->id)->update([
                            'amount_paid' => $calculated,
                            'status'      => $status,
                            'updated_at'  => now(),
                        ]);
                    }
                }

                $processed++;
            }
        });

        $this->newLine();
        $this->info(
            "Processed {$processed} invoices. "
            . ($dryRun ? "Would sync {$changed}." : "Synced {$changed}.")
        );

        return self::SUCCESS;
    }
}