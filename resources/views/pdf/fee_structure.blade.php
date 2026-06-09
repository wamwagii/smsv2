<!DOCTYPE html>
<html>
<head>
    <title>Fee Structure - Grade {{ $class->level }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid #1a56db;
            padding: 20px;
            position: relative;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #1a56db;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .school-name {
            font-size: 24px;
            font-weight: bold;
            color: #1a56db;
            margin-bottom: 5px;
        }
        .school-motto {
            font-size: 12px;
            font-style: italic;
            color: #666;
        }
        .school-contact {
            font-size: 10px;
            color: #666;
            margin-top: 5px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
            background: #1a56db;
            color: white;
            padding: 8px;
        }
        .info-table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        .info-table td.label {
            font-weight: bold;
            width: 30%;
            background-color: #f3f4f6;
        }
        .breakdown-table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }
        .breakdown-table th {
            background-color: #1a56db;
            color: white;
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        .breakdown-table td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        .breakdown-table td:last-child {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
            background-color: #fef3c7;
        }
        .payment-plan-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .payment-plan-table th {
            background-color: #10b981;
            color: white;
            padding: 8px;
            text-align: left;
        }
        .payment-plan-table td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        .signatures {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            text-align: center;
            width: 45%;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 40px;
            padding-top: 10px;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 60px;
            color: rgba(0,0,0,0.05);
            white-space: nowrap;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="container">
        @if($feeStructure->is_active)
            <div class="watermark">ACTIVE</div>
        @endif
        
        <div class="header">
            <div class="school-name">School Management System</div>
            <div class="school-motto">Excellence in Education</div>
            <div class="school-contact">
                P.O. Box 12345-00100, Nairobi, Kenya | Tel: +254 700 123 456 | Email: info@school.ac.ke
            </div>
        </div>
        
        <div class="title">FEE STRUCTURE - {{ $class->name }}</div>
        
        <table class="info-table">
            <tr>
                <td class="label">Academic Year:</td>
                <td>{{ $academicYear->name }}</td>
                <td class="label">Grade Level:</td>
                <td>{{ $class->name }}</td>
            </tr>
            <tr>
                <td class="label">Effective Date:</td>
                <td>{{ $feeStructure->created_at->format('d/m/Y') }}</td>
                <td class="label">Status:</td>
                <td>
                    @if($feeStructure->is_active)
                        <span style="color: green;">✓ Active</span>
                    @else
                        <span style="color: red;">✗ Inactive</span>
                    @endif
                </td>
            </tr>
        </table>
        
        <h3>FEE BREAKDOWN</h3>
        <table class="breakdown-table">
            <thead>
                <tr>
                    <th>Fee Component</th>
                    <th>Amount (KES)</th>
                </tr>
            </thead>
            <tbody>
                @if($feeStructure->tuition_fees > 0)
                <tr>
                    <td>Tuition Fees</td>
                    <td style="text-align: right;">{{ number_format($feeStructure->tuition_fees, 2) }}</td>
                </tr>
                @endif
                @if($feeStructure->activity_fees > 0)
                <tr>
                    <td>Activity Fees</td>
                    <td style="text-align: right;">{{ number_format($feeStructure->activity_fees, 2) }}</td>
                </tr>
                @endif
                @if($feeStructure->library_fees > 0)
                <tr>
                    <td>Library Fees</td>
                    <td style="text-align: right;">{{ number_format($feeStructure->library_fees, 2) }}</td>
                </tr>
                @endif
                @if($feeStructure->sports_fees > 0)
                <tr>
                    <td>Sports Fees</td>
                    <td style="text-align: right;">{{ number_format($feeStructure->sports_fees, 2) }}</td>
                </tr>
                @endif
                @if($feeStructure->medical_fees > 0)
                <tr>
                    <td>Medical Fees</td>
                    <td style="text-align: right;">{{ number_format($feeStructure->medical_fees, 2) }}</td>
                </tr>
                @endif
                @if($feeStructure->transport_fees > 0)
                <tr>
                    <td>Transport Fees</td>
                    <td style="text-align: right;">{{ number_format($feeStructure->transport_fees, 2) }}</td>
                </tr>
                @endif
                @if($feeStructure->boarding_fees > 0)
                <tr>
                    <td>Boarding Fees</td>
                    <td style="text-align: right;">{{ number_format($feeStructure->boarding_fees, 2) }}</td>
                </tr>
                @endif
                @if($feeStructure->uniform_fees > 0)
                <tr>
                    <td>Uniform Fees</td>
                    <td style="text-align: right;">{{ number_format($feeStructure->uniform_fees, 2) }}</td>
                </tr>
                @endif
                @if($feeStructure->other_fees > 0)
                <tr>
                    <td>Other Fees</td>
                    <td style="text-align: right;">{{ number_format($feeStructure->other_fees, 2) }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td><strong>TOTAL ANNUAL FEES</strong></td>
                    <td style="text-align: right;"><strong>KES {{ number_format($feeStructure->tuition_fees + $feeStructure->activity_fees + $feeStructure->library_fees + $feeStructure->sports_fees + $feeStructure->medical_fees + $feeStructure->transport_fees + $feeStructure->boarding_fees + $feeStructure->uniform_fees + $feeStructure->other_fees, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>
        
        @if($feeStructure->payment_plan && count($feeStructure->payment_plan) > 0)
        <div class="payment-plan">
            <h3>PAYMENT PLAN</h3>
            <table class="payment-plan-table">
                <thead>
                    <tr>
                        <th>Term</th>
                        <th>Due Date</th>
                        <th>Amount (KES)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($feeStructure->payment_plan as $plan)
                    <tr>
                        <td>{{ ucfirst(str_replace('_', ' ', $plan['term'])) }}</td>
                        <td>{{ \Carbon\Carbon::parse($plan['due_date'])->format('d/m/Y') }}</td>
                        <td style="text-align: right;">{{ number_format($plan['amount'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
        
        <div class="signatures">
            <div class="signature-box">
                <div class="signature-line">_________________</div>
                <div>Finance Officer</div>
                <div style="font-size: 10px; color: #666;">(Finance Signature)</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">_________________</div>
                <div>Principal</div>
                <div style="font-size: 10px; color: #666;">(Principal's Signature)</div>
            </div>
        </div>
        
        <div class="footer">
            <p>This is a computer-generated document. No signature is required if digitally verified.</p>
            <p>Generated on: {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>