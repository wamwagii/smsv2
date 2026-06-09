<!DOCTYPE html>
<html>
<head>
    <title>{{ $title ?? 'Fee Structures Report' }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 10px;
            font-size: 10px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #1a56db;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .school-name {
            font-size: 18px;
            font-weight: bold;
            color: #1a56db;
        }
        .report-title {
            font-size: 16px;
            font-weight: bold;
            margin: 10px 0;
        }
        .info {
            text-align: center;
            font-size: 10px;
            color: #666;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #1a56db;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }
        td {
            padding: 6px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        .total-row {
            background-color: #fef3c7;
            font-weight: bold;
        }
        .signatures {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            text-align: center;
            width: 45%;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 30px;
            padding-top: 8px;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 8px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="school-name">School Management System</div>
        <div class="report-title">{{ $title ?? 'COMPLETE FEE STRUCTURES REPORT' }}</div>
        <div class="info">
            P.O. Box 12345-00100, Nairobi, Kenya | Tel: +254 700 123 456 | Email: info@school.ac.ke
        </div>
        <div class="info">
            Generated on: {{ $generatedDate->format('d/m/Y H:i:s') }}
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Grade</th>
                <th>Tuition (KES)</th>
                <th>Activity (KES)</th>
                <th>Library (KES)</th>
                <th>Sports (KES)</th>
                <th>Medical (KES)</th>
                <th>Transport (KES)</th>
                <th>Boarding (KES)</th>
                <th>Uniform (KES)</th>
                <th>Other (KES)</th>
                <th>Total (KES)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($feeStructures as $fs)
            <tr>
                <td><strong>Grade {{ $fs->class->level }}</strong></td>
                <td>{{ number_format($fs->tuition_fees, 2) }}</td>
                <td>{{ number_format($fs->activity_fees, 2) }}</td>
                <td>{{ number_format($fs->library_fees, 2) }}</td>
                <td>{{ number_format($fs->sports_fees, 2) }}</td>
                <td>{{ number_format($fs->medical_fees, 2) }}</td>
                <td>{{ number_format($fs->transport_fees, 2) }}</td>
                <td>{{ number_format($fs->boarding_fees, 2) }}</td>
                <td>{{ number_format($fs->uniform_fees, 2) }}</td>
                <td>{{ number_format($fs->other_fees, 2) }}</td>
                <td>{{ number_format($fs->tuition_fees + $fs->activity_fees + $fs->library_fees + $fs->sports_fees + $fs->medical_fees + $fs->transport_fees + $fs->boarding_fees + $fs->uniform_fees + $fs->other_fees, 2) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="10" style="text-align: right;"><strong>GRAND TOTAL:</strong></td>
                <td><strong>KES {{ number_format($feeStructures->sum(function($fs) { return $fs->tuition_fees + $fs->activity_fees + $fs->library_fees + $fs->sports_fees + $fs->medical_fees + $fs->transport_fees + $fs->boarding_fees + $fs->uniform_fees + $fs->other_fees; }), 2) }}</strong></td>
            </tr>
        </tbody>
    </table>
    
    <div class="signatures">
        <div class="signature-box">
            <div class="signature-line">_________________</div>
            <div>Finance Officer</div>
            <div style="font-size: 9px;">(Finance Signature)</div>
            <div style="font-size: 9px;">Date: ___________</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">_________________</div>
            <div>Principal</div>
            <div style="font-size: 9px;">(Principal's Signature)</div>
            <div style="font-size: 9px;">Date: ___________</div>
        </div>
    </div>
    
    <div class="footer">
        <p>This is an official document from School Management System.</p>
        <p>For any queries, please contact the school finance office.</p>
    </div>
</body>
</html>