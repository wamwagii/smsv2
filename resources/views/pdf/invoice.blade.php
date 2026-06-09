<!DOCTYPE html>
<html>
<head>
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .school-name { font-size: 20px; font-weight: bold; color: #4F46E5; }
        .invoice-title { font-size: 16px; font-weight: bold; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f3f4f6; }
        .total { font-weight: bold; background-color: #fef3c7; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <div class="school-name">School Management System</div>
        <div class="invoice-title">INVOICE</div>
        <p>Invoice Number: {{ $invoice->invoice_number }}</p>
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
            <th>Term:</th>
            <td>{{ ucfirst(str_replace('_', ' ', $invoice->term)) }}</td>
        </tr>
        <tr>
            <th>Due Date:</th>
            <td>{{ $invoice->due_date->format('d/m/Y') }}</td>
            <th>Status:</th>
            <td>{{ ucfirst($invoice->status) }}</td>
        </tr>
    </table>
    
    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Amount (KES)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>School Fees - {{ ucfirst(str_replace('_', ' ', $invoice->term)) }}</td>
                <td>{{ number_format($invoice->amount, 2) }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr class="total">
                <td><strong>Total</strong></td>
                <td><strong>KES {{ number_format($invoice->amount, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>
    
    @if($invoice->payments->count() > 0)
    <table>
        <thead>
            <tr>
                <th>Payment Date</th>
                <th>Receipt No.</th>
                <th>Method</th>
                <th>Amount (KES)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->payments as $payment)
            <tr>
                <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                <td>{{ $payment->receipt_number }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                <td>{{ number_format($payment->amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total">
                <td colspan="3"><strong>Total Paid</strong></td>
                <td><strong>KES {{ number_format($invoice->amount_paid, 2) }}</strong></td>
            </tr>
            <tr>
                <td colspan="3"><strong>Balance</strong></td>
                <td><strong>KES {{ number_format($invoice->balance, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>
    @endif
    
    <div class="footer">
        <p>Thank you for your payment!</p>
        <p>Generated on: {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>