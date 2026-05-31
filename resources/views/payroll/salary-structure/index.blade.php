@extends('layouts.app')
@section('title', 'Salary Management')

@section('content')
<div class="space-y-4">
    {{-- Search --}}
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" action="{{ route('salary-structures.index') }}" class="flex items-end space-x-3">
            <div class="flex-1 max-w-sm">
                <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Employee name or code..."
                       class="form-control">
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">Search</button>
            @if(request('search'))
                <a href="{{ route('salary-structures.index') }}" class="border border-gray-300 text-gray-600 px-3 py-2 rounded-lg text-sm hover:bg-gray-50">Clear</a>
            @endif
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-6 border-b flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Salary Management <span class="text-sm font-normal text-gray-400">({{ $structures->total() }})</span></h2>
            <a href="{{ route('salary-structures.create') }}"
               class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">
                Assign Salary
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left font-medium text-gray-500">Employee</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500">Code</th>
                        <th class="px-6 py-3 text-right font-medium text-gray-500">Basic</th>
                        <th class="px-6 py-3 text-right font-medium text-gray-500">HRA</th>
                        <th class="px-6 py-3 text-right font-medium text-gray-500">Transport</th>
                        <th class="px-6 py-3 text-right font-medium text-gray-500">Other</th>
                        <th class="px-6 py-3 text-right font-medium text-gray-500">Gross</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500">Effective</th>
                        <th class="px-6 py-3 text-center font-medium text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($structures as $s)
                        @continue(! $s->employee || ! $s->employee->user)
                        @php $gross = $s->basic + $s->hra + ($s->transport_allowance ?? 0) + ($s->other_allowances ?? 0); @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    @if($s->employee->photo)
                                        <img src="{{ Storage::url($s->employee->photo) }}" class="w-8 h-8 rounded-full mr-3 object-cover">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center mr-3 text-xs font-bold">
                                            {{ strtoupper(substr($s->employee->user->name, 0, 2)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-medium text-gray-800">{{ $s->employee->user->name }}</p>
                                        <p class="text-xs text-gray-400">{{ $s->employee->department->name ?? '' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-mono text-gray-600">{{ $s->employee->employee_code }}</td>
                            <td class="px-6 py-4 text-right">{{ number_format($s->basic, 2) }}</td>
                            <td class="px-6 py-4 text-right">{{ number_format($s->hra, 2) }}</td>
                            <td class="px-6 py-4 text-right">{{ number_format($s->transport_allowance ?? 0, 2) }}</td>
                            <td class="px-6 py-4 text-right">{{ number_format($s->other_allowances ?? 0, 2) }}</td>
                            <td class="px-6 py-4 text-right font-bold text-green-700">{{ number_format($gross, 2) }}</td>
                            <td class="px-6 py-4">{{ $s->effective_from->format('d M Y') }}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center space-x-3">
                                    <a href="{{ route('salary-structures.edit', $s) }}"
                                       class="text-yellow-600 hover:text-yellow-800 text-sm">Edit</a>
                                    <form method="POST" action="{{ route('salary-structures.destroy', $s) }}" class="inline"
                                          onsubmit="return confirm('Delete salary structure for {{ $s->employee->user->name }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                                No salary records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($structures->hasPages())
            <div class="p-4 border-t">{{ $structures->links() }}</div>
        @endif
    </div>
</div>
@endsection
