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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->string('employee_code', 20)->unique();
            $table->string('designation', 100);
            $table->date('date_of_birth');
            $table->date('date_of_joining');
            $table->string('phone', 15);
            $table->text('address');
            $table->string('photo')->nullable();
            $table->string('aadhar_number', 20)->nullable();
            $table->string('pan_number', 20)->nullable();
            $table->enum('employment_type', ['full_time', 'part_time', 'contract'])->default('full_time');
            $table->enum('status', ['active', 'inactive', 'terminated'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
