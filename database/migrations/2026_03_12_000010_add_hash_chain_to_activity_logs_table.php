<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->string('event_hash', 128)->nullable()->after('user_agent');
            $table->string('previous_hash', 128)->nullable()->after('event_hash');
            $table->index('event_hash');
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['event_hash']);
            $table->dropColumn(['event_hash', 'previous_hash']);
        });
    }
};
