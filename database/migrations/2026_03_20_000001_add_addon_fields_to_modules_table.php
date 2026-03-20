<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->json('allowed_plans')->nullable()->after('description');
            $table->boolean('requires_api_key')->default(false)->after('allowed_plans');
            $table->string('api_key_hash')->nullable()->after('requires_api_key');
        });
    }

    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn(['allowed_plans', 'requires_api_key', 'api_key_hash']);
        });
    }
};

