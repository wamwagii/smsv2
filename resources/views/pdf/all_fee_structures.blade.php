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
        .school-motto {
            font-size: 10px;
            font-style: italic;
            color: #666;
            margin-top: 2px;
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
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #1a56db;
            color: white;
            padding: 8px 4px;
            text-align: center;
            font-size: 9px;
        }
        td {
            padding: 6px 4px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        td.number {
            text-align: right;
            white-space: nowrap;
        }
        td.grade-cell {
            text-align: left;
        }
        .total-row {
            background-color: #fef3c7;
            font-weight: bold;
        }
        .signatures {
            margin-top: 30px;
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
            border: none;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 8px;
            font-size: 11px;
            font-weight: bold;
        }
        .signature-role {
            font-size: 9px;
            color: #666;
            margin-top: 2px;
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
        <div class="school-name">{{ config('school.name') }}</div>
        @if(config('school.motto'))
            <div class="school-motto">{{ config('school.motto') }}</div>
        @endif
        <div class="report-title">{{ $title ?? 'COMPLETE FEE STRUCTURES REPORT' }}</div>
        <div class="info">
            {{ config('school.address') }} &middot;
            Tel: {{ config('school.phone') }} &middot;
            Email: {{ config('school.email') }}
        </div>
        <div class="info">
            Generated on: {{ $generatedDate->format('d/m/Y H:i:s') }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Grade</th>
                <th>Tuition</th>
                <th>Activity</th>
                <th>Library</th>
                <th>Sports</th>
                <th>Medical</th>
                <th>Transport</th>
                <th>Boarding</th>
                <th>Uniform</th>
                <th>Other</th>
                <th>Total (KES)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($feeStructures as $fs)
            <tr>
                <td class="grade-cell"><strong>{{ $fs->class?->name ?? 'N/A' }}</strong></td>
                <td class="number">{{ number_format((float) $fs->tuition_fees, 2) }}</td>
                <td class="number">{{ number_format((float) $fs->activity_fees, 2) }}</td>
                <td class="number">{{ number_format((float) $fs->library_fees, 2) }}</td>
                <td class="number">{{ number_format((float) $fs->sports_fees, 2) }}</td>
                <td class="number">{{ number_format((float) $fs->medical_fees, 2) }}</td>
                <td class="number">{{ number_format((float) $fs->transport_fees, 2) }}</td>
                <td class="number">{{ number_format((float) $fs->boarding_fees, 2) }}</td>
                <td class="number">{{ number_format((float) $fs->uniform_fees, 2) }}</td>
                <td class="number">{{ number_format((float) $fs->other_fees, 2) }}</td>
                <td class="number"><strong>{{ number_format((float) $fs->total_fees, 2) }}</strong></td>
            </tr>
            @endforeach
            
            </tr>
        </tbody>
    </table>

    <div class="signatures">
        <table class="signature-table">
            <tr>
                <td>
                    <div class="signature-line">Finance Officer</div>
                    <div class="signature-role">(Finance Signature)</div>
                    <div class="signature-role">Date: ___________</div>
                </td>
                <td>
                    <div class="signature-line">Principal</div>
                    <div class="signature-role">(Principal's Signature)</div>
                    <div class="signature-role">Date: ___________</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>This is an official document from {{ config('school.name') }}.</p>
        <p>For any queries, please contact the school finance office.</p>
    </div>
</body>
</html>