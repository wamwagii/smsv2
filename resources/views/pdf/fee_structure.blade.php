<!DOCTYPE html>
<html>
<head>
    <title>Fee Structure - {{ $class->name }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 10px;
            font-size: 12px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 10px;
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
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }
        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            padding: 0 20px;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 8px;
            font-size: 12px;
            font-weight: bold;
        }
        .signature-role {
            font-size: 10px;
            color: #666;
            margin-top: 2px;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="school-name">{{ config('school.name') }}</div>
            @if(config('school.motto'))
                <div class="school-motto">{{ config('school.motto') }}</div>
            @endif
            <div class="school-contact">
                {{ config('school.address') }} &middot;
                Tel: {{ config('school.phone') }} &middot;
                Email: {{ config('school.email') }}
            </div>
        </div>

        <div class="title">FEE STRUCTURE  {{ $academicYear->name }}- {{ $class->name }}</div>
        <table class="breakdown-table">
            <thead>
                <tr>
                    <th>Fee Component</th>
                    <th style="text-align: right;">Amount (KES)</th>
                </tr>
            </thead>
            <tbody>
                @if((float) $feeStructure->tuition_fees > 0)
                <tr>
                    <td>Tuition Fees</td>
                    <td style="text-align: right;">{{ number_format((float) $feeStructure->tuition_fees, 2) }}</td>
                </tr>
                @endif
                @if((float) $feeStructure->activity_fees > 0)
                <tr>
                    <td>Activity Fees</td>
                    <td style="text-align: right;">{{ number_format((float) $feeStructure->activity_fees, 2) }}</td>
                </tr>
                @endif
                @if((float) $feeStructure->library_fees > 0)
                <tr>
                    <td>Library Fees</td>
                    <td style="text-align: right;">{{ number_format((float) $feeStructure->library_fees, 2) }}</td>
                </tr>
                @endif
                @if((float) $feeStructure->sports_fees > 0)
                <tr>
                    <td>Sports Fees</td>
                    <td style="text-align: right;">{{ number_format((float) $feeStructure->sports_fees, 2) }}</td>
                </tr>
                @endif
                @if((float) $feeStructure->medical_fees > 0)
                <tr>
                    <td>Medical Fees</td>
                    <td style="text-align: right;">{{ number_format((float) $feeStructure->medical_fees, 2) }}</td>
                </tr>
                @endif
                @if((float) $feeStructure->transport_fees > 0)
                <tr>
                    <td>Transport Fees</td>
                    <td style="text-align: right;">{{ number_format((float) $feeStructure->transport_fees, 2) }}</td>
                </tr>
                @endif
                @if((float) $feeStructure->boarding_fees > 0)
                <tr>
                    <td>Boarding Fees</td>
                    <td style="text-align: right;">{{ number_format((float) $feeStructure->boarding_fees, 2) }}</td>
                </tr>
                @endif
                @if((float) $feeStructure->uniform_fees > 0)
                <tr>
                    <td>Uniform Fees</td>
                    <td style="text-align: right;">{{ number_format((float) $feeStructure->uniform_fees, 2) }}</td>
                </tr>
                @endif
                @if((float) $feeStructure->other_fees > 0)
                <tr>
                    <td>Other Fees</td>
                    <td style="text-align: right;">{{ number_format((float) $feeStructure->other_fees, 2) }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td><strong>TOTAL ANNUAL FEES</strong></td>
                    <td style="text-align: right;">
                        <strong>KES {{ number_format((float) $feeStructure->total_fees, 2) }}</strong>
                    </td>
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
                        <th style="text-align: right;">Amount (KES)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($feeStructure->payment_plan as $plan)
                    <tr>
                        <td>{{ ucfirst(str_replace('_', ' ', $plan['term'] ?? '-')) }}</td>
                        <td style="text-align: right;">{{ number_format((float) ($plan['amount'] ?? 0), 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <div class="signatures">
            <table class="signature-table">
                <tr>
                    <td>
                        <div class="signature-line">Finance Officer</div>
                        <div class="signature-role">(Finance Signature)</div>
                    </td>
                    <td>
                        <div class="signature-line">Principal</div>
                        <div class="signature-role">(Principal's Signature)</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="footer">
            <p>This is a computer-generated document. No signature is required if digitally verified.</p>
            <p>Generated on: {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>