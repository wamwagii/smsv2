<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PrintController extends Controller
{
    public function printInvoice($id)
    {
        $invoice = Invoice::with(['student', 'student.class', 'payments'])->findOrFail($id);
        
        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'student' => $invoice->student,
        ]);
        
        // Sanitize filename - replace slashes and other invalid characters
        $filename = 'invoice_' . str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $invoice->invoice_number) . '.pdf';
        
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
        
        // Sanitize filename - replace slashes and other invalid characters
        $receiptNumber = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $payment->receipt_number);
        $filename = 'receipt_' . $receiptNumber . '.pdf';
        
        return $pdf->download($filename);
    }
}