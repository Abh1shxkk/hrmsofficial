<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salary_slips', function (Blueprint $table) {
            $table->decimal('present_days', 5, 1)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('salary_slips', function (Blueprint $table) {
            $table->tinyInteger('present_days')->change();
        });
    }
};
