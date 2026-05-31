<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $role = $user->role;

        if (in_array($role, ['super_admin', 'hr_admin'])) {
            $query = Task::with(['assignedEmployee.user', 'assignedEmployee.department', 'assignedByUser']);
        } elseif ($role === 'manager') {
            $departmentId = $user->employee?->department_id;
            $query = Task::with(['assignedEmployee.user', 'assignedEmployee.department', 'assignedByUser'])
                ->where(function ($query) use ($user, $departmentId) {
                    $query->where('assigned_by', $user->id);

                    if ($departmentId) {
                        $query->orWhereHas('assignedEmployee', function ($employeeQuery) use ($departmentId) {
                            $employeeQuery->where('department_id', $departmentId);
                        });
                    }
                });
        } else {
            $query = Task::with(['assignedEmployee.user', 'assignedEmployee.department', 'assignedByUser'])
                ->where('assigned_to', $user->employee?->id);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($priority = $request->get('priority')) {
            $query->where('priority', $priority);
        }
        if ($search = $request->get('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        $tasks = $query->latest()->paginate(15)->withQueryString();
        $tasks->getCollection()->each(function (Task $task) {
            $task->can_update_status = $this->canUpdateStatus($task);
            $task->can_delete = $this->canManageTask($task);
        });

        return view('tasks.index', compact('tasks'));
    }

    public function create()
    {
        $employees = $this->assignableEmployees()->with(['user', 'department'])->orderBy('employee_code')->get();
        return view('tasks.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:employees,id',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'required|date|after_or_equal:today',
        ]);

        $assignee = Employee::with('department')->findOrFail($request->assigned_to);
        $this->authorizeAssignment($assignee);

        Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'assigned_to' => $request->assigned_to,
            'assigned_by' => auth()->id(),
            'priority' => $request->priority,
            'due_date' => $request->due_date,
        ]);

        return redirect()->route('tasks.index')->with('success', 'Task assigned.');
    }

    public function destroy(Task $task)
    {
        abort_unless($this->canManageTask($task), 403);

        $task->delete();
        return redirect()->route('tasks.index')->with('success', 'Task deleted.');
    }

    public function updateStatus(Request $request, Task $task)
    {
        $request->validate(['status' => 'required|in:todo,in_progress,completed']);

        abort_unless($this->canUpdateStatus($task), 403);

        $task->update(['status' => $request->status]);

        return back()->with('success', 'Task status updated.');
    }

    private function assignableEmployees()
    {
        $user = auth()->user();
        $query = Employee::active();

        if ($user->hasRole('manager')) {
            return $query->where('department_id', $user->employee?->department_id);
        }

        return $query;
    }

    private function authorizeAssignment(Employee $employee): void
    {
        $user = auth()->user();

        if ($user->hasAnyRole(['super_admin', 'hr_admin'])) {
            return;
        }

        abort_unless(
            $user->hasRole('manager') &&
            $user->employee?->department_id &&
            $user->employee->department_id === $employee->department_id,
            403
        );
    }

    private function canManageTask(Task $task): bool
    {
        $user = auth()->user();

        if ($user->hasAnyRole(['super_admin', 'hr_admin'])) {
            return true;
        }

        return $user->hasRole('manager') && $task->assigned_by === $user->id;
    }

    private function canUpdateStatus(Task $task): bool
    {
        $user = auth()->user();

        if ($user->hasAnyRole(['super_admin', 'hr_admin'])) {
            return true;
        }

        if ($user->hasRole('manager')) {
            return $task->assigned_by === $user->id || $task->assigned_to === $user->employee?->id;
        }

        return $user->hasRole('employee') && $task->assigned_to === $user->employee?->id;
    }
}
