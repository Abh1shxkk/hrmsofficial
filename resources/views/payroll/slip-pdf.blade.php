<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Salary Slip — {{ $slip->employee->employee_code }} — {{ $slip->month }}/{{ $slip->year }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #333; padding: 30px; }

        .header { text-align: center; border-bottom: 3px solid #1a56db; padding-bottom: 15px; margin-bottom: 25px; }
        .header h1 { font-size: 24px; color: #1a56db; margin-bottom: 3px; }
        .header .subtitle { font-size: 11px; color: #666; }
        .header .slip-title { font-size: 16px; font-weight: bold; margin-top: 10px; color: #333; }

        .info-grid { width: 100%; margin-bottom: 25px; border: 1px solid #e5e7eb; border-radius: 4px; }
        .info-grid td { padding: 8px 12px; font-size: 11px; }
        .info-grid .label { color: #6b7280; font-weight: bold; width: 25%; background: #f9fafb; }
        .info-grid .value { width: 25%; }

        .section-header { font-size: 13px; font-weight: bold; padding: 8px 10px; margin-bottom: 0; border-radius: 4px 4px 0 0; }
        .section-header.earnings { background: #dcfce7; color: #166534; }
        .section-header.deductions { background: #fee2e2; color: #991b1b; }

        table.breakdown { width: 100%; border-collapse: collapse; border: 1px solid #e5e7eb; border-top: none; }
        table.breakdown td { padding: 7px 12px; border-bottom: 1px solid #f3f4f6; }
        table.breakdown td:last-child { text-align: right; font-family: 'Courier New', monospace; }
        table.breakdown tr.total { background: #f9fafb; }
        table.breakdown tr.total td { border-top: 2px solid #d1d5db; font-weight: bold; padding: 10px 12px; }

        .columns { width: 100%; }
        .columns td { vertical-align: top; }
        .columns td.gap { width: 3%; }
        .columns td.col { width: 48.5%; }

        .net-pay-box { text-align: center; margin-top: 25px; padding: 20px; background: #f0fdf4; border: 2px solid #86efac; border-radius: 6px; }
        .net-pay-box .label { font-size: 11px; color: #6b7280; text-transform: uppercase; letter-spacing: 1px; }
        .net-pay-box .amount { font-size: 28px; font-weight: bold; color: #166534; margin-top: 5px; }

        .employer-section { margin-top: 20px; padding: 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 4px; font-size: 10px; color: #6b7280; }
        .employer-section strong { color: #374151; }

        .footer { margin-top: 30px; padding-top: 15px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 9px; color: #9ca3af; }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <h1>HRMS Company</h1>
        <p class="subtitle">123 Business Park, Mumbai, Maharashtra — 400001</p>
        <p class="slip-title">
            Salary Slip for {{ DateTime::createFromFormat('!m', $slip->month)->format('F') }} {{ $slip->year }}
        </p>
    </div>

    {{-- Employee Info --}}
    <table class="info-grid">
        <tr>
            <td class="label">Employee Name</td>
            <td class="value">{{ $slip->employee->user->name }}</td>
            <td class="label">Employee Code</td>
            <td class="value">{{ $slip->employee->employee_code }}</td>
        </tr>
        <tr>
            <td class="label">Department</td>
            <td class="value">{{ $slip->employee->department->name }}</td>
            <td class="label">Designation</td>
            <td class="value">{{ $slip->employee->designation }}</td>
        </tr>
        <tr>
            <td class="label">Working Days</td>
            <td class="value">{{ $slip->working_days }}</td>
            <td class="label">Present Days</td>
            <td class="value">{{ $slip->present_days }}</td>
        </tr>
    </table>

    @php
        $transport = $slip->transport_allowance ?? 0;
        $other = $slip->other_allowances ?? 0;
    @endphp

    {{-- Earnings & Deductions side by side --}}
    <table class="columns">
        <tr>
            <td class="col">
                <div class="section-header earnings">Earnings</div>
                <table class="breakdown">
                    <tr>
                        <td>Basic</td>
                        <td>{{ number_format($slip->basic, 2) }}</td>
                    </tr>
                    <tr>
                        <td>HRA</td>
                        <td>{{ number_format($slip->hra, 2) }}</td>
                    </tr>
                    @if($transport > 0)
                    <tr>
                        <td>Transport Allowance</td>
                        <td>{{ number_format($transport, 2) }}</td>
                    </tr>
                    @endif
                    @if($other > 0)
                    <tr>
                        <td>Other Allowances</td>
                        <td>{{ number_format($other, 2) }}</td>
                    </tr>
                    @endif
                    <tr class="total">
                        <td>Gross Salary</td>
                        <td>{{ number_format($slip->gross_salary, 2) }}</td>
                    </tr>
                </table>
            </td>
            <td class="gap"></td>
            <td class="col">
                <div class="section-header deductions">Deductions</div>
                <table class="breakdown">
                    <tr>
                        <td>PF (Employee 12%)</td>
                        <td>{{ number_format($slip->pf_employee, 2) }}</td>
                    </tr>
                    <tr>
                        <td>ESI (Employee 0.75%)</td>
                        <td>{{ number_format($slip->esi_employee, 2) }}</td>
                    </tr>
                    <tr>
                        <td>TDS</td>
                        <td>{{ number_format($slip->tds, 2) }}</td>
                    </tr>
                    <tr class="total">
                        <td>Total Deductions</td>
                        <td>{{ number_format($slip->total_deductions, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Net Pay --}}
    <div class="net-pay-box">
        <p class="label">Net Pay</p>
        <p class="amount">Rs. {{ number_format($slip->net_salary, 2) }}</p>
    </div>

    {{-- Employer Contributions --}}
    <div class="employer-section">
        <strong>Employer Contributions (not deducted from employee):</strong>
        PF Employer: {{ number_format($slip->pf_employer, 2) }} |
        ESI Employer: {{ number_format($slip->esi_employer, 2) }}
    </div>

    {{-- Footer --}}
    <div class="footer">
        Generated on {{ now()->format('d M Y, h:i A') }} | This is a system-generated document and does not require a signature.
    </div>
</body>
</html>
