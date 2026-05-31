@php
    $canManageDocuments = auth()->user()->hasAnyRole(['super_admin', 'hr_admin']);
@endphp

<div class="bg-white rounded-xl shadow-sm p-6">
    <div class="mb-4 flex items-center justify-between">
        <div>
            <h3 class="font-semibold text-gray-800 text-sm uppercase tracking-wide">Documents</h3>
            <p class="mt-1 text-xs text-gray-500">Employee verification and HR records.</p>
        </div>
        <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600">{{ $employee->documents->count() }} files</span>
    </div>

    @if($canManageDocuments)
        <form method="POST" action="{{ route('employees.documents.store', $employee) }}" enctype="multipart/form-data" class="mb-5 grid grid-cols-1 gap-3 rounded-lg border border-gray-200 bg-gray-50 p-4 md:grid-cols-4 md:items-end">
            @csrf
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-500">Type</label>
                <select name="type" required class="form-control">
                    @foreach(\App\Models\EmployeeDocument::TYPES as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-500">Title</label>
                <input type="text" name="title" placeholder="Optional title" class="form-control">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-500">File</label>
                <input type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" class="form-file-control">
            </div>
            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Upload</button>
        </form>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Document</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Size</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($employee->documents as $document)
                    <tr>
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-800">{{ $document->title }}</p>
                            <p class="text-xs text-gray-400">{{ $document->original_name }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $document->typeLabel() }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ number_format($document->size / 1024, 1) }} KB</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('employees.documents.download', [$employee, $document]) }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">Download</a>
                                @if($canManageDocuments)
                                    <form method="POST" action="{{ route('employees.documents.destroy', [$employee, $document]) }}" onsubmit="return confirm('Delete this document?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-800">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">No documents uploaded.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
