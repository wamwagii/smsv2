<!DOCTYPE html>
<html>
<head>
    <title>Receipt {{ $payment->receipt_number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .school-name { font-size: 20px; font-weight: bold; color: #4F46E5; }
        .receipt-title { font-size: 16px; font-weight: bold; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f3f4f6; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #666; }
        .signature { margin-top: 40px; display: flex; justify-content: space-between; }
        .signature-line { border-top: 1px solid #333; width: 200px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="school-name">School Management System</div>
        <div class="receipt-title">OFFICIAL PAYMENT RECEIPT</div>
        <p>Receipt Number: {{ $payment->receipt_number }}</p>
    </div>
    
    <table>
        <tr>
            <th style="width: 30%">Student Name:</th>
            <td>{{ $student->first_name }} {{ $student->last_name }}</td>
            <th style="width: 30%">Admission No:</th>
            <td>{{ $student->admission_number }}</td>
        </tr>
        <tr>
            <th>Class:</th>
            <td>{{ $student->class->class_code ?? 'N/A' }}</td>
            <th>Invoice No:</th>
            <td>{{ $invoice->invoice_number ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Payment Date:</th>
            <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
            <th>Payment Method:</th>
            <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
        </tr>
        @if($payment->mpesa_receipt)
        <tr>
            <th>M-Pesa Receipt:</th>
            <td colspan="3">{{ $payment->mpesa_receipt }}</td>
        </tr>
        @endif
        @if($payment->transaction_reference)
        <tr>
            <th>Transaction Ref:</th>
            <td colspan="3">{{ $payment->transaction_reference }}</td>
        </tr>
        @endif
        <tr>
            <th>Amount Paid:</th>
            <td colspan="3"><strong>KES {{ number_format($payment->amount, 2) }}</strong></td>
        </tr>
    </table>
    
    <div class="signature">
        <div class="signature-line">
            <div>Cashier's Signature</div>
        </div>
        <div class="signature-line">
            <div>Student's Signature</div>
        </div>
    </div>
    
    <div class="footer">
        <p>This is a computer-generated receipt. No physical signature required.</p>
        <p>Generated on: {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>