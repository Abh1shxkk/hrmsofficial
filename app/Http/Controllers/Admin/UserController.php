<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('employee.department')->latest();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('employee', fn ($employee) => $employee->where('employee_code', 'like', "%{$search}%"));
            });
        }

        if ($role = $request->get('role')) {
            $query->where('role', $role);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->boolean('status'));
        }

        $users = $query->paginate(15)->withQueryString();
        $roles = User::ROLES;
        $stats = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'blocked' => User::where('is_active', false)->count(),
            'admins' => User::whereIn('role', ['super_admin', 'hr_admin'])->count(),
        ];

        return view('users.index', compact('users', 'roles', 'stats'));
    }

    public function create()
    {
        $roles = User::ROLES;

        return view('users.create', compact('roles'));
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
            'is_active' => $validated['is_active'],
        ]);

        return redirect()->route('users.index')->with('success', 'User account created successfully.');
    }

    public function edit(User $user)
    {
        $user->load('employee.department');
        $roles = User::ROLES;
        $permissions = RolePermission::where('role', $user->role)->orderBy('module')->get();

        return view('users.edit', compact('user', 'roles', 'permissions'));
    }

    public function update(StoreUserRequest $request, User $user)
    {
        $validated = $request->validated();

        if ($user->id === auth()->id()) {
            $validated['role'] = $user->role;
            $validated['is_active'] = true;
        }

        $user->update($validated);

        if ($user->employee) {
            $user->employee->update([
                'status' => $user->is_active ? 'active' : 'inactive',
            ]);
        }

        return redirect()->route('users.index')->with('success', 'User account updated successfully.');
    }

    public function activate(User $user)
    {
        $user->update(['is_active' => true]);
        $user->employee?->update(['status' => 'active']);

        return back()->with('success', 'User account activated.');
    }

    public function deactivate(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->update(['is_active' => false]);
        $user->employee?->update(['status' => 'inactive']);

        return back()->with('success', 'User account deactivated.');
    }

    public function resetPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password reset successfully.');
    }

    public function permissions(Request $request)
    {
        $roles = User::ROLES;
        $selectedRole = $request->get('role', 'hr_admin');

        if (! in_array($selectedRole, $roles, true)) {
            $selectedRole = 'hr_admin';
        }

        $permissions = RolePermission::where('role', $selectedRole)->orderBy('module')->get();

        return view('users.permissions', compact('roles', 'selectedRole', 'permissions'));
    }

    public function updatePermissions(Request $request)
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in(User::ROLES)],
            'permissions' => 'array',
        ]);

        $permissions = $request->input('permissions', []);

        $role = $validated['role'];

        RolePermission::where('role', $role)->get()->each(function (RolePermission $permission) use ($permissions) {
            $row = $permissions[$permission->id] ?? [];

            $permission->update([
                'can_view' => array_key_exists('can_view', $row),
                'can_edit' => array_key_exists('can_edit', $row),
                'can_delete' => array_key_exists('can_delete', $row),
                'can_manage' => array_key_exists('can_manage', $row),
            ]);
        });

        // Clear cached permissions for this role
        $modules = ['User Management', 'Employees', 'Departments', 'Attendance', 'Leaves', 'Payroll', 'Tasks', 'Holidays'];
        foreach ($modules as $module) {
            Cache::forget("role_permissions.{$role}.{$module}");
        }

        return redirect()
            ->route('users.permissions', ['role' => $role])
            ->with('success', 'Role permissions updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($user->employee) {
            return back()->with('error', 'This user has an employee profile. Deactivate the account instead to preserve HR records.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User account deleted successfully.');
    }

}
