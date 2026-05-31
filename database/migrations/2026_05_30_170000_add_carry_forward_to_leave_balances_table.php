<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_balances', function (Blueprint $table) {
            if (! Schema::hasColumn('leave_balances', 'carried_forward')) {
                $table->decimal('carried_forward', 5, 2)->default(0)->after('allocated');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leave_balances', function (Blueprint $table) {
            if (Schema::hasColumn('leave_balances', 'carried_forward')) {
                $table->dropColumn('carried_forward');
            }
        });
    }
};
