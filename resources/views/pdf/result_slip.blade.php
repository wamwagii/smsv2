<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Result Slip — {{ $student->full_name }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #222;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header h1 { margin: 0 0 4px 0; font-size: 20px; letter-spacing: 1px; }
        .header .school-name { font-size: 22px; font-weight: bold; color: #1a56db; margin-bottom: 4px; }
        .header .school-motto { font-size: 10px; font-style: italic; color: #666; margin-bottom: 6px; }
        .header .school-contact { font-size: 9px; color: #888; margin-bottom: 10px; }
        .header p { margin: 2px 0; color: #666; font-size: 12px; }
        .header .slip-title { font-size: 14px; font-weight: bold; letter-spacing: 2px; margin: 8px 0 4px 0; color: #333; }

        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .info-table td { padding: 4px 6px; vertical-align: top; }
        .info-table td.label-cell { font-weight: bold; width: 130px; color: #555; }

        .result-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .result-table th,
        .result-table td {
            border: 1px solid #999;
            padding: 6px 8px;
            text-align: left;
        }
        .result-table th {
            background: #f0f0f0;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .result-table td.number { text-align: right; }
        .result-table td.grade  { font-weight: bold; font-size: 12px; text-align: center; }

        /* Grade colors */
        .grade-A, .grade-Aminus   { color: #15803d; }
        .grade-Bplus, .grade-B, .grade-Bminus { color: #0369a1; }
        .grade-Cplus, .grade-C, .grade-Cminus { color: #b45309; }
        .grade-Dplus, .grade-D, .grade-Dminus { color: #b91c1c; }
        .grade-E { color: #7f1d1d; }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 20px;
        }
        .summary-table td {
            padding: 8px 10px;
            border: 1px solid #999;
            background: #fafafa;
        }
        .summary-table td.label {
            font-weight: bold;
            color: #555;
            background: #f0f0f0;
            width: 140px;
        }
        .summary-table td.value { font-weight: bold; font-size: 13px; }

        .comments {
            margin-top: 16px;
            padding: 10px 12px;
            background: #fafafa;
            border-left: 4px solid #4F46E5;
            font-size: 11px;
        }
        .comments strong { display: block; margin-bottom: 4px; }

        .grading-key {
            margin-top: 20px;
            border: 1px solid #ddd;
            padding: 8px 12px;
            background: #fafafa;
            font-size: 9px;
        }
        .grading-key strong { display: block; margin-bottom: 4px; font-size: 10px; }

        .footer {
            margin-top: 40px;
            font-size: 10px;
            color: #888;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 8px;
        }
    </style>
</head>
<body>

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

        <div class="slip-title">STUDENT RESULT SLIP</div>
        <p>{{ $exam->name }} &mdash; {{ ucfirst(str_replace('_', ' ', $exam->term)) }}</p>
        @if($exam->academicYear)
            <p>Academic Year: {{ $exam->academicYear->name }}</p>
        @endif
    </div>

    <table class="info-table">
        <tr>
            <td class="label-cell">Student Name</td>
            <td><strong>{{ $student->full_name }}</strong></td>
            <td class="label-cell">Admission No.</td>
            <td>{{ $student->admission_number }}</td>
        </tr>
        <tr>
            <td class="label-cell">Class</td>
            <td>{{ $student->class?->class_code ?? 'N/A' }}</td>
            <td class="label-cell">Roll Number</td>
            <td>{{ $student->roll_number ?? 'N/A' }}</td>
        </tr>
    </table>

    <table class="result-table">
        <thead>
            <tr>
                <th style="width: 30px;">#</th>
                <th>Subject</th>
                <th style="width: 55px;">Code</th>
                <th style="width: 70px; text-align: right;">Marks</th>
                <th style="width: 65px; text-align: right;">Total</th>
                <th style="width: 80px; text-align: right;">Percentage</th>
                <th style="width: 55px; text-align: center;">Grade</th>
            </tr>
        </thead>
        <tbody>
            @foreach($results as $index => $result)
                @php
                    $gradeClass = 'grade-' . str_replace(['+', '-'], ['plus', 'minus'], $result->grade ?? '');
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $result->subject?->name ?? '-' }}</td>
                    <td>{{ $result->subject?->code ?? '-' }}</td>
                    <td class="number">{{ number_format((float) $result->marks_obtained, 2) }}</td>
                    <td class="number">{{ $result->total_marks }}</td>
                    <td class="number">{{ number_format((float) $result->percentage, 2) }}%</td>
                    <td class="grade {{ $gradeClass }}">{{ $result->grade ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary-table">
        <tr>
            <td class="label">Total Marks</td>
            <td>{{ number_format((float) $totalMarksObtained, 2) }} / {{ number_format((float) $totalMarksPossible, 2) }}</td>
            <td class="label">Average</td>
            <td class="value">{{ number_format((float) $averagePercentage, 2) }}%</td>
        </tr>
        <tr>
            <td class="label">Overall Grade</td>
            <td class="value">{{ $overallGrade }}</td>
            <td class="label">Points</td>
            <td class="value">{{ $overallPoints }}</td>
        </tr>
        @if($rank)
            <tr>
                <td class="label">Class Position</td>
                <td class="value">{{ $rank }} of {{ $classSize }}</td>
                <td class="label">Subjects</td>
                <td>{{ $results->count() }}</td>
            </tr>
        @endif
    </table>

    <div class="grading-key">
        <strong>GRADING KEY</strong>
        A: 80–100 | A-: 75–79 | B+: 70–74 | B: 65–69 | B-: 60–64 |
        C+: 55–59 | C: 50–54 | C-: 45–49 | D+: 40–44 | D: 35–39 |
        D-: 30–34 | E: 0–29
    </div>

    <div class="footer">
        Generated on {{ now()->format('d/m/Y H:i') }} &middot;
        This is a computer-generated document.
    </div>

</body>
</html>