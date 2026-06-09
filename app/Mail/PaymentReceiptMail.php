<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PaymentReceiptMail extends Mailable
{
    use Queueable, SerializesModels;
    
    public Payment $payment;
    public string $pdfPath;
    
    public function __construct(Payment $payment, string $pdfPath)
    {
        $this->payment = $payment;
        $this->pdfPath = $pdfPath;
    }
    
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment Receipt - ' . $this->payment->receipt_number,
        );
    }
    
    public function content(): Content
    {
        return new Content(
            view: 'emails.payment_receipt',
        );
    }
    
    public function attachments(): array
    {
        $fullPath = storage_path('app/public/' . $this->pdfPath);
        
        if (file_exists($fullPath)) {
            return [
                Attachment::fromPath($fullPath)
                    ->as('receipt_' . $this->payment->receipt_number . '.pdf')
                    ->withMime('application/pdf'),
            ];
        }
        
        return [];
    }
}