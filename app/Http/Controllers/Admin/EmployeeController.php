<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\User;
use App\Services\LeaveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with(['user', 'department'])->withCount('documents');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('employee_code', 'like', "%{$search}%")
                  ->orWhere('designation', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            });
        }
        if ($dept = $request->get('department')) {
            $query->where('department_id', $dept);
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($type = $request->get('employment_type')) {
            $query->where('employment_type', $type);
        }

        $employees = $query->latest()->paginate(15)->withQueryString();
        $departments = Department::orderBy('name')->get();

        return view('employees.index', compact('employees', 'departments'));
    }

    public function create(Request $request)
    {
        $departments = Department::all();
        $linkedUser = null;

        if ($request->filled('user')) {
            $linkedUser = User::with('employee')->find($request->get('user'));

            if (! $linkedUser) {
                return redirect()->route('users.index')->with('error', 'Selected user account was not found.');
            }

            if ($linkedUser->employee) {
                return redirect()
                    ->route('employees.show', $linkedUser->employee)
                    ->with('error', 'This user already has an employee profile.');
            }
        }

        return view('employees.create', compact('departments', 'linkedUser'));
    }

    public function store(StoreEmployeeRequest $request)
    {

        DB::transaction(function () use ($request) {
            if ($request->filled('existing_user_id')) {
                $user = User::whereDoesntHave('employee')->findOrFail($request->existing_user_id);
            } else {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => bcrypt($request->password),
                    'role' => $request->role,
                    'is_active' => true,
                ]);
            }

            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('employees/photos', 'public');
            }

            $employee = Employee::create([
                'user_id' => $user->id,
                'department_id' => $request->department_id,
                'employee_code' => $request->employee_code,
                'designation' => $request->designation,
                'date_of_birth' => $request->date_of_birth,
                'date_of_joining' => $request->date_of_joining,
                'phone' => $request->phone,
                'address' => $request->address,
                'photo' => $photoPath,
                'aadhar_number' => $request->aadhar_number,
                'pan_number' => $request->pan_number,
                'employment_type' => $request->employment_type,
            ]);

            $this->storeDocuments($request, $employee);
            app(LeaveService::class)->ensureBalancesForEmployee($employee, now()->year);
        });

        return redirect()->route('employees.index')->with('success', 'Employee created successfully.');
    }

    public function show(Employee $employee)
    {
        app(LeaveService::class)->ensureBalancesForEmployee($employee, now()->year);
        $employee->load([
            'user',
            'department',
            'documents',
            'salaryStructures',
            'leaveBalances.leaveType',
            'leaveApplications.leaveType',
        ]);
        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $departments = Department::all();
        $employee->load(['user', 'documents']);
        return view('employees.edit', compact('employee', 'departments'));
    }

    public function update(StoreEmployeeRequest $request, Employee $employee)
    {

        DB::transaction(function () use ($request, $employee) {
            $employee->user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            $data = $request->only([
                'department_id', 'designation', 'date_of_birth', 'date_of_joining',
                'phone', 'address', 'aadhar_number', 'pan_number', 'employment_type', 'status',
            ]);

            if ($request->hasFile('photo')) {
                if ($employee->photo) {
                    Storage::disk('public')->delete($employee->photo);
                }
                $data['photo'] = $request->file('photo')->store('employees/photos', 'public');
            }

            $employee->update($data);

            // Sync user login status with employee status
            $isActive = $request->status === 'active';
            if ($employee->user->is_active !== $isActive) {
                $employee->user->update(['is_active' => $isActive]);
            }

            $this->storeDocuments($request, $employee);
        });

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        if (auth()->id() === $employee->user_id) {
            return back()->with('error', 'You cannot delete your own employee profile.');
        }

        if (Department::where('manager_id', $employee->id)->exists()) {
            return back()->with('error', 'This employee is a department manager. Reassign the department manager before deleting.');
        }

        DB::transaction(function () use ($employee) {
            // Soft delete: deactivate login and mark as terminated
            $employee->user->update(['is_active' => false]);
            $employee->update(['status' => 'terminated']);
            $employee->delete();       // soft delete employee
            $employee->user->delete();  // soft delete user
        });

        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully.');
    }

    public function activate(Employee $employee)
    {
        $employee->user->update(['is_active' => true]);
        $employee->update(['status' => 'active']);

        return back()->with('success', 'Employee account activated.');
    }

    public function deactivate(Employee $employee)
    {
        if (auth()->id() === $employee->user_id) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $employee->user->update(['is_active' => false]);
        $employee->update(['status' => 'inactive']);

        return back()->with('success', 'Employee account deactivated.');
    }

    public function storeDocument(Request $request, Employee $employee)
    {
        $request->validate([
            'type' => ['required', Rule::in(array_keys(EmployeeDocument::TYPES))],
            'title' => 'nullable|string|max:255',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
        ]);

        $this->storeDocumentFile($employee, $request->file('file'), $request->type, $request->title);

        return back()->with('success', 'Document uploaded successfully.');
    }

    public function destroyDocument(Employee $employee, EmployeeDocument $document)
    {
        abort_unless($document->employee_id === $employee->id, 404);

        Storage::disk('local')->delete($document->path);
        $document->delete();

        return back()->with('success', 'Document deleted successfully.');
    }

    public function downloadDocument(Employee $employee, EmployeeDocument $document)
    {
        abort_unless($document->employee_id === $employee->id, 404);

        $path = Storage::disk('local')->path($document->path);
        abort_unless(file_exists($path), 404);

        return response()->download($path, $document->original_name, [
            'Content-Type' => $document->mime_type,
        ]);
    }

    private function storeDocuments(Request $request, Employee $employee): void
    {
        foreach ($request->file('documents', []) as $index => $document) {
            if (! isset($document['file'])) {
                continue;
            }

            $meta = $request->input("documents.{$index}", []);
            $this->storeDocumentFile(
                $employee,
                $document['file'],
                $meta['type'] ?? 'other',
                $meta['title'] ?? null
            );
        }
    }

    private function storeDocumentFile(Employee $employee, $file, string $type, ?string $title = null): void
    {
        $path = $file->store("employees/{$employee->id}/documents", 'local');

        $employee->documents()->create([
            'type' => $type,
            'title' => $title ?: (EmployeeDocument::TYPES[$type] ?? 'Document'),
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize() ?: 0,
        ]);
    }
}
