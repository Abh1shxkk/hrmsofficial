<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('smtp_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_enabled')->default(false);
            $table->string('mailer')->default('smtp');
            $table->string('host')->default('smtp.gmail.com');
            $table->unsignedInteger('port')->default(587);
            $table->string('username')->nullable();
            $table->text('password')->nullable();
            $table->string('encryption', 10)->default('tls');
            $table->string('from_address')->nullable();
            $table->string('from_name')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smtp_settings');
    }
};
