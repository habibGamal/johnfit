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
        Schema::create('user_daily_water_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('target_ml')->default(2500);
            $table->unsignedInteger('consumed_ml')->default(0);
            $table->unsignedInteger('custom_target_ml')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'date']);
            $table->index(['user_id', 'date', 'is_completed']);
        });

        Schema::create('user_water_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('water_log_id')->constrained('user_daily_water_logs')->cascadeOnDelete();
            $table->unsignedInteger('amount_ml');
            $table->string('container_type', 30)->default('cup'); // cup, bottle, shaker, custom
            $table->timestamp('logged_at');
            $table->timestamps();

            $table->index(['water_log_id', 'logged_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_water_entries');
        Schema::dropIfExists('user_daily_water_logs');
    }
};
