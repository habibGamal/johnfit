<?php

use App\Models\MealPlan;
use App\Models\UserPlanAssignment;
use App\Models\WorkoutPlan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ai_plan_generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workout_plan_id')->nullable()->constrained('workout_plans')->nullOnDelete();
            $table->foreignId('meal_plan_id')->nullable()->constrained('meal_plans')->nullOnDelete();
            $table->string('driver', 50)->default('ai');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });

        // Backfill existing AI plan generations from existing user plan assignments & plans
        $this->backfillExistingAiPlanGenerations();
    }

    /**
     * Backfill existing users who already generated AI plans.
     */
    protected function backfillExistingAiPlanGenerations(): void
    {
        try {
            // Find assignments linked to AI-generated plans
            $aiAssignments = DB::table('user_plan_assignments')
                ->select('user_id', 'plan_id', 'plan_type', 'created_at')
                ->get();

            $userAiGenerations = [];

            foreach ($aiAssignments as $assignment) {
                $isAi = false;
                if ($assignment->plan_type === 'meal') {
                    $mealPlan = DB::table('meal_plans')->where('id', $assignment->plan_id)->first();
                    if ($mealPlan && str_starts_with($mealPlan->name, 'AI Plan -')) {
                        $isAi = true;
                    }
                } elseif ($assignment->plan_type === 'workout') {
                    $workoutPlan = DB::table('workout_plans')->where('id', $assignment->plan_id)->first();
                    if ($workoutPlan && str_starts_with($workoutPlan->name, 'AI Workout Plan -')) {
                        $isAi = true;
                    }
                }

                if ($isAi) {
                    $userId = $assignment->user_id;
                    if (!isset($userAiGenerations[$userId])) {
                        $userAiGenerations[$userId] = [
                            'user_id' => $userId,
                            'workout_plan_id' => null,
                            'meal_plan_id' => null,
                            'driver' => 'ai',
                            'metadata' => json_encode(['backfilled' => true, 'source' => 'historical_plan_assignment']),
                            'created_at' => $assignment->created_at ?? now(),
                            'updated_at' => $assignment->created_at ?? now(),
                        ];
                    }

                    if ($assignment->plan_type === 'workout') {
                        $userAiGenerations[$userId]['workout_plan_id'] = $assignment->plan_id;
                    } elseif ($assignment->plan_type === 'meal') {
                        $userAiGenerations[$userId]['meal_plan_id'] = $assignment->plan_id;
                    }
                }
            }

            if (!empty($userAiGenerations)) {
                DB::table('ai_plan_generations')->insert(array_values($userAiGenerations));
            }
        } catch (\Throwable $e) {
            // Gracefully ignore backfill failure if tables or data differ
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_plan_generations');
    }
};
