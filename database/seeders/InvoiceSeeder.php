<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\Student;
use App\Models\FeeStructure;
use App\Models\AcademicYears;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $students = Student::with('class')->get();

        if ($students->isEmpty()) {
            $this->command->warn('No students found. Please run StudentSeeder first.');
            return;
        }

        $academicYear = AcademicYears::where('is_current', true)->first()
            ?? AcademicYears::first();

        if (!$academicYear) {
            $this->command->error('No academic year found.');
            return;
        }

        $terms = ['term_1', 'term_2', 'term_3'];
        $created = 0;
        $updated = 0;

        DB::transaction(function () use ($students, $academicYear, $terms, &$created, &$updated) {
            foreach ($students as $student) {
                $yearId = $student->academic_year_id ?? $academicYear->id;

                $feeStructure = FeeStructure::where('class_id', $student->class_id)
                    ->where('academic_year_id', $yearId)
                    ->where('is_active', true)
                    ->first();

                if (!$feeStructure) {
                    $this->command->warn("No active fee structure for student {$student->admission_number}");
                    continue;
                }

                foreach ($terms as $term) {
                    [$amount, $dueDate] = $this->resolveAmountAndDueDate($feeStructure, $term);

                    $status = $this->getRandomStatus();
                    $amountPaid = match ($status) {
                        'paid'           => $amount,
                        'partially_paid' => round($amount * (rand(30, 70) / 100), 2),
                        default          => 0,
                    };

                    $existing = Invoice::where('student_id', $student->id)
                        ->where('term', $term)
                        ->where('fee_structure_id', $feeStructure->id)
                        ->first();

                    if ($existing) {
                        $existing->update([
                            'amount'      => $amount,
                            'amount_paid' => $amountPaid,
                            'due_date'    => $dueDate,
                            'status'      => $status,
                            'notes'       => $this->statusNote($status),
                        ]);
                        $updated++;
                    } else {
                        Invoice::create([
                            'student_id'       => $student->id,
                            'fee_structure_id' => $feeStructure->id,
                            'term'             => $term,
                            'amount'           => $amount,
                            'amount_paid'      => $amountPaid,
                            'due_date'         => $dueDate,
                            'status'           => $status,
                            'notes'            => $this->statusNote($status),
                            'created_at'       => $this->randomDateBetween(now()->subMonths(6), now()),
                        ]);
                        $created++;
                    }
                }
            }
        });

        $this->command->info("Invoices seeded: {$created} created, {$updated} updated.");
    }

    private function resolveAmountAndDueDate(FeeStructure $feeStructure, string $term): array
    {
        if (is_array($feeStructure->payment_plan) && count($feeStructure->payment_plan) > 0) {
            foreach ($feeStructure->payment_plan as $plan) {
                if (is_array($plan) && ($plan['term'] ?? null) === $term) {
                    return [
                        (float) ($plan['amount'] ?? 0),
                        $plan['due_date'] ?? $this->getDueDateForTerm($term),
                    ];
                }
            }
        }

        $totalFees = (float) (
            ($feeStructure->tuition_fees   ?? 0) +
            ($feeStructure->activity_fees  ?? 0) +
            ($feeStructure->library_fees   ?? 0) +
            ($feeStructure->sports_fees    ?? 0) +
            ($feeStructure->medical_fees   ?? 0) +
            ($feeStructure->transport_fees ?? 0) +
            ($feeStructure->boarding_fees  ?? 0) +
            ($feeStructure->uniform_fees   ?? 0) +
            ($feeStructure->other_fees     ?? 0)
        );

        return [round($totalFees / 3, 2), $this->getDueDateForTerm($term)];
    }

    private function getDueDateForTerm(string $term): string
    {
        $year = date('Y');
        return match ($term) {
            'term_1' => "{$year}-03-15",
            'term_2' => "{$year}-07-15",
            'term_3' => "{$year}-11-15",
            default  => now()->addDays(30)->format('Y-m-d'),
        };
    }

    private function getRandomStatus(): string
    {
        $rand = rand(1, 10);
        if ($rand <= 4) return 'paid';
        if ($rand <= 7) return 'partially_paid';
        return 'pending';
    }

    private function statusNote(string $status): ?string
    {
        return match ($status) {
            'paid'           => 'Fully paid',
            'partially_paid' => 'Partial payment received',
            default          => null,
        };
    }

    private function randomDateBetween($start, $end): \Carbon\Carbon
    {
        return \Carbon\Carbon::createFromTimestamp(rand($start->timestamp, $end->timestamp));
    }
}