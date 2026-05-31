<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; }
        h1 { font-size: 18px; margin: 0 0 4px; }
        h2 { font-size: 13px; margin: 18px 0 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #d1d5db; padding: 6px; text-align: left; }
        th { background: #f3f4f6; font-weight: bold; }
        .muted { color: #6b7280; }
        .summary { width: 45%; margin-right: 2%; display: inline-table; vertical-align: top; }
    </style>
</head>
<body>
    <h1>Attendance Report</h1>
    <p class="muted">{{ DateTime::createFromFormat('!m', (int) $month)->format('F') }} {{ $year }}</p>

    <table>
        <tr>
            <th>Total</th>
            <th>Present</th>
            <th>Absent</th>
            <th>Half Day</th>
            <th>WFH</th>
        </tr>
        <tr>
            <td>{{ $totals['total'] }}</td>
            <td>{{ $totals['present'] }}</td>
            <td>{{ $totals['absent'] }}</td>
            <td>{{ $totals['half_day'] }}</td>
            <td>{{ $totals['wfh'] }}</td>
        </tr>
    </table>

    <h2>Monthly Report Per Employee</h2>
    <table>
        <thead>
            <tr>
                <th>Employee</th>
                <th>Code</th>
                <th>Present</th>
                <th>Absent</th>
                <th>Half Day</th>
                <th>WFH</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($employeeSummary as $row)
                <tr>
                    <td>{{ $row['employee']->user->name }}</td>
                    <td>{{ $row['employee']->employee_code }}</td>
                    <td>{{ $row['present'] }}</td>
                    <td>{{ $row['absent'] }}</td>
                    <td>{{ $row['half_day'] }}</td>
                    <td>{{ $row['wfh'] }}</td>
                    <td>{{ $row['total'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Department-wise Summary</h2>
    <table>
        <thead>
            <tr>
                <th>Department</th>
                <th>Present</th>
                <th>Absent</th>
                <th>Half Day</th>
                <th>WFH</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($departmentSummary as $row)
                <tr>
                    <td>{{ $row['department']->name ?? '-' }}</td>
                    <td>{{ $row['present'] }}</td>
                    <td>{{ $row['absent'] }}</td>
                    <td>{{ $row['half_day'] }}</td>
                    <td>{{ $row['wfh'] }}</td>
                    <td>{{ $row['total'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Detailed Records</h2>
    <table>
        <thead>
            <tr>
                <th>Employee</th>
                <th>Department</th>
                <th>Date</th>
                <th>Status</th>
                <th>Check In</th>
                <th>Check Out</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendances as $attendance)
                <tr>
                    <td>{{ $attendance->employee->user->name }}</td>
                    <td>{{ $attendance->employee->department->name ?? '-' }}</td>
                    <td>{{ $attendance->date->format('Y-m-d') }}</td>
                    <td>{{ ucwords(str_replace('_', ' ', $attendance->status)) }}</td>
                    <td>{{ $attendance->check_in ?? '-' }}</td>
                    <td>{{ $attendance->check_out ?? '-' }}</td>
                    <td>{{ $attendance->remarks ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
