<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('employee')->after('password')->index();
            }
        });

        if (Schema::hasTable('roles') && Schema::hasTable('model_has_roles')) {
            DB::table('model_has_roles')
                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->where('model_has_roles.model_type', 'App\\Models\\User')
                ->orderBy('model_has_roles.model_id')
                ->select('model_has_roles.model_id', 'roles.name')
                ->each(function ($assignment) {
                    DB::table('users')
                        ->where('id', $assignment->model_id)
                        ->update(['role' => $assignment->name]);
                });
        }

        foreach (['role_has_permissions', 'model_has_roles', 'model_has_permissions', 'roles', 'permissions'] as $table) {
            Schema::dropIfExists($table);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
        });
    }
};
