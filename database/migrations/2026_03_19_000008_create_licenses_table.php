<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('customer_email')->nullable();
            $table->string('status')->default('active'); // active, suspended, revoked
            $table->timestamp('valid_until')->nullable();
            $table->integer('max_instances')->default(1);
            $table->timestamps();
        });

        Schema::create('license_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')->constrained('licenses')->cascadeOnDelete();
            $table->string('instance_id')->unique();
            $table->string('app_url')->nullable();
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_instances');
        Schema::dropIfExists('licenses');
    }
};
