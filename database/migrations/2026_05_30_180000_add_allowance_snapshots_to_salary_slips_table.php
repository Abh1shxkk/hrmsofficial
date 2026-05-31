<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salary_slips', function (Blueprint $table) {
            if (! Schema::hasColumn('salary_slips', 'transport_allowance')) {
                $table->decimal('transport_allowance', 10, 2)->default(0)->after('hra');
            }

            if (! Schema::hasColumn('salary_slips', 'other_allowances')) {
                $table->decimal('other_allowances', 10, 2)->default(0)->after('transport_allowance');
            }
        });
    }

    public function down(): void
    {
        Schema::table('salary_slips', function (Blueprint $table) {
            if (Schema::hasColumn('salary_slips', 'other_allowances')) {
                $table->dropColumn('other_allowances');
            }

            if (Schema::hasColumn('salary_slips', 'transport_allowance')) {
                $table->dropColumn('transport_allowance');
            }
        });
    }
};
