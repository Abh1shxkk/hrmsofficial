<?php

namespace App\Http\Middleware;

use App\Models\RolePermission;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Usage: middleware('permission:Employees,view')
     *        middleware('permission:Payroll,manage')
     *
     * Actions: view, edit, delete, manage
     */
    public function handle(Request $request, Closure $next, string $module, string $action = 'view'): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        // Super admin always has full access
        if ($user->role === 'super_admin') {
            return $next($request);
        }

        $column = match ($action) {
            'view' => 'can_view',
            'edit' => 'can_edit',
            'delete' => 'can_delete',
            'manage' => 'can_manage',
            default => 'can_view',
        };

        $cacheKey = "role_permissions.{$user->role}.{$module}";

        $permission = Cache::remember($cacheKey, 300, function () use ($user, $module) {
            return RolePermission::where('role', $user->role)
                ->where('module', $module)
                ->first();
        });

        if (! $permission || ! $permission->{$column}) {
            abort(403, 'You do not have permission to perform this action.');
        }

        return $next($request);
    }
}
