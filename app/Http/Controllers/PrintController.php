<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\FeeStructure;
use App\Models\Result;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PrintController extends Controller
{
    private function sanitizeFilename($filename)
    {
        $invalid = ['/', '\\', ':', '*', '?', '"', '<', '>', '|', ' '];
        $replace = ['-', '-', '-', '-', '-', '-', '-', '-', '-', '_'];
        return str_replace($invalid, $replace, $filename);
    }
    
    public function printInvoice($id)
    {
        $invoice = Invoice::with(['student', 'student.class', 'payments'])->findOrFail($id);
        
        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'student' => $invoice->student,
        ]);
        
        $filename = $this->sanitizeFilename('invoice_' . $invoice->invoice_number . '.pdf');
        
        return $pdf->download($filename);
    }
    
    public function printReceipt($id)
    {
        $payment = Payment::with(['student', 'invoice'])->findOrFail($id);
        
        $pdf = Pdf::loadView('pdf.receipt', [
            'payment' => $payment,
            'student' => $payment->student,
            'invoice' => $payment->invoice,
        ]);
        
        $filename = $this->sanitizeFilename('receipt_' . $payment->receipt_number . '.pdf');
        
        return $pdf->download($filename);
    }
    
    public function printFeeStructure($id)
    {
        $feeStructure = FeeStructure::with(['class', 'academicYear'])->findOrFail($id);
        
        $pdf = Pdf::loadView('pdf.fee_structure', [
            'feeStructure' => $feeStructure,
            'class' => $feeStructure->class,
            'academicYear' => $feeStructure->academicYear,
        ]);
        
        $filename = $this->sanitizeFilename('fee_structure_grade_' . $feeStructure->class->level . '.pdf');
        
        return $pdf->download($filename);
    }
    
    public function printAllFeeStructures()
    {
        $feeStructures = FeeStructure::with(['class', 'academicYear'])
            ->orderBy('class_id')
            ->get();
        
        $pdf = Pdf::loadView('pdf.all_fee_structures', [
            'feeStructures' => $feeStructures,
            'generatedDate' => now(),
            'title' => 'Complete Fee Structures Report',
        ]);
        
        return $pdf->download('all_fee_structures_' . date('Y-m-d') . '.pdf');
    }
    
    public function printSelectedFeeStructures(Request $request)
    {
        $ids = explode(',', $request->input('ids'));
        
        $feeStructures = FeeStructure::with(['class', 'academicYear'])
            ->whereIn('id', $ids)
            ->orderBy('class_id')
            ->get();
        
        $pdf = Pdf::loadView('pdf.all_fee_structures', [
            'feeStructures' => $feeStructures,
            'generatedDate' => now(),
            'title' => 'Selected Fee Structures Report',
        ]);
        
        $filename = 'selected_fee_structures_' . date('Y-m-d_His') . '.pdf';
        
        return $pdf->download($filename);
    }
    
    public function printFeeStructuresByGrade(Request $request)
    {
        $startGrade = $request->input('start_grade', 1);
        $endGrade = $request->input('end_grade', 12);
        
        $feeStructures = FeeStructure::with(['class', 'academicYear'])
            ->whereHas('class', function ($query) use ($startGrade, $endGrade) {
                $query->whereBetween('level', [$startGrade, $endGrade]);
            })
            ->orderBy('class_id')
            ->get();
        
        $pdf = Pdf::loadView('pdf.all_fee_structures', [
            'feeStructures' => $feeStructures,
            'generatedDate' => now(),
            'title' => "Fee Structures - Grades {$startGrade} to {$endGrade}",
        ]);
        
        $filename = "fee_structures_grades_{$startGrade}_to_{$endGrade}_" . date('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }
    
    /**
     * Print a full result slip for a student in a specific exam.
     * Includes ALL subjects, totals, average, grade, and class rank.
     */
    public function printResultSlip($studentId, $examId)
    {
        $student = \App\Models\Student::with('class')->findOrFail($studentId);
        $exam = \App\Models\Exam::with('academicYear')->findOrFail($examId);

        $results = Result::with('subject')
            ->where('student_id', $studentId)
            ->where('exam_id', $examId)
            ->get()
            ->sortBy(fn ($r) => $r->subject?->name ?? '');

        if ($results->isEmpty()) {
            abort(404, 'No results found for this student in the selected exam.');
        }

        // Totals and average
        $totalMarksObtained = (float) $results->sum('marks_obtained');
        $totalMarksPossible = (float) $results->sum('total_marks');
        $averagePercentage  = $totalMarksPossible > 0
            ? round(($totalMarksObtained / $totalMarksPossible) * 100, 2)
            : 0.0;

        $overallGrade  = Result::calculateGrade($averagePercentage);
        $overallPoints = Result::getGradePoint($overallGrade);

        // Class rank
        $rankings  = $this->classRankingsForExam($student->class_id, (int) $examId);
        $rankData  = $rankings->firstWhere('student_id', $studentId);
        $classSize = $rankings->count();

        $pdf = Pdf::loadView('pdf.result_slip', [
            'student'            => $student,
            'exam'               => $exam,
            'results'            => $results,
            'totalMarksObtained' => $totalMarksObtained,
            'totalMarksPossible' => $totalMarksPossible,
            'averagePercentage'  => $averagePercentage,
            'overallGrade'       => $overallGrade,
            'overallPoints'      => $overallPoints,
            'rank'               => $rankData['position'] ?? null,
            'classSize'          => $classSize,
        ]);

        $admission = $this->sanitizeFilename($student->admission_number ?? 'student');
        $examName  = $this->sanitizeFilename($exam->name ?? 'exam');

        return $pdf->download("result_slip_{$admission}_{$examName}.pdf");
    }

    /**
     * Compute dense-rank standings of all students in a class for a given exam,
     * based on their average percentage across all subjects.
     *
     * @return \Illuminate\Support\Collection<int, array{student_id:int, position:int, average:float}>
     */
    private function classRankingsForExam(?int $classId, int $examId)
    {
        if (!$classId) {
            return collect();
        }

        $studentAverages = Result::query()
            ->where('class_id', $classId)
            ->where('exam_id', $examId)
            ->get()
            ->groupBy('student_id')
            ->map(function ($studentResults) {
                $obtained = (float) $studentResults->sum('marks_obtained');
                $possible = (float) $studentResults->sum('total_marks');
                return [
                    'student_id' => $studentResults->first()->student_id,
                    'average'    => $possible > 0 ? round(($obtained / $possible) * 100, 2) : 0.0,
                ];
            })
            ->sortByDesc('average')
            ->values();

        $rank = 1;
        $previousAverage = null;
        $position = 0;

        return $studentAverages->map(function ($row, $index) use (&$rank, &$previousAverage) {
            if ($previousAverage !== null && $row['average'] == $previousAverage) {
                // Tie — same position as previous
            } else {
                $rank = $index + 1;
            }
            $previousAverage = $row['average'];

            return [
                'student_id' => $row['student_id'],
                'position'   => $rank,
                'average'    => $row['average'],
            ];
        });
    }
}