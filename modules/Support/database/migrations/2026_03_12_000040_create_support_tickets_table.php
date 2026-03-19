<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Deprecated: support tickets are now created by the main app migrations
        // (see `database/migrations/2026_03_19_000009_create_support_tickets_tables.php`).
        //
        // This migration is intentionally a no-op to avoid creating duplicate tables
        // when the Support module is enabled via the module registry.
        return;
    }

    public function down(): void
    {
        // No-op: do not drop consolidated tables.
        return;
    }
};
