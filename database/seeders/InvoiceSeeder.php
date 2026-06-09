<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\Student;
use App\Models\FeeStructure;
use App\Models\AcademicYears;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $students = Student::all();
        
        if ($students->isEmpty()) {
            $this->command->warn('No students found. Please run StudentSeeder first.');
            return;
        }
        
        $academicYear = AcademicYears::where('is_current', true)->first();
        
        if (!$academicYear) {
            $academicYear = AcademicYears::first();
        }
        
        if (!$academicYear) {
            $this->command->error('No academic year found.');
            return;
        }
        
        $terms = ['term_1', 'term_2', 'term_3'];
        $invoiceCounter = 1;
        $invoices = [];
        
        foreach ($students as $student) {
            $feeStructure = FeeStructure::where('class_id', $student->class_id)
                ->where('academic_year_id', $academicYear->id)
                ->first();
            
            if (!$feeStructure) {
                $this->command->warn("No fee structure for student {$student->admission_number}");
                continue;
            }
            
            foreach ($terms as $term) {
                $amount = 0;
                $dueDate = null;
                
                if ($feeStructure->payment_plan && is_array($feeStructure->payment_plan) && count($feeStructure->payment_plan) > 0) {
                    foreach ($feeStructure->payment_plan as $plan) {
                        if (is_array($plan) && isset($plan['term']) && $plan['term'] === $term) {
                            $amount = $plan['amount'];
                            $dueDate = $plan['due_date'] ?? $this->getDueDateForTerm($term);
                            break;
                        }
                    }
                }
                
                if ($amount === 0) {
                    $totalFees = $feeStructure->tuition_fees + $feeStructure->activity_fees + 
                                 $feeStructure->library_fees + $feeStructure->sports_fees + 
                                 $feeStructure->medical_fees + $feeStructure->transport_fees + 
                                 $feeStructure->boarding_fees + $feeStructure->uniform_fees + 
                                 $feeStructure->other_fees;
                    $amount = round($totalFees / 3, 2);
                    $dueDate = $this->getDueDateForTerm($term);
                }
                
                // Create more unpaid invoices (70% unpaid, 30% paid)
                $rand = rand(1, 10);
                if ($rand <= 3) {
                    // Fully paid (30%)
                    $status = 'paid';
                    $amountPaid = $amount;
                } elseif ($rand <= 6) {
                    // Partially paid (30%)
                    $status = 'partially_paid';
                    $amountPaid = round($amount * (rand(30, 70) / 100), 2);
                } else {
                    // Pending (40%)
                    $status = 'pending';
                    $amountPaid = 0;
                }
                
                $invoices[] = [
                    'invoice_number' => 'INV/' . date('Y') . '/' . str_pad($invoiceCounter, 4, '0', STR_PAD_LEFT),
                    'student_id' => $student->id,
                    'fee_structure_id' => $feeStructure->id,
                    'term' => $term,
                    'amount' => $amount,
                    'amount_paid' => $amountPaid,
                    'due_date' => $dueDate,
                    'status' => $status,
                    'notes' => $status === 'paid' ? 'Fully paid' : ($status === 'partially_paid' ? 'Partial payment received' : null),
                    'created_at' => $this->randomDateBetween(now()->subMonths(6), now()),
                    'updated_at' => now(),
                ];
                
                $invoiceCounter++;
            }
        }
        
        if (!empty($invoices)) {
            foreach (array_chunk($invoices, 50) as $chunk) {
                Invoice::insert($chunk);
            }
            $this->command->info(count($invoices) . ' invoices created successfully.');
        } else {
            $this->command->warn('No invoices were created.');
        }
    }
    
    private function getDueDateForTerm($term)
    {
        $year = date('Y');
        return match($term) {
            'term_1' => "$year-03-15",
            'term_2' => "$year-07-15",
            'term_3' => "$year-11-15",
            default => now()->addDays(30)->format('Y-m-d'),
        };
    }
    
    private function randomDateBetween($startDate, $endDate)
    {
        $timestamp = rand($startDate->timestamp, $endDate->timestamp);
        return \Carbon\Carbon::createFromTimestamp($timestamp);
    }
}