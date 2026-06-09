<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\FeeStructure;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PrintController extends Controller
{
    private function sanitizeFilename($filename)
    {
        // Remove any characters that are not allowed in filenames
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
    
    // Print single fee structure
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
    
    // Print all fee structures
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
    
    // Print selected fee structures
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
    
    // Print fee structures for a specific grade range
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
}