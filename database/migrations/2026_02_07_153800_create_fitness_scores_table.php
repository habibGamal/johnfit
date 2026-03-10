<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fitness_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Overall score (0-100)
            $table->decimal('total_score', 5, 2);

            // Component scores (0-100 each)
            $table->decimal('workout_score', 5, 2);
            $table->decimal('meal_score', 5, 2);
            $table->decimal('inbody_score', 5, 2)->nullable();

            // Detailed metrics
            $table->json('workout_metrics')->nullable();
            $table->json('meal_metrics')->nullable();
            $table->json('inbody_metrics')->nullable();

            // Time period
            $table->date('period_start');
            $table->date('period_end');
            $table->integer('period_days')->default(7);

            $table->timestamps();

            $table->index(['user_id', 'period_end']);
            $table->unique(['user_id', 'period_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fitness_scores');
    }
};
