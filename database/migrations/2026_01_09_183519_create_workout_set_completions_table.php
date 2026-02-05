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
        Schema::create('workout_set_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workout_plan_id')->constrained()->cascadeOnDelete();
            $table->string('day');
            $table->unsignedBigInteger('workout_id');
            $table->unsignedInteger('set_number');
            $table->decimal('weight', 8, 2)->nullable(); // Weight in LB/KG - nullable for bodyweight exercises
            $table->unsignedInteger('reps');
            $table->boolean('completed')->default(false);
            $table->date('session_date'); // Track which date this set was completed
            $table->timestamps();

            // Unique constraint for a specific set in a workout session
            $table->unique(
                ['user_id', 'workout_plan_id', 'day', 'workout_id', 'set_number', 'session_date'],
                'workout_set_completions_unique'
            );

            // Index for quick lookups of previous sessions
            $table->index(['user_id', 'workout_id', 'session_date'], 'workout_set_previous_lookup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workout_set_completions');
    }
};
