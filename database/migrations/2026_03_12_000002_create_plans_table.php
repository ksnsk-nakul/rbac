<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedInteger('audit_retention_days')->default(30);
            $table->unsignedInteger('max_admin_users')->default(1);
            $table->boolean('allow_api_tokens')->default(false);
            $table->boolean('allow_ip_allowlist')->default(false);
            $table->boolean('allow_mfa_enforcement')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
