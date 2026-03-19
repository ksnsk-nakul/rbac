<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->string('currency')->default('INR')->after('slug');
            $table->integer('price_monthly')->nullable()->after('currency'); // minor units (paise)
            $table->integer('price_yearly')->nullable()->after('price_monthly'); // minor units (paise)
            $table->string('razorpay_plan_id_monthly')->nullable()->after('price_yearly');
            $table->string('razorpay_plan_id_yearly')->nullable()->after('razorpay_plan_id_monthly');
            $table->boolean('is_active')->default(true)->after('razorpay_plan_id_yearly');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'currency',
                'price_monthly',
                'price_yearly',
                'razorpay_plan_id_monthly',
                'razorpay_plan_id_yearly',
                'is_active',
            ]);
        });
    }
};
