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
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon', 60)->default('Trophy');
            $table->enum('tier', ['bronze', 'silver', 'gold', 'platinum', 'diamond'])->default('bronze');
            $table->string('category', 50)->default('general')->index();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('badge_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('badge_id')->constrained('badges')->cascadeOnDelete();
            $table->enum('metric', [
                'workout',
                'meal',
                'inbody',
                'total',
                'streak_workout',
                'streak_workout_best',
                'streak_meal',
                'streak_meal_best',
                'streak_hydration',
                'streak_hydration_best',
                'streak_overall',
                'streak_overall_best',
            ])->index();
            $table->enum('operator', ['gte', 'lte'])->default('gte');
            $table->decimal('threshold', 5, 2)->default(0);
            $table->timestamps();

            $table->index(['badge_id', 'metric']);
        });

        Schema::create('user_streaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('streak_type', ['workout', 'meal', 'hydration', 'overall'])->index();
            $table->unsignedInteger('current_streak')->default(0);
            $table->unsignedInteger('longest_streak')->default(0);
            $table->date('last_activity_date')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'streak_type']);
        });

        Schema::create('user_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('badge_id')->constrained('badges')->cascadeOnDelete();
            $table->timestamp('unlocked_at');
            $table->date('period_end')->nullable();
            $table->json('score_snapshot')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'badge_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_badges');
        Schema::dropIfExists('user_streaks');
        Schema::dropIfExists('badge_requirements');
        Schema::dropIfExists('badges');
    }
};
