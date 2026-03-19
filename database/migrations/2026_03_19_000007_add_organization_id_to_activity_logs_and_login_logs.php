<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->foreignId('organization_id')
                ->nullable()
                ->after('user_id')
                ->constrained('organizations')
                ->nullOnDelete();
            $table->index(['organization_id', 'created_at']);
        });

        Schema::table('login_activity_logs', function (Blueprint $table) {
            $table->foreignId('organization_id')
                ->nullable()
                ->after('user_id')
                ->constrained('organizations')
                ->nullOnDelete();
            $table->index(['organization_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['organization_id', 'created_at']);
            $table->dropConstrainedForeignId('organization_id');
        });

        Schema::table('login_activity_logs', function (Blueprint $table) {
            $table->dropIndex(['organization_id', 'created_at']);
            $table->dropConstrainedForeignId('organization_id');
        });
    }
};
