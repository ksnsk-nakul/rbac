<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table): void {
            if (! Schema::hasColumn('roles', 'is_protected')) {
                $table->boolean('is_protected')->default(false)->after('is_default');
                $table->index('is_protected');
            }
        });

        Schema::table('permissions', function (Blueprint $table): void {
            if (! Schema::hasColumn('permissions', 'is_protected')) {
                $table->boolean('is_protected')->default(false)->after('slug');
                $table->index('is_protected');
            }
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table): void {
            if (Schema::hasColumn('roles', 'is_protected')) {
                $table->dropIndex(['is_protected']);
                $table->dropColumn('is_protected');
            }
        });

        Schema::table('permissions', function (Blueprint $table): void {
            if (Schema::hasColumn('permissions', 'is_protected')) {
                $table->dropIndex(['is_protected']);
                $table->dropColumn('is_protected');
            }
        });
    }
};

