@php
    $documentTypes = \App\Models\EmployeeDocument::TYPES;
    $rows = $rows ?? 3;
@endphp

<div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
    <div class="mb-4">
        <h3 class="text-sm font-semibold text-gray-800">Documents</h3>
        <p class="mt-1 text-xs text-gray-500">Upload Aadhar, PAN, offer letter or other employee documents. Allowed: PDF, JPG, PNG, DOC, DOCX up to 5 MB.</p>
    </div>

    <div class="space-y-3">
        @for($i = 0; $i < $rows; $i++)
            <div class="grid grid-cols-1 gap-3 rounded-md border border-gray-200 bg-white p-3 md:grid-cols-3">
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500">Document Type</label>
                    <select name="documents[{{ $i }}][type]" class="form-control">
                        @foreach($documentTypes as $value => $label)
                            <option value="{{ $value }}" {{ old("documents.{$i}.type") === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500">Title</label>
                    <input type="text" name="documents[{{ $i }}][title]" value="{{ old("documents.{$i}.title") }}" placeholder="Optional title" class="form-control">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500">File</label>
                    <input type="file" name="documents[{{ $i }}][file]" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" class="form-file-control">
                </div>
            </div>
        @endfor
    </div>
</div>
