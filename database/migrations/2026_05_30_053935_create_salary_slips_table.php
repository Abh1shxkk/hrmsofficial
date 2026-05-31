<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('salary_slips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('month');
            $table->integer('year');
            $table->decimal('basic', 10, 2);
            $table->decimal('hra', 10, 2);
            $table->decimal('gross_salary', 10, 2);
            $table->decimal('pf_employee', 10, 2);
            $table->decimal('pf_employer', 10, 2);
            $table->decimal('esi_employee', 10, 2)->default(0);
            $table->decimal('esi_employer', 10, 2)->default(0);
            $table->decimal('tds', 10, 2)->default(0);
            $table->decimal('total_deductions', 10, 2);
            $table->decimal('net_salary', 10, 2);
            $table->tinyInteger('working_days');
            $table->tinyInteger('present_days');
            $table->enum('status', ['draft', 'processed', 'paid'])->default('draft');
            $table->foreignId('generated_by')->constrained('users');
            $table->timestamps();

            $table->unique(['employee_id', 'month', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_slips');
    }
};
