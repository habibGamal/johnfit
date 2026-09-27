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
        // Drop legacy fitness_scores table if it exists
        Schema::dropIfExists('fitness_scores');

        // Create daily progress points table
        Schema::create('user_daily_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date')->index();
            $table->unsignedInteger('workout_points')->default(0);
            $table->unsignedInteger('meal_points')->default(0);
            $table->unsignedInteger('hydration_points')->default(0);
            $table->unsignedInteger('total_points')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'date']);
            $table->index(['user_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_daily_points');
    }
};
