@extends('layouts.app')
@section('title', 'Salary Slip')

@php
    $transport = $salarySlip->transport_allowance ?? 0;
    $other = $salarySlip->other_allowances ?? 0;

    $statusClasses = [
        'draft' => 'bg-gray-100 text-gray-700',
        'processed' => 'bg-blue-100 text-blue-700',
        'paid' => 'bg-green-100 text-green-700',
    ];
@endphp

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex justify-between items-start">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Salary Slip</h2>
                <p class="text-gray-500 mt-1">
                    {{ DateTime::createFromFormat('!m', $salarySlip->month)->format('F') }} {{ $salarySlip->year }}
                </p>
                <span class="inline-block mt-2 px-2.5 py-1 text-xs font-medium rounded-full {{ $statusClasses[$salarySlip->status] ?? '' }}">
                    {{ ucfirst($salarySlip->status) }}
                </span>
            </div>
            <a href="{{ route('salary-slip.download', $salarySlip) }}"
               class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700 flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Download PDF</span>
            </a>
        </div>
    </div>

    {{-- Employee Info --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-800 mb-4 text-sm uppercase tracking-wide">Employee Details</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
            <div>
                <p class="text-gray-400">Name</p>
                <p class="font-medium text-gray-800">{{ $salarySlip->employee->user->name }}</p>
            </div>
            <div>
                <p class="text-gray-400">Employee Code</p>
                <p class="font-medium text-gray-800 font-mono">{{ $salarySlip->employee->employee_code }}</p>
            </div>
            <div>
                <p class="text-gray-400">Department</p>
                <p class="font-medium text-gray-800">{{ $salarySlip->employee->department->name }}</p>
            </div>
            <div>
                <p class="text-gray-400">Designation</p>
                <p class="font-medium text-gray-800">{{ $salarySlip->employee->designation }}</p>
            </div>
            <div>
                <p class="text-gray-400">Working Days</p>
                <p class="font-medium text-gray-800">{{ $salarySlip->working_days }}</p>
            </div>
            <div>
                <p class="text-gray-400">Present Days</p>
                <p class="font-medium text-gray-800">{{ $salarySlip->present_days }}</p>
            </div>
        </div>
    </div>

    {{-- Earnings & Deductions --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Earnings --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-green-700 mb-4 text-sm uppercase tracking-wide border-b border-green-100 pb-2">
                Earnings
            </h3>
            <table class="w-full text-sm">
                <tbody>
                    <tr class="border-b border-gray-100">
                        <td class="py-2.5 text-gray-600">Basic</td>
                        <td class="py-2.5 text-right font-medium">{{ number_format($salarySlip->basic, 2) }}</td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-2.5 text-gray-600">HRA</td>
                        <td class="py-2.5 text-right font-medium">{{ number_format($salarySlip->hra, 2) }}</td>
                    </tr>
                    @if($transport > 0)
                    <tr class="border-b border-gray-100">
                        <td class="py-2.5 text-gray-600">Transport Allowance</td>
                        <td class="py-2.5 text-right font-medium">{{ number_format($transport, 2) }}</td>
                    </tr>
                    @endif
                    @if($other > 0)
                    <tr class="border-b border-gray-100">
                        <td class="py-2.5 text-gray-600">Other Allowances</td>
                        <td class="py-2.5 text-right font-medium">{{ number_format($other, 2) }}</td>
                    </tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-green-200">
                        <td class="py-3 font-bold text-green-700">Gross Salary</td>
                        <td class="py-3 text-right font-bold text-green-700">{{ number_format($salarySlip->gross_salary, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Deductions --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-red-700 mb-4 text-sm uppercase tracking-wide border-b border-red-100 pb-2">
                Deductions
            </h3>
            <table class="w-full text-sm">
                <tbody>
                    <tr class="border-b border-gray-100">
                        <td class="py-2.5 text-gray-600">PF (Employee 12%)</td>
                        <td class="py-2.5 text-right font-medium">{{ number_format($salarySlip->pf_employee, 2) }}</td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-2.5 text-gray-600">ESI (Employee 0.75%)</td>
                        <td class="py-2.5 text-right font-medium">{{ number_format($salarySlip->esi_employee, 2) }}</td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-2.5 text-gray-600">TDS</td>
                        <td class="py-2.5 text-right font-medium">{{ number_format($salarySlip->tds, 2) }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-red-200">
                        <td class="py-3 font-bold text-red-700">Total Deductions</td>
                        <td class="py-3 text-right font-bold text-red-700">{{ number_format($salarySlip->total_deductions, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Employer Contributions (info only) --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-600 mb-3 text-sm uppercase tracking-wide">Employer Contributions (not deducted from salary)</h3>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-gray-400">PF (Employer 12%)</p>
                <p class="font-medium">{{ number_format($salarySlip->pf_employer, 2) }}</p>
            </div>
            <div>
                <p class="text-gray-400">ESI (Employer 3.25%)</p>
                <p class="font-medium">{{ number_format($salarySlip->esi_employer, 2) }}</p>
            </div>
        </div>
    </div>

    {{-- Net Pay --}}
    <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl shadow-sm p-8 text-center border border-green-100">
        <p class="text-sm text-gray-500 uppercase tracking-wide mb-1">Net Pay</p>
        <p class="text-4xl font-bold text-green-700">{{ number_format($salarySlip->net_salary, 2) }}</p>
        <p class="text-xs text-gray-400 mt-2">
            {{ DateTime::createFromFormat('!m', $salarySlip->month)->format('F') }} {{ $salarySlip->year }}
        </p>
    </div>
</div>
@endsection
