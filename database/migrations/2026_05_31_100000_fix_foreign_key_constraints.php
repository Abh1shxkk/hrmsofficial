<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // attendances.marked_by — set null when user is deleted
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['marked_by']);
            $table->unsignedBigInteger('marked_by')->nullable()->change();
            $table->foreign('marked_by')->references('id')->on('users')->nullOnDelete();
        });

        // leave_applications.approved_by — already nullable, add nullOnDelete
        Schema::table('leave_applications', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });

        // salary_slips.generated_by — set null when user is deleted
        Schema::table('salary_slips', function (Blueprint $table) {
            $table->dropForeign(['generated_by']);
            $table->unsignedBigInteger('generated_by')->nullable()->change();
            $table->foreign('generated_by')->references('id')->on('users')->nullOnDelete();
        });

        // tasks.assigned_by — set null when user is deleted
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['assigned_by']);
            $table->unsignedBigInteger('assigned_by')->nullable()->change();
            $table->foreign('assigned_by')->references('id')->on('users')->nullOnDelete();
        });

        // tasks.assigned_to — cascade delete when employee is deleted
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->foreign('assigned_to')->references('id')->on('employees')->cascadeOnDelete();
        });

        // departments.manager_id — add proper FK with nullOnDelete
        Schema::table('departments', function (Blueprint $table) {
            $table->foreign('manager_id')->references('id')->on('employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['marked_by']);
            $table->unsignedBigInteger('marked_by')->nullable(false)->change();
            $table->foreign('marked_by')->references('id')->on('users');
        });

        Schema::table('leave_applications', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->foreign('approved_by')->references('id')->on('users');
        });

        Schema::table('salary_slips', function (Blueprint $table) {
            $table->dropForeign(['generated_by']);
            $table->unsignedBigInteger('generated_by')->nullable(false)->change();
            $table->foreign('generated_by')->references('id')->on('users');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['assigned_by']);
            $table->unsignedBigInteger('assigned_by')->nullable(false)->change();
            $table->foreign('assigned_by')->references('id')->on('users');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->foreign('assigned_to')->references('id')->on('employees');
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
        });
    }
};
