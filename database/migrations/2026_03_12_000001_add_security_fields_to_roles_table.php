<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('mfa_required')->default(false)->after('is_default');
            $table->boolean('require_ip_allowlist')->default(false)->after('mfa_required');
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['mfa_required', 'require_ip_allowlist']);
        });
    }
};
