<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('tier_id')->nullable()->after('plan_id')->constrained('subscription_plan_tiers')->nullOnDelete();
            $table->unsignedInteger('duration_months')->default(1)->after('tier_id');
            $table->unsignedInteger('duration_days')->default(30)->after('duration_months');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['tier_id']);
            $table->dropColumn(['tier_id', 'duration_months', 'duration_days']);
        });
    }
};
