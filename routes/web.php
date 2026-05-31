<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SmtpSettingController;
use App\Http\Controllers\HR\AttendanceController;
use App\Http\Controllers\HR\LeaveController;
use App\Http\Controllers\HR\PayrollController;
use App\Http\Controllers\Employee\LeaveApplicationController;
use App\Http\Controllers\Employee\ProfileController;
use App\Http\Controllers\Manager\TaskController;
use App\Http\Controllers\Admin\HolidayController;
use App\Http\Controllers\HR\SalaryStructureController;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/', fn() => redirect()->route('login'));
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::get('forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email')->middleware('throttle:3,1');
    Route::get('reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('password.update')->middleware('throttle:3,1');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Leave application — all roles
    Route::get('leaves/apply', [LeaveApplicationController::class, 'create'])->name('leaves.apply');
    Route::post('leaves/apply', [LeaveApplicationController::class, 'store'])->name('leaves.store');
    Route::get('leaves/balance', [LeaveController::class, 'balance'])->name('leaves.balance');
    Route::get('leaves/my', [LeaveApplicationController::class, 'index'])->name('leaves.my');

    // Attendance — all roles
    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('attendance/mark', [AttendanceController::class, 'mark'])->name('attendance.mark');
    Route::get('attendance/report', [AttendanceController::class, 'report'])->name('attendance.report');

    // Payroll history and salary slips
    Route::get('payroll', [PayrollController::class, 'index'])->name('payroll.index');
    Route::get('salary-slip/{salarySlip}', [PayrollController::class, 'show'])->name('salary-slip.show');
    Route::get('salary-slip/{salarySlip}/download', [PayrollController::class, 'download'])->name('salary-slip.download');

    // Tasks — all can view and update own status
    Route::get('tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');

    // Holidays — all can view
    Route::get('holidays', [HolidayController::class, 'index'])->name('holidays.index');

    // HR & Super Admin routes
    Route::middleware(['role:hr_admin|super_admin'])->group(function () {
        // Employees
        Route::middleware('permission:Employees,edit')->group(function () {
            Route::get('employees/create', [EmployeeController::class, 'create'])->name('employees.create');
            Route::post('employees', [EmployeeController::class, 'store'])->name('employees.store');
        });
        Route::middleware('permission:Employees,view')->group(function () {
            Route::get('employees', [EmployeeController::class, 'index'])->name('employees.index');
            Route::get('employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
            Route::get('employees/{employee}/documents/{document}/download', [EmployeeController::class, 'downloadDocument'])->name('employees.documents.download');
        });
        Route::middleware('permission:Employees,edit')->group(function () {
            Route::get('employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
            Route::put('employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
            Route::post('employees/{employee}/documents', [EmployeeController::class, 'storeDocument'])->name('employees.documents.store');
            Route::delete('employees/{employee}/documents/{document}', [EmployeeController::class, 'destroyDocument'])->name('employees.documents.destroy');
            Route::patch('employees/{employee}/activate', [EmployeeController::class, 'activate'])->name('employees.activate');
            Route::patch('employees/{employee}/deactivate', [EmployeeController::class, 'deactivate'])->name('employees.deactivate');
        });
        Route::delete('employees/{employee}', [EmployeeController::class, 'destroy'])
            ->name('employees.destroy')
            ->middleware(['role:super_admin', 'permission:Employees,delete']);

        // Departments
        Route::middleware('permission:Departments,edit')->group(function () {
            Route::get('departments/create', [DepartmentController::class, 'create'])->name('departments.create');
            Route::post('departments', [DepartmentController::class, 'store'])->name('departments.store');
        });
        Route::middleware('permission:Departments,view')->group(function () {
            Route::get('departments', [DepartmentController::class, 'index'])->name('departments.index');
            Route::get('departments/{department}', [DepartmentController::class, 'show'])->name('departments.show');
        });
        Route::middleware('permission:Departments,edit')->group(function () {
            Route::get('departments/{department}/edit', [DepartmentController::class, 'edit'])->name('departments.edit');
            Route::put('departments/{department}', [DepartmentController::class, 'update'])->name('departments.update');
        });
        Route::delete('departments/{department}', [DepartmentController::class, 'destroy'])
            ->name('departments.destroy')
            ->middleware('permission:Departments,delete');

        // Salary Structures
        Route::middleware('permission:Payroll,edit')->group(function () {
            Route::resource('salary-structures', SalaryStructureController::class)->except(['show']);
        });

        // Payroll
        Route::middleware('permission:Payroll,manage')->group(function () {
            Route::get('payroll/process', [PayrollController::class, 'process'])->name('payroll.process');
            Route::post('payroll/generate', [PayrollController::class, 'generate'])->name('payroll.generate');
            Route::patch('salary-slip/{salarySlip}/mark-paid', [PayrollController::class, 'markAsPaid'])->name('salary-slip.mark-paid');
            Route::delete('salary-slip/{salarySlip}', [PayrollController::class, 'destroy'])->name('salary-slip.destroy');
        });

        // Attendance export
        Route::get('attendance/export', [AttendanceController::class, 'export'])
            ->name('attendance.export')
            ->middleware('permission:Attendance,manage');

        // Holidays manage
        Route::middleware('permission:Holidays,manage')->group(function () {
            Route::post('holidays', [HolidayController::class, 'store'])->name('holidays.store');
            Route::put('holidays/{holiday}', [HolidayController::class, 'update'])->name('holidays.update');
            Route::delete('holidays/{holiday}', [HolidayController::class, 'destroy'])->name('holidays.destroy');
        });
    });

    // Manager routes
    Route::middleware('role:manager|hr_admin|super_admin')->group(function () {
        Route::middleware('permission:Leaves,edit')->group(function () {
            Route::get('leaves/approvals', [LeaveController::class, 'approvals'])->name('leaves.approvals');
            Route::post('leaves/{leaveApplication}/approve', [LeaveController::class, 'approve'])->name('leaves.approve');
            Route::post('leaves/{leaveApplication}/reject', [LeaveController::class, 'reject'])->name('leaves.reject');
        });
        Route::middleware('permission:Tasks,edit')->group(function () {
            Route::post('tasks', [TaskController::class, 'store'])->name('tasks.store');
            Route::get('tasks/create', [TaskController::class, 'create'])->name('tasks.create');
            Route::delete('tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
        });
    });

    // Super Admin only
    Route::middleware('role:super_admin')->group(function () {
        Route::get('users/permissions', [UserController::class, 'permissions'])->name('users.permissions');
        Route::put('users/permissions', [UserController::class, 'updatePermissions'])->name('users.permissions.update');
        Route::get('settings/smtp', [SmtpSettingController::class, 'edit'])->name('settings.smtp.edit');
        Route::put('settings/smtp', [SmtpSettingController::class, 'update'])->name('settings.smtp.update');
        Route::post('settings/smtp/test', [SmtpSettingController::class, 'sendTest'])->name('settings.smtp.test');
        Route::resource('users', UserController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
        Route::patch('users/{user}/activate', [UserController::class, 'activate'])->name('users.activate');
        Route::patch('users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
        Route::patch('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    });
});
